<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'YBS_CERT_QUEUE_OPT', 'ybs_cert_mail_queue_v1' );
define( 'YBS_CERT_STATE_OPT', 'ybs_cert_mail_state_v1' );
define( 'YBS_CERT_LOG_OPT', 'ybs_cert_mail_log_v1' );
define( 'YBS_ORG_QUEUE_OPT', 'ybs_org_cert_mail_queue_v1' );
define( 'YBS_ORG_STATE_OPT', 'ybs_org_cert_mail_state_v1' );
define( 'YBS_ORG_LOG_OPT', 'ybs_org_cert_mail_log_v1' );
define( 'YBS_CERT_CRON_HOOK', 'ybs_cert_mailer_tick' );

function ybs_cert_mail_profile_opts( $profile ) {
	if ( $profile === 'org' ) {
		return [
			'queue' => YBS_ORG_QUEUE_OPT,
			'state' => YBS_ORG_STATE_OPT,
			'log'   => YBS_ORG_LOG_OPT,
		];
	}
	return [
		'queue' => YBS_CERT_QUEUE_OPT,
		'state' => YBS_CERT_STATE_OPT,
		'log'   => YBS_CERT_LOG_OPT,
	];
}

function ybs_cert_sender_mail_content_type_html() { return 'text/html'; }

function ybs_cert_sender_paths( $profile = 'katilim' ) {
	$base_dir = trailingslashit( get_template_directory() );
	$base_url = trailingslashit( get_template_directory_uri() );
	if ( $profile === 'org' ) {
		return [
			'csv'     => $base_dir . 'assets/img/Organizasyon/organizasyon.csv',
			'img_dir' => $base_dir . 'assets/img/Organizasyon/',
			'img_url' => $base_url . 'assets/img/Organizasyon/',
		];
	}
	return [
		'csv'     => $base_dir . 'assets/img/Katilimcilar/mailatilacaklar.csv',
		'img_dir' => $base_dir . 'assets/img/Katilimcilar/',
		'img_url' => $base_url . 'assets/img/Katilimcilar/',
	];
}

function ybs_cert_sender_norm( $s ) {
	$s = trim( (string) $s );
	$map = [ 'ç'=>'c','Ç'=>'c','ğ'=>'g','Ğ'=>'g','ı'=>'i','İ'=>'i','ö'=>'o','Ö'=>'o','ş'=>'s','Ş'=>'s','ü'=>'u','Ü'=>'u' ];
	$s = strtr( $s, $map );
	$s = mb_strtolower( $s, 'UTF-8' );
	$s = preg_replace( '/[^a-z0-9]+/u', '-', $s );
	return trim( $s, '-' );
}

function ybs_cert_sender_build_candidates( $name ) {
	$n = trim( (string) $name );
	$slug = ybs_cert_sender_norm( $n );
	$plain = preg_replace( '/\s+/', ' ', $n );
	$plain_dash = str_replace( ' ', '-', $plain );
	$plain_us = str_replace( ' ', '_', $plain );
	$base = array_filter( array_unique( [ $n, $plain, $plain_dash, $plain_us, $slug ] ) );
	$exts = [ 'jpg', 'jpeg', 'png', 'webp' ];
	$out = [];
	foreach ( $base as $b ) {
		foreach ( $exts as $e ) {
			$out[] = $b . '.' . $e;
		}
	}
	return array_unique( $out );
}

function ybs_cert_sender_scan_image_index( $img_dir ) {
	static $idx_cache = [];
	if ( isset( $idx_cache[ $img_dir ] ) ) {
		return $idx_cache[ $img_dir ];
	}
	$idx = [];
	if ( ! is_dir( $img_dir ) ) {
		$idx_cache[ $img_dir ] = $idx;
		return $idx;
	}
	$files = @scandir( $img_dir );
	if ( ! is_array( $files ) ) {
		$idx_cache[ $img_dir ] = $idx;
		return $idx;
	}
	foreach ( $files as $f ) {
		if ( $f === '.' || $f === '..' ) continue;
		$full = $img_dir . $f;
		if ( ! is_file( $full ) ) continue;
		$ext = strtolower( pathinfo( $f, PATHINFO_EXTENSION ) );
		if ( ! in_array( $ext, [ 'jpg', 'jpeg', 'png', 'webp' ], true ) ) continue;
		$base = pathinfo( $f, PATHINFO_FILENAME );
		$k = ybs_cert_sender_norm( $base );
		if ( $k !== '' && ! isset( $idx[ $k ] ) ) {
			$idx[ $k ] = $f;
		}
	}
	$idx_cache[ $img_dir ] = $idx;
	return $idx;
}

