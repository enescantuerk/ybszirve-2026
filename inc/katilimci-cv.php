<?php
/**
 * Katılımcı CV toplama: CPT, güvenli PDF yükleme, admin listesi.
 *
 * @package duybs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'YBS_CV_MAX_BYTES', 5 * 1024 * 1024 ); // 5 MB
define( 'YBS_CV_UPLOAD_SUBDIR', 'katilimci-cv' );

/**
 * CPT: her kayıt bir CV gönderimi.
 */
function ybs_register_katilimci_cv_cpt() {
	register_post_type(
		'ybs_katilimci_cv',
		array(
			'labels'       => array(
				'name' => 'Katılımcı CVleri',
			),
			'public'       => false,
			'show_ui'      => false,
			'show_in_menu' => false,
			'query_var'    => false,
			'rewrite'      => false,
			'capability_type' => 'post',
			'map_meta_cap' => true,
			'supports'     => array( 'title' ),
		)
	);
}
add_action( 'init', 'ybs_register_katilimci_cv_cpt' );

/**
 * @return string Örn. 905510635775 (E.164 benzeri, TR cep)
 */
function ybs_cv_normalize_phone( $telefon ) {
	$d = preg_replace( '/\D+/', '', (string) $telefon );
	if ( $d === '' ) {
		return '';
	}
	// 05XX XXX XX XX → 90 5XX XXX XX XX
	if ( strlen( $d ) === 11 && $d[0] === '0' && isset( $d[1] ) && $d[1] === '5' ) {
		$d = '90' . substr( $d, 1 );
	} elseif ( strlen( $d ) === 10 && $d[0] === '5' ) {
		// 5XX XXX XX XX
		$d = '90' . $d;
	} elseif ( strlen( $d ) === 12 && $d[0] === '0' && substr( $d, 1, 2 ) === '90' ) {
		// 0905... yazım hatası
		$d = substr( $d, 1 );
	}
	return $d;
}

/**
 * @return WP_Post|null
 */
function ybs_cv_find_post_by_phone( $normalized ) {
	if ( $normalized === '' ) {
		return null;
	}
	$q = new WP_Query(
		array(
			'post_type'      => 'ybs_katilimci_cv',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_ybs_cv_telefon_norm',
			'meta_value'     => $normalized,
			'fields'         => 'all',
		)
	);
	if ( ! $q->have_posts() ) {
		return null;
	}
	return $q->posts[0];
}

/**
 * Başka bir kayıt bu dosya adını kullanıyor mu? (Güncellemede mevcut ID hariç.)
 *
 * @return int Post ID veya 0
 */
function ybs_cv_owner_of_filename( $filename, $except_post_id = 0 ) {
	$q = new WP_Query(
		array(
			'post_type'      => 'ybs_katilimci_cv',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_ybs_cv_filename',
			'meta_value'     => $filename,
			'fields'         => 'ids',
		)
	);
	if ( ! $q->have_posts() ) {
		return 0;
	}
	$id = (int) $q->posts[0];
	return ( $id && $id !== (int) $except_post_id ) ? $id : 0;
}

/**
 * ad-soyad kısa ad (dosya tabanı).
 */
function ybs_cv_build_base_slug( $ad, $soyad ) {
	$ad    = sanitize_title( trim( (string) $ad ) );
	$soyad = sanitize_title( trim( (string) $soyad ) );
	$base  = trim( $ad . '-' . $soyad, '-' );
	return $base !== '' ? $base : 'katilimci';
}

/**
 * Hedef dosya adını belirle; çakışmada telefon son 4 hane eklenir.
 */
function ybs_cv_resolve_filename( $ad, $soyad, $normalized_phone, $existing_post_id ) {
	$base = ybs_cv_build_base_slug( $ad, $soyad );
	$fname = $base . '.pdf';
	$owner = ybs_cv_owner_of_filename( $fname, $existing_post_id );
	if ( $owner ) {
		$suffix = substr( $normalized_phone, -4 );
		if ( strlen( $suffix ) < 4 ) {
			$suffix = substr( md5( $normalized_phone ), 0, 4 );
		}
		$fname = $base . '-' . $suffix . '.pdf';
		// Nadir ikinci çakışma
		$n = 2;
		while ( ybs_cv_owner_of_filename( $fname, $existing_post_id ) && $n < 20 ) {
			$fname = $base . '-' . $suffix . '-' . $n . '.pdf';
			$n++;
		}
	}
	return $fname;
}

