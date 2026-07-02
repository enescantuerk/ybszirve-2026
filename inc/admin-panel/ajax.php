<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * ==============================================================================
 * SABİTLER VE YARDIMCI FONKSİYONLAR
 * ==============================================================================
 */
define('YBS_DRIVE_ROOT', ABSPATH . 'dosyalar/');
define('YBS_DRIVE_URL', site_url('/dosyalar/'));

// Güvenlik: Dizin yolunu temizle (Directory Traversal Koruması)
function ybs_clean_path($path) {
    $path = str_replace(array('../', '..\\'), '', $path);
    return trim($path, '/');
}

/**
 * ==============================================================================
 * 1. ÜYE YÖNETİMİ (HIZLI EKLEME, LİSTELEME, DETAY GÖSTERME, DURUM GÜNCELLEME)
 * ==============================================================================
 */

// 1.1. HIZLI ÜYE EKLE (Admin Panelinden - Otomatik AKTİF olur)
function ybs_handle_add_member_ajax() {
    check_ajax_referer( 'ybs_ajax_nonce', 'security' );

    $fullname = sanitize_text_field($_POST['fullname']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $department_name = sanitize_text_field($_POST['department_name']);
    $duty_title = sanitize_text_field($_POST['duty_title']);
    $is_asil = isset($_POST['ybs_is_asil']) ? 1 : 0;

    if ( email_exists( $email ) ) { wp_send_json_error( 'E-posta zaten kayıtlı.' ); wp_die(); }

    $password = wp_generate_password( 12, false );
    $user_id = wp_create_user( $email, $password, $email );

    if ( is_wp_error( $user_id ) ) { wp_send_json_error( $user_id->get_error_message() ); wp_die(); }

    $parts = explode(' ', $fullname);
    $last = array_pop($parts);
    $first = implode(' ', $parts);
    
    wp_update_user( array( 
        'ID' => $user_id, 
        'first_name' => $first, 
        'last_name' => $last, 
        'display_name' => $fullname, 
        'role' => 'topluluk_uyesi' 
    ));

    update_user_meta($user_id, 'ybs_telefon', $phone);
    update_user_meta($user_id, 'ybs_departman', $department_name);
    update_user_meta($user_id, 'ybs_gorev_tanimi', $duty_title);
    update_user_meta($user_id, 'ybs_is_asil', $is_asil);
    
    // ADMİN EKLEDİĞİ İÇİN DİREKT AKTİF YAPIYORUZ
    update_user_meta($user_id, 'ybs_status', 'aktif');

    wp_send_json_success( 'Üye başarıyla eklendi! Detaylı bilgilerini profilinden doldurabilir.' );
}
add_action( 'wp_ajax_ybs_add_member_ajax', 'ybs_handle_add_member_ajax' );

// 1.2. ÜYE LİSTELEME VE FİLTRELEME (DURUMA GÖRE)
function ybs_filter_members_ajax() {
    check_ajax_referer( 'ybs_ajax_nonce', 'security' );

    $search = sanitize_text_field($_POST['search']);
    $dept   = sanitize_text_field($_POST['dept']); 
    $status = sanitize_text_field($_POST['status']); 
    $user_status = sanitize_text_field($_POST['user_status']); 

    $args = array( 'role' => 'topluluk_uyesi', 'orderby' => 'registered', 'order' => 'DESC', 'number' => -1 );

    if ( !empty($search) ) {
        $args['search'] = '*' . $search . '*';
        $args['search_columns'] = array( 'user_login', 'user_email', 'display_name' );
    }

    $meta_query = array('relation' => 'AND');
    if ( !empty($dept) ) $meta_query[] = array('key' => 'ybs_departman', 'value' => $dept, 'compare' => '=');
    if ( $status === 'asil' ) $meta_query[] = array('key' => 'ybs_is_asil', 'value' => '1', 'compare' => '=');
    if ( $status === 'yk' ) $meta_query[] = array('key' => 'ybs_is_yk', 'value' => '1', 'compare' => '=');
    
    // ÜYE DURUMU FİLTRESİ
    if ( !empty($user_status) ) {
        if ($user_status === 'aktif') {
            $meta_query[] = array(
                'relation' => 'OR',
                array('key' => 'ybs_status', 'value' => 'aktif', 'compare' => '='),
                array('key' => 'ybs_status', 'compare' => 'NOT EXISTS')
            );
        } else {
            $meta_query[] = array('key' => 'ybs_status', 'value' => $user_status, 'compare' => '=');
        }
    }
    
    if ( count($meta_query) > 1 ) $args['meta_query'] = $meta_query;

    $user_query = new WP_User_Query( $args );
    $results = $user_query->get_results();

    if ( empty($results) ) { wp_send_json_success('<p style="grid-column:1/-1; text-align:center; padding:20px; color:#666;">Bu kriterlere uygun kayıt bulunamadı.</p>'); wp_die(); }

    ob_start();
    foreach ( $results as $user ) {
        $img_url = get_user_meta($user->ID, 'ybs_fotograf', true) ?: 'https://www.gravatar.com/avatar/'.md5($user->user_email).'?s=150&d=mp';
        $dept_name = get_user_meta($user->ID, 'ybs_departman', true) ?: 'Belirtilmedi';
        $is_asil = get_user_meta($user->ID, 'ybs_is_asil', true);
        $is_yk = get_user_meta($user->ID, 'ybs_is_yk', true);
        $phone = get_user_meta($user->ID, 'ybs_telefon', true);
        $wa_link = $phone ? 'https://wa.me/90' . preg_replace('/[^0-9]/', '', $phone) : '#';
        // Durumu Belirle
        $u_status = get_user_meta($user->ID, 'ybs_status', true);
        if(empty($u_status)) $u_status = 'aktif';

        $status_badge = '';
        $status_action = '';

        if($u_status === 'beklemede') {
            $status_badge = '<span class="y-badge" style="background:#f59e0b; color:#fff;">BEKLEYEN BAŞVURU</span>';
            $status_action = '<button class="button btn-status-change" data-id="'.$user->ID.'" data-to="aktif" style="border-color:#10b981; color:#10b981; flex:1;">Onayla</button>';
        } elseif($u_status === 'pasif') {
            $status_badge = '<span class="y-badge" style="background:#6b7280; color:#fff;">PASİF</span>';
            $status_action = '<button class="button btn-status-change" data-id="'.$user->ID.'" data-to="aktif" style="flex:1;">Aktif Yap</button>';
        } else {
            $status_badge = '<span class="y-badge" style="background:#10b981; color:#fff;">AKTİF</span>';
            $status_action = '<button class="button btn-status-change" data-id="'.$user->ID.'" data-to="pasif" style="color:#ef4444; flex:1;">Pasif Yap</button>';
        }

        echo '<div class="ybs-card">
            <div class="yc-header">
                <div class="yc-badges">'.$status_badge.($is_yk?' <span class="y-badge yk">YK</span>':'').($is_asil?' <span class="y-badge asil">ASİL</span>':'').'</div>
                <div class="yc-avatar"><img src="'.esc_url($img_url).'"></div>
            </div>
            <div class="yc-body">
                <h3 class="yc-name">'.$user->display_name.'</h3>
                <span class="yc-dept">'.$dept_name.'</span>
                <div class="yc-contact">
                    <div class="yc-contact-item"><span class="dashicons dashicons-email"></span> '.mb_strimwidth($user->user_email,0,20,'...').'</div>
                    '.($phone ? '<div class="yc-contact-item"><span class="dashicons dashicons-smartphone"></span> '.$phone.' <a href="'.$wa_link.'" target="_blank" class="yc-wa-icon"><span class="dashicons dashicons-whatsapp"></span></a></div>' : '').'
                </div>
            </div>
            <div class="yc-footer" style="display:flex; gap:5px;">
                <button class="button btn-view-member" data-id="'.$user->ID.'" style="flex:1;">Detay</button>
                '.$status_action.'
            </div>
        </div>';
    }
    wp_send_json_success(ob_get_clean());
}
add_action( 'wp_ajax_ybs_filter_members_ajax', 'ybs_filter_members_ajax' );

// 1.3. ÜYE DETAY HTML (GELİŞMİŞ POPUP İÇİN)
function ybs_get_member_detail_ajax() {
    check_ajax_referer( 'ybs_ajax_nonce', 'security' );
    $user_id = intval($_POST['user_id']);
    $user = get_userdata($user_id);

    // Tüm Verileri Çek
    $img_url = get_user_meta($user_id, 'ybs_fotograf', true) ?: 'https://www.gravatar.com/avatar/'.md5($user->user_email).'?s=300';
    $cv_url = get_user_meta($user_id, 'ybs_cv_dosyasi', true);
    
    // Temel Bilgiler
    $tc = get_user_meta($user_id, 'ybs_tc_kimlik', true) ?: '-';
    $student_no = get_user_meta($user_id, 'ybs_student_no', true) ?: '-';
    $dogum_tarihi = get_user_meta($user_id, 'ybs_dogum_tarihi', true);
    $dogum = $dogum_tarihi ? date('d.m.Y', strtotime($dogum_tarihi)) : '-';
    $phone = get_user_meta($user_id, 'ybs_telefon', true) ?: '-';
    $sehir = get_user_meta($user_id, 'ybs_sehir', true) ?: '-';
    $linkedin = get_user_meta($user_id, 'ybs_linkedin', true);
    $beden = get_user_meta($user_id, 'ybs_beden', true) ?: '-';

    // Organizasyon
    $dept = get_user_meta($user_id, 'ybs_departman', true) ?: '-';
    $gorev = get_user_meta($user_id, 'ybs_gorev_tanimi', true) ?: '-';
    $tecrube = get_user_meta($user_id, 'ybs_tecrube', true) ?: '-';
    
    // Acil Durum
    $acil_kisi = get_user_meta($user_id, 'ybs_acil_kisi', true) ?: '-';
    $acil_telefon = get_user_meta($user_id, 'ybs_acil_telefon', true) ?: '-';
    $acil_yakinlik = get_user_meta($user_id, 'ybs_acil_yakinlik', true) ?: '-';

    // Sağlık
    $blood = get_user_meta($user_id, 'ybs_kan_grubu', true) ?: '-';
    $beslenme = get_user_meta($user_id, 'ybs_beslenme', true) ?: '-';
    $saglik_notu = get_user_meta($user_id, 'ybs_health_notes', true) ?: 'Belirtilmedi';
    
    // Durum ve KVKK
    $status = get_user_meta($user_id, 'ybs_status', true) ?: 'aktif';
    $kvkk = get_user_meta($user_id, 'ybs_kvkk_onay', true) ? '<span style="color:#10b981; font-weight:bold;">✓ Onaylandı</span>' : '<span style="color:#ef4444; font-weight:bold;">❌ Onaylanmadı</span>';

    ob_start();
    ?>
    <div style="display:flex; flex-wrap:wrap; gap:25px;">
        
        <div style="width: 200px; text-align:center;">
            <img src="<?php echo esc_url($img_url); ?>" style="width:100%; height:200px; border-radius:8px; border:1px solid #cbd5e1; object-fit:cover; box-shadow:0 4px 6px rgba(0,0,0,0.05);">
            
            <div style="margin-top:15px;">
                <span style="display:block; font-size:12px; font-weight:bold; color:#64748b; text-transform:uppercase; margin-bottom:5px;">Sistem Durumu</span>
                <?php 
                    if($status == 'aktif') echo '<span style="display:inline-block; background:#10b981; color:#fff; padding:4px 12px; border-radius:20px; font-size:13px; font-weight:bold;">Aktif Üye</span>';
                    elseif($status == 'beklemede') echo '<span style="display:inline-block; background:#f59e0b; color:#fff; padding:4px 12px; border-radius:20px; font-size:13px; font-weight:bold;">Bekleyen Başvuru</span>';
                    else echo '<span style="display:inline-block; background:#64748b; color:#fff; padding:4px 12px; border-radius:20px; font-size:13px; font-weight:bold;">Pasif Üye</span>';
                ?>
            </div>

            <?php if($cv_url): ?>
                <a href="<?php echo esc_url($cv_url); ?>" target="_blank" class="button button-primary" style="margin-top:20px; width:100%;">CV Görüntüle / İndir</a>
            <?php else: ?>
                <span style="display:block; margin-top:20px; font-size:12px; color:#ef4444; font-weight:bold;">CV Yüklenmemiş</span>
            <?php endif; ?>
        </div>

        <div style="flex:1; min-width: 300px;">
            <h2 style="margin-top:0; border-bottom:2px solid #e2e8f0; padding-bottom:10px; color:#0f172a; font-size:24px;">
                <?php echo esc_html($user->display_name); ?>
            </h2>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-top:15px; font-size:14px; color:#334155;">
                
                <div style="grid-column: 1 / -1; background:#f1f5f9; padding:6px 12px; border-radius:4px; font-weight:bold; color:#475569; font-size:12px; text-transform:uppercase; letter-spacing:0.5px;">Kişisel Bilgiler</div>
                <div><strong style="color:#0f172a;">E-Posta:</strong> <br><?php echo esc_html($user->user_email); ?></div>
                <div><strong style="color:#0f172a;">Telefon:</strong> <br><?php echo esc_html($phone); ?></div>
                <div><strong style="color:#0f172a;">T.C. Kimlik:</strong> <br><?php echo esc_html($tc); ?></div>
                <div><strong style="color:#0f172a;">Öğrenci No:</strong> <br><?php echo esc_html($student_no); ?></div>
                <div><strong style="color:#0f172a;">Doğum Tarihi:</strong> <br><?php echo esc_html($dogum); ?></div>
                <div><strong style="color:#0f172a;">Şehir:</strong> <br><?php echo esc_html($sehir); ?></div>
                <div><strong style="color:#0f172a;">Tişört Bedeni:</strong> <br><?php echo esc_html($beden); ?></div>
                <div><strong style="color:#0f172a;">LinkedIn:</strong> <br><?php echo $linkedin ? '<a href="'.esc_url($linkedin).'" target="_blank">Profile Git &rarr;</a>' : '-'; ?></div>

                <div style="grid-column: 1 / -1; background:#f1f5f9; padding:6px 12px; border-radius:4px; font-weight:bold; color:#475569; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; margin-top:10px;">Organizasyon</div>
                <div><strong style="color:#0f172a;">Departman:</strong> <br><?php echo esc_html($dept); ?></div>
                <div><strong style="color:#0f172a;">Görev Tanımı:</strong> <br><?php echo esc_html($gorev); ?></div>
                <div style="grid-column: 1 / -1;"><strong style="color:#0f172a;">Organizasyon Tecrübesi:</strong> <br><?php echo esc_html($tecrube); ?></div>

                <div style="grid-column: 1 / -1; background:#fef2f2; padding:6px 12px; border-radius:4px; font-weight:bold; color:#991b1b; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; margin-top:10px;">Sağlık ve Acil Durum</div>
                <div><strong style="color:#0f172a;">Kan Grubu:</strong> <br><?php echo esc_html($blood); ?></div>
                <div><strong style="color:#0f172a;">Beslenme:</strong> <br><?php echo esc_html($beslenme); ?></div>
                <div style="grid-column: 1 / -1;"><strong style="color:#0f172a;">Alerjen / Kronik Hastalık / İlaç:</strong> <br><?php echo esc_html($saglik_notu); ?></div>
                
                <div style="grid-column: 1 / -1; border-top: 1px dashed #cbd5e1; margin-top:5px; padding-top:10px;">
                    <strong style="color:#0f172a;">Acil Durumda Ulaşılacak Kişi:</strong> <br>
                    <?php echo esc_html($acil_kisi); ?> (<?php echo esc_html($acil_yakinlik); ?>) - <strong><?php echo esc_html($acil_telefon); ?></strong>
                </div>

                <div style="grid-column: 1 / -1; margin-top:15px; padding-top:15px; border-top:2px solid #e2e8f0; font-size:13px; text-align:right;">
                    <strong>KVKK Onayı:</strong> <?php echo $kvkk; ?>
                </div>

            </div>
            
            <div style="margin-top:25px; text-align:right;">
                <a href="<?php echo get_edit_user_link($user_id); ?>" class="button" style="text-decoration:none;">Profili Düzenle</a>
            </div>
        </div>
    </div>
    <?php
    wp_send_json_success(ob_get_clean());
}
add_action( 'wp_ajax_ybs_get_member_detail_ajax', 'ybs_get_member_detail_ajax' );

// 1.4 DURUM GÜNCELLEME (AKTİF/PASİF/BEKLEMEDE YAP)
add_action('wp_ajax_ybs_change_status_ajax', 'ybs_change_status_ajax');
function ybs_change_status_ajax() {
    check_ajax_referer('ybs_ajax_nonce', 'security');
    
    // JS 'user_id' veya sadece 'id' göndermiş olabilir, ikisini de kontrol et
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
    
    // JS 'new_status', 'status' veya 'to' göndermiş olabilir
    $new_status = isset($_POST['new_status']) ? sanitize_text_field($_POST['new_status']) : '';
    if(empty($new_status) && isset($_POST['status'])) $new_status = sanitize_text_field($_POST['status']);
    if(empty($new_status) && isset($_POST['to'])) $new_status = sanitize_text_field($_POST['to']);
    
    if($user_id > 0 && in_array($new_status, ['aktif', 'pasif', 'beklemede'])) {
        update_user_meta($user_id, 'ybs_status', $new_status);
        wp_send_json_success('Durum başarıyla güncellendi.');
    }
    
    wp_send_json_error('Geçersiz durum veya eksik veri.');
}

/**
 * ==============================================================================
 * 2. DEPARTMAN YÖNETİMİ
 * ==============================================================================
 */
function ybs_handle_add_dept_ajax() {
    check_ajax_referer( 'ybs_ajax_nonce', 'security' );
    $name = sanitize_text_field($_POST['dept_name']);
    if(empty($name)) wp_send_json_error('İsim girin');
    $id = wp_insert_post(array('post_title'=>$name, 'post_type'=>'departman', 'post_status'=>'publish'));
    if($id) wp_send_json_success('Eklendi'); else wp_send_json_error('Hata');
}
add_action( 'wp_ajax_ybs_add_dept_ajax', 'ybs_handle_add_dept_ajax' );


/**
 * ==============================================================================
 * 3. DOSYA MERKEZİ (DRIVE) İŞLEMLERİ
 * ==============================================================================
 */

// 3.1. LİSTELEME
function ybs_drive_list_ajax() {
    check_ajax_referer('ybs_ajax_nonce', 'security'); 

    if (!file_exists(YBS_DRIVE_ROOT)) { mkdir(YBS_DRIVE_ROOT, 0755, true); file_put_contents(YBS_DRIVE_ROOT.'index.php', ''); }
    $req_path = isset($_POST['path']) ? ybs_clean_path($_POST['path']) : '';
    $full_path = YBS_DRIVE_ROOT . $req_path;

    if (!is_dir($full_path)) { wp_send_json_error('Klasör bulunamadı.'); wp_die(); }

    $items = scandir($full_path);
    $folders = []; $files = [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..' || $item === 'index.php') continue;
        if (is_dir($full_path . '/' . $item)) $folders[] = $item;
        else $files[] = $item;
    }

    $public_folders = get_option('ybs_public_folders', []);

    ob_start();
    foreach ($folders as $f) {
        $rel_path = ($req_path ? $req_path . '/' : '') . $f;
        $is_public = in_array($rel_path, $public_folders);
        $lock = $is_public ? 'dashicons-unlock' : 'dashicons-lock';
        $cls = $is_public ? 'public' : 'private';
        
        echo '<div class="drive-item drive-folder" data-name="'.$f.'">
            <div class="d-delete" data-name="'.$f.'" data-type="folder">×</div>
            <div class="d-visibility '.$cls.'" data-path="'.$rel_path.'"><span class="dashicons '.$lock.'"></span></div>
            <div class="d-icon folder"><span class="dashicons dashicons-category"></span></div>
            <div class="d-name">'.$f.'</div>
        </div>';
    }
    foreach ($files as $f) {
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        $icon = 'dashicons-media-default';
        if(in_array($ext, ['jpg','png'])) $icon = 'dashicons-format-image';
        if($ext=='pdf') $icon='dashicons-pdf';
        if(in_array($ext, ['doc','docx'])) $icon='dashicons-media-document';

        echo '<div class="drive-item drive-file" data-url="'.YBS_DRIVE_URL . ($req_path ? $req_path . '/' : '') . $f .'">
            <div class="d-delete" data-name="'.$f.'" data-type="file">×</div>
            <div class="d-icon file"><span class="dashicons '.$icon.'"></span></div>
            <div class="d-name">'.$f.'</div>
        </div>';
    }
    if(empty($folders) && empty($files)) echo '<p style="grid-column:1/-1;text-align:center;color:#999;">Boş klasör.</p>';
    
    wp_send_json_success(['html'=>ob_get_clean(), 'current_path'=>$req_path]);
}
add_action('wp_ajax_ybs_drive_list_ajax', 'ybs_drive_list_ajax');

// 3.2. KLASÖR OLUŞTUR
function ybs_drive_mkdir_ajax() {
    check_ajax_referer('ybs_ajax_nonce', 'security');
    $path = isset($_POST['path']) ? ybs_clean_path($_POST['path']) : '';
    $name = sanitize_file_name($_POST['name']);
    $new = YBS_DRIVE_ROOT . ($path ? $path . '/' : '') . $name;
    
    if (file_exists($new)) wp_send_json_error('Zaten var');
    if (mkdir($new, 0755)) wp_send_json_success('Oluşturuldu');
    else wp_send_json_error('Oluşturulamadı (İzin hatası olabilir)');
}
add_action('wp_ajax_ybs_drive_mkdir_ajax', 'ybs_drive_mkdir_ajax');

// 3.3. DOSYA YÜKLE
function ybs_drive_upload_ajax() {
    check_ajax_referer('ybs_ajax_nonce', 'security');
    $path = isset($_POST['path']) ? ybs_clean_path($_POST['path']) : '';
    $target = YBS_DRIVE_ROOT . ($path ? $path . '/' : '');

    if (!empty($_FILES['files']['name'][0])) {
        $count = 0;
        foreach ($_FILES['files']['name'] as $i => $name) {
            $name = sanitize_file_name($name);
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if(in_array($ext, ['php','exe','sh'])) continue; 
            
            if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $target . $name)) $count++;
        }
        if($count > 0) wp_send_json_success($count.' dosya yüklendi');
        else wp_send_json_error('Yükleme başarısız');
    }
    wp_send_json_error('Dosya yok');
}
add_action('wp_ajax_ybs_drive_upload_ajax', 'ybs_drive_upload_ajax');