function ybs_cert_sender_resolve_image( $name, $paths = null ) {
	$p = $paths ? $paths : ybs_cert_sender_paths( 'katilim' );
	foreach ( ybs_cert_sender_build_candidates( $name ) as $candidate ) {
		$full = $p['img_dir'] . $candidate;
		if ( is_file( $full ) ) {
			return [ 'file' => $full, 'url' => $p['img_url'] . rawurlencode( $candidate ) ];
		}
	}
	$norm = ybs_cert_sender_norm( $name );
	if ( $norm !== '' ) {
		$idx = ybs_cert_sender_scan_image_index( $p['img_dir'] );
		if ( isset( $idx[ $norm ] ) ) {
			$fname = $idx[ $norm ];
			return [ 'file' => $p['img_dir'] . $fname, 'url' => $p['img_url'] . rawurlencode( $fname ) ];
		}
	}
	return [ 'file' => '', 'url' => '' ];
}

function ybs_cert_sender_parse_csv( $profile = 'katilim' ) {
	$p = ybs_cert_sender_paths( $profile );
	if ( ! is_file( $p['csv'] ) ) {
		$rel = $profile === 'org' ? 'assets/img/Organizasyon/organizasyon.csv' : 'assets/img/Katilimcilar/mailatilacaklar.csv';
		return new WP_Error( 'missing_csv', 'CSV dosyası bulunamadı: ' . $rel );
	}
	$rows = [];
	$h = fopen( $p['csv'], 'r' );
	if ( ! $h ) return new WP_Error( 'csv_open', 'CSV dosyası açılamadı.' );
	$seen = [];
	while ( ( $line = fgetcsv( $h, 0, ';' ) ) !== false ) {
		if ( empty( $line ) || count( $line ) < 2 ) continue;
		$name = trim( (string) $line[0] );
		$name = preg_replace( '/^\xEF\xBB\xBF/u', '', $name );
		$email = sanitize_email( trim( (string) $line[1] ) );
		if ( $name === '' || ! is_email( $email ) ) continue;
		$ek = strtolower( $email );
		if ( isset( $seen[ $ek ] ) ) continue;
		$seen[ $ek ] = true;
		$img = ybs_cert_sender_resolve_image( $name, $p );
		$rows[] = [
			'name'       => $name,
			'email'      => $email,
			'img_file'   => $img['file'],
			'img_url'    => $img['url'],
			'status'     => 'pending',
			'attempts'   => 0,
			'last_error' => '',
			'sent_at'    => '',
		];
	}
	fclose( $h );
	return $rows;
}

function ybs_cert_sender_get_state( $profile = 'katilim' ) {
	$o = ybs_cert_mail_profile_opts( $profile );
	$s = get_option( $o['state'], [] );
	return wp_parse_args( $s, [
		'running'    => 0,
		'total'      => 0,
		'sent'       => 0,
		'failed'     => 0,
		'updated_at' => '',
	] );
}

function ybs_cert_sender_set_state_from_queue( $queue, $running = null, $profile = 'katilim' ) {
	$o = ybs_cert_mail_profile_opts( $profile );
	$total  = count( $queue );
	$sent   = 0;
	$failed = 0;
	foreach ( $queue as $q ) {
		if ( isset( $q['status'] ) && $q['status'] === 'sent' ) $sent++;
		if ( isset( $q['status'] ) && $q['status'] === 'failed' ) $failed++;
	}
	$cur = ybs_cert_sender_get_state( $profile );
	if ( $running === null ) $running = (int) $cur['running'];
	update_option( $o['state'], [
		'running'    => (int) $running,
		'total'        => (int) $total,
		'sent'         => (int) $sent,
		'failed'       => (int) $failed,
		'updated_at'   => current_time( 'mysql' ),
	], false );
}

function ybs_cert_sender_register_cron( $schedules ) {
	if ( ! isset( $schedules['every_minute'] ) ) {
		$schedules['every_minute'] = [ 'interval' => 60, 'display' => 'Her dakika' ];
	}
	return $schedules;
}
add_filter( 'cron_schedules', 'ybs_cert_sender_register_cron' );

function ybs_cert_sender_ensure_cron() {
	if ( ! wp_next_scheduled( YBS_CERT_CRON_HOOK ) ) {
		wp_schedule_event( time() + 30, 'every_minute', YBS_CERT_CRON_HOOK );
	}
}
add_action( 'init', 'ybs_cert_sender_ensure_cron' );