/**
 * Klasörde PHP çalıştırmayı zorlaştır (Apache .htaccess + boş index).
 */
function ybs_cv_ensure_directory_protection( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$index = $dir . '/index.php';
	if ( ! file_exists( $index ) ) {
		file_put_contents( $index, "<?php\n// Silence is golden.\n" );
	}
	$htaccess = $dir . '/.htaccess';
	if ( ! file_exists( $htaccess ) ) {
		$rules = "# YBS katılımcı CV\n<FilesMatch \"\\.(php|phtml|php5|phar)$\">\nRequire all denied\n</FilesMatch>\n";
		// Eski Apache uyumu
		$rules .= "<FilesMatch \"\\.(php|phtml|php5|phar)$\">\nOrder deny,allow\nDeny from all\n</FilesMatch>\n";
		@file_put_contents( $htaccess, $rules );
	}
}

/**
 * Geçici dosyanın gerçekten PDF olup olmadığını kontrol et.
 *
 * @return true|WP_Error
 */
function ybs_cv_validate_pdf_tmp( $tmp_path ) {
	if ( ! is_uploaded_file( $tmp_path ) ) {
		return new WP_Error( 'invalid', 'Dosya okunamadı.' );
	}
	$h = @fopen( $tmp_path, 'rb' );
	if ( ! $h ) {
		return new WP_Error( 'invalid', 'Dosya okunamadı.' );
	}
	$head = fread( $h, 8 );
	fclose( $h );
	if ( strncmp( $head, '%PDF', 4 ) !== 0 ) {
		return new WP_Error( 'not_pdf', 'Yalnızca PDF dosyası yükleyebilirsiniz.' );
	}
	$finfo = function_exists( 'finfo_open' ) ? finfo_open( FILEINFO_MIME_TYPE ) : null;
	if ( $finfo ) {
		$mime = finfo_file( $finfo, $tmp_path );
		finfo_close( $finfo );
		$allowed = array( 'application/pdf', 'application/x-pdf' );
		if ( ! in_array( $mime, $allowed, true ) ) {
			return new WP_Error( 'mime', 'Geçersiz dosya türü.' );
		}
	}
	$size = filesize( $tmp_path );
	if ( $size === false || $size > YBS_CV_MAX_BYTES ) {
		return new WP_Error( 'size', 'Dosya en fazla 5 MB olabilir.' );
	}
	if ( $size < 100 ) {
		return new WP_Error( 'small', 'Dosya çok küçük veya bozuk.' );
	}
	return true;
}

/**
 * Güncelleme şifresi denemesi kilidi (telefon bazlı).
 */
function ybs_cv_pw_lock_key( $norm ) {
	return 'ybs_cv_pwlock_' . md5( $norm );
}

function ybs_cv_pw_fail_key( $norm ) {
	return 'ybs_cv_pwfail_' . md5( $norm );
}

function ybs_cv_is_update_pw_locked( $norm ) {
	return (bool) get_transient( ybs_cv_pw_lock_key( $norm ) );
}

function ybs_cv_register_update_pw_fail( $norm ) {
	$k = ybs_cv_pw_fail_key( $norm );
	$n = (int) get_transient( $k ) + 1;
	set_transient( $k, $n, 30 * MINUTE_IN_SECONDS );
	if ( $n >= 5 ) {
		set_transient( ybs_cv_pw_lock_key( $norm ), 1, 30 * MINUTE_IN_SECONDS );
	}
}

function ybs_cv_clear_update_pw_fails( $norm ) {
	delete_transient( ybs_cv_pw_fail_key( $norm ) );
	delete_transient( ybs_cv_pw_lock_key( $norm ) );
}

/**
 * IP başına hız sınırı (5 dk içinde en fazla 8 istek).
 */