// 3.4. SİLME
function ybs_drive_delete_ajax() {
    check_ajax_referer('ybs_ajax_nonce', 'security');
    $path = isset($_POST['path']) ? ybs_clean_path($_POST['path']) : '';
    $name = sanitize_file_name($_POST['name']);
    $type = sanitize_text_field($_POST['type']);
    $target = YBS_DRIVE_ROOT . ($path ? $path . '/' : '') . $name;

    if (!file_exists($target)) wp_send_json_error('Bulunamadı');

    if ($type === 'file') unlink($target);
    else rmdir($target);

    wp_send_json_success('Silindi');
}
add_action('wp_ajax_ybs_drive_delete_ajax', 'ybs_drive_delete_ajax');

// 3.5. GÖRÜNÜRLÜK (TOGGLE)
function ybs_drive_toggle_visibility_ajax() {
    check_ajax_referer('ybs_ajax_nonce', 'security');
    $path = ybs_clean_path($_POST['path']);
    $publics = get_option('ybs_public_folders', []);

    if (in_array($path, $publics)) {
        $publics = array_diff($publics, [$path]);
    } else {
        $publics[] = $path;
    }
    update_option('ybs_public_folders', array_values($publics));
    wp_send_json_success();
}
add_action('wp_ajax_ybs_drive_toggle_visibility_ajax', 'ybs_drive_toggle_visibility_ajax');