function ybs_cert_sender_send_one( &$item, $profile = 'katilim' ) {
	$name  = $item['name'];
	$email = $item['email'];
	$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
	$attachments = [];
	$site = 'https://duybs.com';
	$instagram = 'https://www.instagram.com/du.ybs';
	$linkedin = 'https://www.linkedin.com/company/du-y%C3%B6netim-bili%C5%9Fim-sistemleri-%C3%B6%C4%9Frenci-toplulu%C4%9Fu/';
	$youtube = 'https://www.youtube.com/@duybs';

	if ( $profile === 'org' ) {
		$subject = 'Teşekkür Sertifikanız | Organizasyon Ekibi | 10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi';
		$body = '<div style="font-family:Arial,Helvetica,sans-serif;color:#111;line-height:1.6;font-size:14px;">';
		$body .= '<p>Merhaba ' . esc_html( $name ) . ',</p>';
		$body .= '<p>10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi\'nin gerçekleşmesinde organizasyon süreçlerindeki emeğiniz ve katkınız için içten teşekkür ederiz. Zirvenin başarısı, sizin gibi özverili ekip üyelerinin çalışmasıyla mümkün oldu.</p>';
		$body .= '<p>Desteğinizi anmak için hazırladığımız sertifikanız aşağıdadır.</p>';
	} else {
		$subject = 'Sertifikanız Hazır! | 10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi';
		$body = '<div style="font-family:Arial,Helvetica,sans-serif;color:#111;line-height:1.6;font-size:14px;">';
		$body .= '<p>Merhaba ' . esc_html( $name ) . ',</p>';
		$body .= '<p>10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi\'ne katılımınız için teşekkür ederiz. Sertifikanız hazır.</p>';
	}

	if ( ! empty( $item['img_file'] ) && is_file( $item['img_file'] ) ) {
		$attachments[] = $item['img_file'];
		$body .= '<p>Sertifika dosyanız bu e-postaya ekli olarak gönderilmiştir.</p>';
	} elseif ( ! empty( $item['img_url'] ) ) {
		$u = esc_url( $item['img_url'] );
		$body .= '<p>Sertifika eki oluşturulamadı. Aşağıdaki bağlantıdan indirebilirsiniz:</p>';
		$body .= '<p><a href="' . $u . '" target="_blank" rel="noopener">Sertifikayı indir</a></p>';
		$body .= '<p><img src="' . $u . '" alt="Sertifika" style="max-width:100%;height:auto;border:1px solid #ddd;border-radius:6px;"></p>';
	} else {
		$body .= '<p>Bu kayıt için sertifika dosyası bulunamadı. Lütfen bizimle iletişime geçin.</p>';
	}
	$body .= '<p>Sertifikanızı sosyal medya hesaplarınızda paylaşırken bizi etiketlemeyi unutmayın.</p>';
	$body .= '<p><strong>Sosyal Medya Hesaplarımız</strong><br>';
	$body .= 'Instagram: <a href="' . esc_url( $instagram ) . '" target="_blank" rel="noopener">' . esc_html( $instagram ) . '</a><br>';
	$body .= 'LinkedIn: <a href="' . esc_url( $linkedin ) . '" target="_blank" rel="noopener">' . esc_html( $linkedin ) . '</a><br>';
	$body .= 'YouTube: <a href="' . esc_url( $youtube ) . '" target="_blank" rel="noopener">' . esc_html( $youtube ) . '</a></p>';
	$body .= '<p>Topluluk web sayfamız: <a href="' . esc_url( $site ) . '" target="_blank" rel="noopener">duybs.com</a></p>';
	$body .= '<p>Herhangi bir sorun yaşarsanız <a href="mailto:ec.enescanturk@gmail.com">ec.enescanturk@gmail.com</a> adresinden iletişime geçebilirsiniz.</p>';
	$body .= '<p>Sevgiler,<br><strong>Enes Cantürk</strong><br>DÜ YBS Ar-Ge Koordinatörü</p>';
	$body .= '</div>';

	add_filter( 'wp_mail_content_type', 'ybs_cert_sender_mail_content_type_html' );
	$ok = wp_mail( $email, $subject, $body, $headers, $attachments );
	remove_filter( 'wp_mail_content_type', 'ybs_cert_sender_mail_content_type_html' );
	return (bool) $ok;
}