function ybs_cv_rate_limit_ok() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
	$key = 'ybs_cv_rl_' . md5( $ip );
	$n   = (int) get_transient( $key );
	if ( $n >= 8 ) {
		return false;
	}
	set_transient( $key, $n + 1, 5 * MINUTE_IN_SECONDS );
	return true;
}

/**
 * AJAX: CV yükleme (giriş gerektirmez — QR sayfası).
 */
function ybs_handle_katilimci_cv_upload() {
	check_ajax_referer( 'ybs_katilimci_cv_submit', 'security' );

	if ( ! ybs_cv_rate_limit_ok() ) {
		wp_send_json_error( array( 'message' => 'Çok fazla deneme. Lütfen birkaç dakika sonra tekrar deneyin.', 'code' => 'ratelimit' ) );
	}

	// Honeypot
	if ( ! empty( $_POST['ybs_cv_company'] ) ) {
		wp_send_json_error( array( 'message' => 'İşlem tamamlanamadı.', 'code' => 'bot' ) );
	}

	$ad     = isset( $_POST['ad'] ) ? sanitize_text_field( wp_unslash( $_POST['ad'] ) ) : '';
	$soyad  = isset( $_POST['soyad'] ) ? sanitize_text_field( wp_unslash( $_POST['soyad'] ) ) : '';
	$telraw = isset( $_POST['telefon'] ) ? sanitize_text_field( wp_unslash( $_POST['telefon'] ) ) : '';
	$replace = ! empty( $_POST['ybs_cv_replace'] );
	$pass    = isset( $_POST['guncelleme_sifre'] ) ? wp_unslash( $_POST['guncelleme_sifre'] ) : '';

	if ( $ad === '' || mb_strlen( $ad ) > 80 ) {
		wp_send_json_error( array( 'message' => 'Geçerli bir ad girin.', 'code' => 'validation' ) );
	}
	if ( $soyad === '' || mb_strlen( $soyad ) > 80 ) {
		wp_send_json_error( array( 'message' => 'Geçerli bir soyad girin.', 'code' => 'validation' ) );
	}
	$norm = ybs_cv_normalize_phone( $telraw );
	// TR cep: 90 + 10 hane, ulusal numara 5 ile başlar
	if ( strlen( $norm ) !== 12 || substr( $norm, 0, 2 ) !== '90' || ! isset( $norm[2] ) || $norm[2] !== '5' ) {
		wp_send_json_error( array( 'message' => 'Geçerli bir cep telefonu girin (örn. 05XX XXX XX XX).', 'code' => 'validation' ) );
	}

	$existing = ybs_cv_find_post_by_phone( $norm );

	if ( $existing && $replace ) {
		if ( ybs_cv_is_update_pw_locked( $norm ) ) {
			wp_send_json_error( array( 'message' => 'Çok fazla hatalı şifre denemesi. Yaklaşık 30 dakika sonra tekrar deneyin.', 'code' => 'locked' ) );
		}
		$stored = get_post_meta( $existing->ID, '_ybs_cv_update_hash', true );
		if ( empty( $stored ) || ! is_string( $stored ) ) {
			wp_send_json_error( array( 'message' => 'Bu kayıt için güvenli güncelleme tanımlı değil. Lütfen yöneticiyle iletişime geçin.', 'code' => 'no_hash' ) );
		}
		if ( mb_strlen( $pass ) < 8 || mb_strlen( $pass ) > 128 ) {
			wp_send_json_error( array( 'message' => 'Güncelleme şifresi en az 8 karakter olmalıdır.', 'code' => 'validation' ) );
		}
		if ( ! wp_check_password( $pass, $stored ) ) {
			ybs_cv_register_update_pw_fail( $norm );
			wp_send_json_error( array( 'message' => 'Güncelleme şifresi hatalı.', 'code' => 'bad_password' ) );
		}
		ybs_cv_clear_update_pw_fails( $norm );
	} elseif ( ! $existing ) {
		if ( mb_strlen( $pass ) < 8 || mb_strlen( $pass ) > 128 ) {
			wp_send_json_error( array( 'message' => 'Güncelleme şifresi en az 8, en fazla 128 karakter olmalıdır. Sonradan CV değiştirmek için bu şifreyi kullanacaksınız.', 'code' => 'validation' ) );
		}
	} elseif ( $existing && ! $replace ) {
		wp_send_json_error(
			array(
				'code'    => 'duplicate',
				'message' => 'Bu cep numarasıyla zaten bir CV kaydı var. Güncellemek için aşağıdaki kutuyu işaretleyin, ilk yüklerken belirlediğiniz güncelleme şifresini girin ve tekrar gönderin.',
			)
		);
	}

	if ( empty( $_FILES['cv_pdf'] ) || ! isset( $_FILES['cv_pdf']['error'] ) || UPLOAD_ERR_OK !== (int) $_FILES['cv_pdf']['error'] ) {
		wp_send_json_error( array( 'message' => 'Lütfen PDF dosyanızı seçin.', 'code' => 'validation' ) );
	}

	$file = $_FILES['cv_pdf'];
	if ( $file['size'] > YBS_CV_MAX_BYTES ) {
		wp_send_json_error( array( 'message' => 'Dosya en fazla 5 MB olabilir.', 'code' => 'validation' ) );
	}

	if ( strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) !== 'pdf' ) {
		wp_send_json_error( array( 'message' => 'Yalnızca PDF yüklenebilir.', 'code' => 'validation' ) );
	}

	$valid = ybs_cv_validate_pdf_tmp( $file['tmp_name'] );
	if ( is_wp_error( $valid ) ) {
		wp_send_json_error( array( 'message' => $valid->get_error_message(), 'code' => 'validation' ) );
	}

	$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'], array( 'pdf' => 'application/pdf' ) );
	if ( ! empty( $check['ext'] ) && $check['ext'] !== 'pdf' ) {
		wp_send_json_error( array( 'message' => 'Yalnızca PDF yüklenebilir.', 'code' => 'validation' ) );
	}

	$uploads = wp_upload_dir();
	if ( ! empty( $uploads['error'] ) ) {
		wp_send_json_error( array( 'message' => 'Yükleme klasörü kullanılamıyor.', 'code' => 'server' ) );
	}

	$dir = trailingslashit( $uploads['basedir'] ) . YBS_CV_UPLOAD_SUBDIR;
	if ( ! wp_mkdir_p( $dir ) ) {
		wp_send_json_error( array( 'message' => 'Klasör oluşturulamadı.', 'code' => 'server' ) );
	}
	ybs_cv_ensure_directory_protection( $dir );

	$existing_id = $existing ? (int) $existing->ID : 0;
	$filename    = ybs_cv_resolve_filename( $ad, $soyad, $norm, $existing_id );
	$dest        = trailingslashit( $dir ) . $filename;

	if ( $existing_id ) {
		$old_fn = get_post_meta( $existing_id, '_ybs_cv_filename', true );
		if ( $old_fn && $old_fn !== $filename ) {
			$old_path = trailingslashit( $dir ) . basename( $old_fn );
			if ( is_file( $old_path ) ) {
				@unlink( $old_path );
			}
		}
	}

	if ( ! move_uploaded_file( $file['tmp_name'], $dest ) ) {
		wp_send_json_error( array( 'message' => 'Dosya kaydedilemedi.', 'code' => 'server' ) );
	}
	@chmod( $dest, 0644 );

	$title = trim( $ad . ' ' . $soyad );
	$url   = trailingslashit( $uploads['baseurl'] ) . YBS_CV_UPLOAD_SUBDIR . '/' . rawurlencode( $filename );

	if ( $existing_id ) {
		wp_update_post(
			array(
				'ID'         => $existing_id,
				'post_title' => $title,
			)
		);
		$post_id = $existing_id;
	} else {
		$post_id = wp_insert_post(
			array(
				'post_type'   => 'ybs_katilimci_cv',
				'post_status' => 'publish',
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $post_id ) || ! $post_id ) {
			@unlink( $dest );
			wp_send_json_error( array( 'message' => 'Kayıt oluşturulamadı.', 'code' => 'server' ) );
		}
	}

	update_post_meta( $post_id, '_ybs_cv_ad', $ad );
	update_post_meta( $post_id, '_ybs_cv_soyad', $soyad );
	update_post_meta( $post_id, '_ybs_cv_telefon', $telraw );
	update_post_meta( $post_id, '_ybs_cv_telefon_norm', $norm );
	update_post_meta( $post_id, '_ybs_cv_filename', $filename );
	update_post_meta( $post_id, '_ybs_cv_file_url', esc_url_raw( $url ) );
	update_post_meta( $post_id, '_ybs_cv_uploaded', current_time( 'mysql' ) );

	if ( ! $existing_id ) {
		update_post_meta( $post_id, '_ybs_cv_update_hash', wp_hash_password( $pass ) );
	}

	wp_send_json_success(
		$existing_id
			? 'CV\'niz güncellendi. Teşekkür ederiz.'
			: 'CV\'niz alındı. İleride aynı numara ile güncelleme için bu şifreyi kullanın; unutmayın. Teşekkür ederiz.'
	);
}
add_action( 'wp_ajax_ybs_katilimci_cv_upload', 'ybs_handle_katilimci_cv_upload' );
add_action( 'wp_ajax_nopriv_ybs_katilimci_cv_upload', 'ybs_handle_katilimci_cv_upload' );