// =========================================================================
// 4. FRONTEND ÜYE KAYIT İŞLEMİ (DOSYA YÜKLEME VE GELİŞMİŞ HATA YÖNETİMİ)
// =========================================================================
add_action( 'wp_ajax_ybs_frontend_register_ajax', 'ybs_handle_frontend_register' );
add_action( 'wp_ajax_nopriv_ybs_frontend_register_ajax', 'ybs_handle_frontend_register' );

function ybs_handle_frontend_register() {
    check_ajax_referer( 'ybs_ajax_nonce', 'security' );

    $email = sanitize_email($_POST['email']);
    $tc_kimlik = sanitize_text_field($_POST['ybs_tc_kimlik']);

    if ( empty($email) || !is_email($email) ) { wp_send_json_error( 'Lütfen geçerli bir e-posta adresi girin.' ); }
    if ( empty($tc_kimlik) || strlen($tc_kimlik) != 11 ) { wp_send_json_error( 'T.C. Kimlik numaranızı eksik veya hatalı girdiniz (11 hane olmalıdır).' ); }
    if ( email_exists( $email ) ) { wp_send_json_error( 'Bu e-posta adresi zaten sistemimizde kayıtlı.' ); }

    // Üyeyi Oluştur
    $password = wp_generate_password( 12, false );
    $user_id = wp_create_user( $email, $password, $email );
    if ( is_wp_error( $user_id ) ) { wp_send_json_error( 'Hesap oluşturulamadı: ' . $user_id->get_error_message() ); }

    $fullname = sanitize_text_field($_POST['fullname']);
    $parts = explode(' ', $fullname);
    $last = array_pop($parts);
    $first = implode(' ', $parts);
    
    wp_update_user([ 'ID' => $user_id, 'first_name' => $first, 'last_name' => $last, 'display_name' => $fullname, 'role' => 'topluluk_uyesi' ]);

    // FRONTEND'DEN GELENLER OTOMATİK BEKLEMEDE OLUYOR
    update_user_meta($user_id, 'ybs_status', 'beklemede');

    // TEMEL BİLGİLER
    update_user_meta($user_id, 'ybs_student_no', sanitize_text_field($_POST['student_no'])); 
    update_user_meta($user_id, 'ybs_telefon', sanitize_text_field($_POST['phone']));
    update_user_meta($user_id, 'ybs_tc_kimlik', $tc_kimlik);
    update_user_meta($user_id, 'ybs_dogum_tarihi', sanitize_text_field($_POST['ybs_dogum_tarihi']));
    update_user_meta($user_id, 'ybs_linkedin', esc_url_raw($_POST['linkedin']));
    update_user_meta($user_id, 'ybs_sehir', sanitize_text_field($_POST['ybs_sehir']));
    update_user_meta($user_id, 'ybs_beden', sanitize_text_field($_POST['ybs_beden']));
    
    // ORGANİZASYON BİLGİLERİ (Görev Tanımı Eklendi)
    update_user_meta($user_id, 'ybs_departman', sanitize_text_field($_POST['department_name']));
    update_user_meta($user_id, 'ybs_gorev_tanimi', sanitize_text_field($_POST['duty_title']));
    
    // ACİL DURUM BİLGİLERİ
    update_user_meta($user_id, 'ybs_acil_kisi', sanitize_text_field($_POST['ybs_acil_kisi']));
    update_user_meta($user_id, 'ybs_acil_telefon', sanitize_text_field($_POST['ybs_acil_telefon']));
    update_user_meta($user_id, 'ybs_acil_yakinlik', sanitize_text_field($_POST['ybs_acil_yakinlik']));
    
    // SAĞLIK BİLGİLERİ
    update_user_meta($user_id, 'ybs_kan_grubu', sanitize_text_field($_POST['blood_type']));
    update_user_meta($user_id, 'ybs_beslenme', sanitize_text_field($_POST['ybs_beslenme']));
    update_user_meta($user_id, 'ybs_health_notes', sanitize_text_field($_POST['health_notes']));
    
    // KVKK
    update_user_meta($user_id, 'ybs_kvkk_onay', 1);

    // ==========================================
    // DOSYA YÜKLEME VE HATA YÖNETİMİ
    // ==========================================
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }

    $upload_overrides = array( 'test_form' => false );
    $upload_errors = array();

    // 1. Profil Fotoğrafı Kontrolü
    if ( ! empty( $_FILES['profile_photo']['name'] ) ) {
        // Dosya uzantısını doğrudan al
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        
        if(!in_array($ext, ['jpg', 'jpeg', 'png', 'heic', 'heif'])) {
            $upload_errors[] = "Fotoğraf sadece JPG, PNG veya iPhone (HEIC) formatında olmalıdır.";
        } else {
            $photo_move = wp_handle_upload( $_FILES['profile_photo'], $upload_overrides );
            if ( $photo_move && ! isset( $photo_move['error'] ) ) {
                update_user_meta( $user_id, 'ybs_fotograf', $photo_move['url'] );
            } else {
                $upload_errors[] = "Fotoğraf yüklenemedi: " . $photo_move['error'];
            }
        }
    } else {
        $upload_errors[] = "Profil fotoğrafı yüklemek zorunludur.";
    }

    // 2. CV (PDF) Kontrolü
    if ( ! empty( $_FILES['cv_file']['name'] ) ) {
        $file_type = wp_check_filetype(basename($_FILES['cv_file']['name']));
        if($file_type['ext'] !== 'pdf') {
            $upload_errors[] = "CV dosyası sadece PDF formatında olmalıdır.";
        } else {
            $cv_move = wp_handle_upload( $_FILES['cv_file'], $upload_overrides );
            if ( $cv_move && ! isset( $cv_move['error'] ) ) {
                update_user_meta( $user_id, 'ybs_cv_dosyasi', $cv_move['url'] );
            } else {
                $upload_errors[] = "CV yüklenemedi: " . $cv_move['error'];
            }
        }
    } else {
        $upload_errors[] = "CV (Özgeçmiş) dosyası yüklemek zorunludur.";
    }

    // Dosyalarda hata varsa uyar ama kaydı iptal etme (beklemede kalsın)
    if ( ! empty($upload_errors) ) {
        $error_msg = "Başvurunuz alındı (Beklemede) ANCAK dosyalarınız yüklenirken sorun çıktı:<br>• " . implode("<br>• ", $upload_errors);
        wp_send_json_error( $error_msg );
    }

    wp_send_json_success('Başvurunuz başarıyla alındı ve onay sürecine girdi. Dosyalarınız onaylandı.');
}
// =========================================================================
// EKİP ÜYESİ BAŞVURU İŞLEMİ
// =========================================================================
add_action( 'wp_ajax_ybs_team_apply_ajax', 'ybs_handle_team_apply' );
add_action( 'wp_ajax_nopriv_ybs_team_apply_ajax', 'ybs_handle_team_apply' );