function ybs_cert_sender_process_queue( $force_max = null, $profile = 'katilim' ) {
	$o = ybs_cert_mail_profile_opts( $profile );
	$queue = get_option( $o['queue'], [] );
	if ( empty( $queue ) || ! is_array( $queue ) ) return;

	$state = ybs_cert_sender_get_state( $profile );
	if ( ! (int) $state['running'] ) return;

	$log = get_option( $o['log'], [] );
	if ( ! is_array( $log ) ) $log = [];
	$now = time();
	$log = array_values( array_filter( $log, function( $t ) use ( $now ) { return (int) $t > ( $now - HOUR_IN_SECONDS ); } ) );
	$remaining = max( 0, 150 - count( $log ) );
	if ( $remaining <= 0 ) {
		update_option( $o['log'], $log, false );
		return;
	}

	$max = is_numeric( $force_max ) ? (int) $force_max : 5;
	if ( $max < 1 ) $max = 1;
	$max = min( $max, $remaining );

	$processed = 0;
	foreach ( $queue as $i => $item ) {
		if ( $processed >= $max ) break;
		$status = isset( $item['status'] ) ? $item['status'] : 'pending';
		if ( ! in_array( $status, [ 'pending', 'retry' ], true ) ) continue;
		$attempts = isset( $item['attempts'] ) ? (int) $item['attempts'] : 0;
		if ( $attempts >= 5 ) {
			$queue[ $i ]['status'] = 'failed';
			$queue[ $i ]['last_error'] = 'Maksimum deneme sayısı aşıldı.';
			continue;
		}

		$queue[ $i ]['attempts'] = $attempts + 1;
		$ok = ybs_cert_sender_send_one( $queue[ $i ], $profile );
		$log[] = time();

		if ( $ok ) {
			$queue[ $i ]['status'] = 'sent';
			$queue[ $i ]['sent_at'] = current_time( 'mysql' );
			$queue[ $i ]['last_error'] = '';
		} else {
			$queue[ $i ]['status'] = ( $queue[ $i ]['attempts'] >= 5 ) ? 'failed' : 'retry';
			$queue[ $i ]['last_error'] = 'wp_mail başarısız.';
		}
		$processed++;
	}

	update_option( $o['queue'], $queue, false );
	update_option( $o['log'], $log, false );
	ybs_cert_sender_set_state_from_queue( $queue, null, $profile );

	$still = false;
	foreach ( $queue as $item ) {
		$st = isset( $item['status'] ) ? $item['status'] : 'pending';
		if ( in_array( $st, [ 'pending', 'retry' ], true ) ) { $still = true; break; }
	}
	if ( ! $still ) {
		$state = ybs_cert_sender_get_state( $profile );
		$state['running'] = 0;
		$state['updated_at'] = current_time( 'mysql' );
		update_option( $o['state'], $state, false );
	}
}

function ybs_cert_sender_cron_tick() {
	ybs_cert_sender_process_queue( null, 'katilim' );
	ybs_cert_sender_process_queue( null, 'org' );
}
add_action( YBS_CERT_CRON_HOOK, 'ybs_cert_sender_cron_tick' );

function ybs_cert_sender_admin_profile() {
	if ( ! isset( $_POST['ybs_cert_profile'] ) ) {
		return 'katilim';
	}
	$p = sanitize_text_field( wp_unslash( $_POST['ybs_cert_profile'] ) );
	return in_array( $p, [ 'katilim', 'org' ], true ) ? $p : 'katilim';
}

function ybs_cert_sender_admin_redirect_for( $profile ) {
	return admin_url( 'admin.php?page=' . ( $profile === 'org' ? 'ybs-ekip-sertifika-gonder' : 'ybs-sertifika-gonder' ) );
}