/**
 * Upload URL -> güvenli dosya yolu (yalnızca uploads altında).
 *
 * @return string
 */
function ybs_cv_upload_url_to_path( $url ) {
	$url = trim( (string) $url );
	if ( $url === '' ) {
		return '';
	}
	$uploads = wp_upload_dir();
	if ( empty( $uploads['baseurl'] ) || empty( $uploads['basedir'] ) ) {
		return '';
	}
	$baseurl = rtrim( $uploads['baseurl'], '/' );
	if ( strpos( $url, $baseurl ) !== 0 ) {
		return '';
	}
	$rel  = ltrim( substr( $url, strlen( $baseurl ) ), '/' );
	$path = trailingslashit( $uploads['basedir'] ) . $rel;
	$real = realpath( $path );
	$base = realpath( $uploads['basedir'] );
	if ( ! $real || ! $base ) {
		return '';
	}
	$base_prefix = rtrim( $base, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
	if ( strpos( $real, $base_prefix ) !== 0 ) {
		return '';
	}
	return ( is_file( $real ) && is_readable( $real ) ) ? $real : '';
}

/**
 * Admin: Katılımcı + Organizasyon ekibi tüm CVleri tek ZIP indir.
 */
function ybs_admin_export_all_cvs_zip() {
	if ( ! is_admin() ) {
		return;
	}
	if ( ! isset( $_GET['page'], $_GET['export_all_cvs_zip'] ) || $_GET['page'] !== 'ybs-katilimci-cv' ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Yetkisiz işlem' );
	}
	if ( ! class_exists( 'ZipArchive' ) ) {
		wp_die( 'Sunucuda ZipArchive desteği yok. Hosting sağlayıcınızdan ZIP eklentisini etkinleştirmenizi isteyin.' );
	}

	$entries = array();

	// 1) Katılımcı CVleri (CPT)
	$q = new WP_Query(
		array(
			'post_type'      => 'ybs_katilimci_cv',
			'post_status'    => 'publish',
			'posts_per_page' => 5000,
			'fields'         => 'ids',
		)
	);
	if ( ! empty( $q->posts ) ) {
		foreach ( $q->posts as $pid ) {
			$pid  = (int) $pid;
			$ad   = (string) get_post_meta( $pid, '_ybs_cv_ad', true );
			$soy  = (string) get_post_meta( $pid, '_ybs_cv_soyad', true );
			$fn   = (string) get_post_meta( $pid, '_ybs_cv_filename', true );
			$furl = (string) get_post_meta( $pid, '_ybs_cv_file_url', true );

			$path = '';
			if ( $fn !== '' ) {
				$uploads = wp_upload_dir();
				$cand    = trailingslashit( $uploads['basedir'] ) . YBS_CV_UPLOAD_SUBDIR . '/' . basename( $fn );
				$real    = realpath( $cand );
				$base    = realpath( trailingslashit( $uploads['basedir'] ) . YBS_CV_UPLOAD_SUBDIR );
				if ( $real && $base ) {
					$base_prefix = rtrim( $base, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
					if ( strpos( $real, $base_prefix ) === 0 && is_file( $real ) && is_readable( $real ) ) {
						$path = $real;
					}
				}
			}
			if ( $path === '' && $furl !== '' ) {
				$path = ybs_cv_upload_url_to_path( $furl );
			}
			if ( $path === '' ) {
				continue;
			}

			$zip_name = sanitize_file_name( trim( $ad . '-' . $soy ) );
			if ( $zip_name === '' ) {
				$zip_name = 'katilimci-' . $pid;
			}
			$entries[] = array(
				'path' => $path,
				'name' => 'katilimci-cv/' . $zip_name . '.pdf',
			);
		}
	}

	// 2) Organizasyon ekibi CVleri (user meta)
	$org_users = get_users(
		array(
			'role'   => 'topluluk_uyesi',
			'number' => -1,
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'ybs_cv_dosyasi',
					'value'   => '',
					'compare' => '!=',
				),
				array(
					'relation' => 'OR',
					array(
						'key'     => 'ybs_status',
						'value'   => 'aktif',
						'compare' => '=',
					),
					array(
						'key'     => 'ybs_status',
						'compare' => 'NOT EXISTS',
					),
				),
			),
		)
	);
	if ( ! empty( $org_users ) ) {
		foreach ( $org_users as $u ) {
			$cv_url = (string) get_user_meta( $u->ID, 'ybs_cv_dosyasi', true );
			$path   = ybs_cv_upload_url_to_path( $cv_url );
			if ( $path === '' ) {
				continue;
			}
			$zip_name = sanitize_file_name( $u->display_name !== '' ? $u->display_name : $u->user_login );
			if ( $zip_name === '' ) {
				$zip_name = 'uye-' . (int) $u->ID;
			}
			$entries[] = array(
				'path' => $path,
				'name' => 'organizasyon-ekibi-cv/' . $zip_name . '.pdf',
			);
		}
	}

	if ( empty( $entries ) ) {
		wp_die( 'ZIP oluşturulamadı: indirilecek CV bulunamadı.' );
	}

	$tmp = wp_tempnam( 'ybs-cvler.zip' );
	if ( ! $tmp ) {
		wp_die( 'Geçici ZIP dosyası oluşturulamadı.' );
	}
	$zip = new ZipArchive();
	if ( true !== $zip->open( $tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE ) ) {
		@unlink( $tmp );
		wp_die( 'ZIP dosyası açılamadı.' );
	}

	$used_names = array();
	foreach ( $entries as $e ) {
		$zip_name = (string) $e['name'];
		$n        = 2;
		while ( isset( $used_names[ $zip_name ] ) ) {
			$ext      = pathinfo( $e['name'], PATHINFO_EXTENSION );
			$filename = pathinfo( $e['name'], PATHINFO_FILENAME );
			$dir      = pathinfo( $e['name'], PATHINFO_DIRNAME );
			$zip_name = ($dir && $dir !== '.') ? $dir . '/' . $filename . '-' . $n . '.' . $ext : $filename . '-' . $n . '.' . $ext;
			$n++;
		}
		$used_names[ $zip_name ] = true;
		$zip->addFile( $e['path'], $zip_name );
	}
	$zip->close();

	if ( ob_get_length() ) {
		ob_clean();
	}
	$download_name = 'ybs-tum-cvler-' . date( 'Y-m-d_H-i' ) . '.zip';
	header( 'Content-Type: application/zip' );
	header( 'Content-Disposition: attachment; filename="' . $download_name . '"' );
	header( 'Content-Length: ' . filesize( $tmp ) );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );
	readfile( $tmp );
	@unlink( $tmp );
	exit;
}
add_action( 'admin_init', 'ybs_admin_export_all_cvs_zip' );