function ybs_handle_team_apply() {
    check_ajax_referer( 'ybs_ajax_nonce', 'security' );

    $email = sanitize_email($_POST['email']);
    if ( empty($email) || !is_email($email) ) { wp_send_json_error( 'Lütfen geçerli bir e-posta adresi girin.' ); }
    if ( email_exists( $email ) ) { wp_send_json_error( 'Bu e-posta adresi ile zaten bir başvuru/kayıt bulunuyor.' ); }

    $password = wp_generate_password( 12, false );
    $user_id = wp_create_user( $email, $password, $email );
    if ( is_wp_error( $user_id ) ) { wp_send_json_error( 'Sistem hatası: Hesap oluşturulamadı.' ); }

    $fullname = sanitize_text_field($_POST['fullname']);
    $parts = explode(' ', $fullname);
    $last = array_pop($parts);
    $first = implode(' ', $parts);
    
    // Adaylara DOĞRUDAN 'ekip_uyesi' rolü veriyoruz
    wp_update_user([ 'ID' => $user_id, 'first_name' => $first, 'last_name' => $last, 'display_name' => $fullname, 'role' => 'ekip_uyesi' ]);

    // EKİP BAŞVURUSU OLDUĞUNU BELİRTEN ÖZEL ETİKET
    update_user_meta($user_id, 'ybs_is_team_applicant', 1);
    update_user_meta($user_id, 'ybs_status', 'beklemede');

    // Form Verileri
    update_user_meta($user_id, 'ybs_telefon', sanitize_text_field($_POST['phone']));
    update_user_meta($user_id, 'ybs_student_no', sanitize_text_field($_POST['student_no']));
    update_user_meta($user_id, 'ybs_linkedin', esc_url_raw($_POST['linkedin']));
    update_user_meta($user_id, 'ybs_departman', sanitize_text_field($_POST['department_name']));
    update_user_meta($user_id, 'ybs_tecrube', sanitize_text_field($_POST['has_experience']));
    update_user_meta($user_id, 'ybs_experience_detail', sanitize_textarea_field($_POST['experience_detail']));
    update_user_meta($user_id, 'ybs_motivation', sanitize_textarea_field($_POST['motivation']));
	// Form Verileri kayıt satırlarının arasına eklenecek:
    update_user_meta($user_id, 'ybs_bolum', sanitize_text_field($_POST['ybs_bolum']));
    update_user_meta($user_id, 'ybs_kvkk_onay', 1);

    wp_send_json_success('Başvurunuz başarıyla alındı!');
}