function ybs_cert_sender_admin_action() {
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) return;
	if ( ! isset( $_POST['ybs_cert_action'] ) ) return;
	check_admin_referer( 'ybs_cert_sender_action' );

	$action   = sanitize_text_field( wp_unslash( $_POST['ybs_cert_action'] ) );
	$profile  = ybs_cert_sender_admin_profile();
	$redirect = ybs_cert_sender_admin_redirect_for( $profile );
	$o        = ybs_cert_mail_profile_opts( $profile );

	if ( $action === 'prepare' ) {
		$rows = ybs_cert_sender_parse_csv( $profile );
		if ( is_wp_error( $rows ) ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( $rows->get_error_message() ), $redirect ) );
			exit;
		}
		update_option( $o['queue'], $rows, false );
		ybs_cert_sender_set_state_from_queue( $rows, 0, $profile );
		update_option( $o['log'], [], false );
		wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Kuyruk oluşturuldu: ' . count( $rows ) . ' kayıt.' ), $redirect ) );
		exit;
	}

	if ( $action === 'start' ) {
		$q = get_option( $o['queue'], [] );
		if ( empty( $q ) ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Önce CSVden kuyruk oluşturun.' ), $redirect ) );
			exit;
		}
		ybs_cert_sender_ensure_cron();
		ybs_cert_sender_set_state_from_queue( $q, 1, $profile );
		wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Gönderim başlatıldı. Cron ile arka planda devam eder.' ), $redirect ) );
		exit;
	}

	if ( $action === 'stop' ) {
		$q = get_option( $o['queue'], [] );
		ybs_cert_sender_set_state_from_queue( is_array( $q ) ? $q : [], 0, $profile );
		wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Gönderim durduruldu.' ), $redirect ) );
		exit;
	}

	if ( $action === 'run_now' ) {
		$q = get_option( $o['queue'], [] );
		if ( ! empty( $q ) ) {
			ybs_cert_sender_set_state_from_queue( $q, 1, $profile );
			ybs_cert_sender_process_queue( 5, $profile );
		}
		wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Manuel çalışma tamamlandı.' ), $redirect ) );
		exit;
	}

	if ( $action === 'send_test_enes' ) {
		$rows = ybs_cert_sender_parse_csv( $profile );
		if ( is_wp_error( $rows ) ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( $rows->get_error_message() ), $redirect ) );
			exit;
		}
		$target = null;
		$want = ybs_cert_sender_norm( 'ENES CANTÜRK' );
		foreach ( $rows as $r ) {
			if ( ybs_cert_sender_norm( $r['name'] ) === $want ) {
				$target = $r;
				break;
			}
		}
		if ( ! $target ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'ENES CANTÜRK CSV listesinde bulunamadı.' ), $redirect ) );
			exit;
		}
		$ok = ybs_cert_sender_send_one( $target, $profile );
		if ( $ok ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Test mail gönderildi: ' . $target['email'] ), $redirect ) );
		} else {
			global $phpmailer;
			$err = isset( $phpmailer->ErrorInfo ) ? $phpmailer->ErrorInfo : 'wp_mail başarısız.';
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Test mail hatası: ' . $err ), $redirect ) );
		}
		exit;
	}

	if ( $action === 'resend_one' ) {
		$email = isset( $_POST['resend_email'] ) ? sanitize_email( wp_unslash( $_POST['resend_email'] ) ) : '';
		$q = get_option( $o['queue'], [] );
		if ( ! is_array( $q ) || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Geçersiz tekrar gönder talebi.' ), $redirect ) );
			exit;
		}
		$found = false;
		foreach ( $q as $i => $row ) {
			if ( isset( $row['email'] ) && strtolower( $row['email'] ) === strtolower( $email ) ) {
				$q[ $i ]['status'] = 'pending';
				$q[ $i ]['last_error'] = '';
				$q[ $i ]['sent_at'] = '';
				$found = true;
				break;
			}
		}
		if ( $found ) {
			update_option( $o['queue'], $q, false );
			ybs_cert_sender_set_state_from_queue( $q, 1, $profile );
			ybs_cert_sender_process_queue( 1, $profile );
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Tekrar gönderim denendi: ' . $email ), $redirect ) );
		} else {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Kuyrukta kayıt bulunamadı: ' . $email ), $redirect ) );
		}
		exit;
	}

	if ( $action === 'requeue_sent_all' ) {
		$q = get_option( $o['queue'], [] );
		if ( ! is_array( $q ) || empty( $q ) ) {
			wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( 'Kuyruk boş.' ), $redirect ) );
			exit;
		}
		$n = 0;
		foreach ( $q as $i => $row ) {
			if ( isset( $row['status'] ) && $row['status'] === 'sent' ) {
				$q[ $i ]['status'] = 'pending';
				$q[ $i ]['sent_at'] = '';
				$q[ $i ]['last_error'] = '';
				$n++;
			}
		}
		update_option( $o['queue'], $q, false );
		ybs_cert_sender_set_state_from_queue( $q, 0, $profile );
		wp_safe_redirect( add_query_arg( 'ybs_notice', rawurlencode( $n . ' kayıt tekrar kuyruğa alındı.' ), $redirect ) );
		exit;
	}
}
add_action( 'admin_init', 'ybs_cert_sender_admin_action' );