// =========================================================================
// BİLET SORGULAMA İŞLEMİ (AJAX)
// =========================================================================
add_action('wp_ajax_ybs_check_my_ticket', 'ybs_check_my_ticket_func');
add_action('wp_ajax_nopriv_ybs_check_my_ticket', 'ybs_check_my_ticket_func');

function ybs_check_my_ticket_func() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_reservations';

    $contact = sanitize_text_field($_POST['contact']);
    if(empty($contact)) {
        wp_send_json_error(['message' => 'Lütfen E-Posta veya Telefon numaranızı giriniz.']);
    }

    // Telefon numarası girilmişse formatı temizle
    $clean_phone = preg_replace('/[^0-9]/', '', $contact);

    // E-posta veya Telefon ile veritabanında arama yap
    $records = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE user_email = %s OR user_phone = %s",
        $contact, $clean_phone
    ));

    if(empty($records)) {
        wp_send_json_error(['message' => 'Bu bilgilere ait kayıtlı bir bilet bulunamadı.']);
    }

    $user_name = $records[0]->user_name;
    $token = $records[0]->bilet_token;
    $seats = [];

    foreach($records as $rec) {
        $seats[] = $rec->seat_id;
    }

    // Tüm bilgileri frontend'e gönder
    wp_send_json_success([
        'name' => $user_name,
        'seats' => $seats,
        'token' => $token
    ]);
}
