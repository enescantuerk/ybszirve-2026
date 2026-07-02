<?php
/**
 * YBS Zirvesi 2026 functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package YBS_Zirvesi_2026
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function duybs_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on YBS Zirvesi 2026, use a find and replace
		* to change 'duybs' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'duybs', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
            'primary' => esc_html__( 'Ana Menü (Header)', 'duybs' ),
            'mobile'  => esc_html__( 'Mobil Menü', 'duybs' ), // İstersen ayrı mobil menü yapabilirsin
        )
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'duybs_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'duybs_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function duybs_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'duybs_content_width', 640 );
}
add_action( 'after_setup_theme', 'duybs_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function duybs_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'duybs' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'duybs' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'duybs_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function duybs_scripts() {
	wp_enqueue_style( 'duybs-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'duybs-style', 'rtl', 'replace' );

	wp_enqueue_script( 'duybs-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'duybs_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load WooCommerce compatibility file.
 */
if ( class_exists( 'WooCommerce' ) ) {
	require get_template_directory() . '/inc/woocommerce.php';
}


function duybs_theme_scripts() {
    // 1. Ana Stil Dosyası (style.css)
    wp_enqueue_style( 'duybs-style', get_stylesheet_uri() );

    // 2. Tools CSS (assets/css/tools.css)
    // get_template_directory_uri() -> Tema klasörünün URL'sini verir
    wp_enqueue_style( 
        'duybs-tools', 
        get_template_directory_uri() . '/assets/css/tools.css', 
        array(), // Bağımlılık yok
        '1.0',   // Versiyon
        'all'    // Medya tipi
    );

    // 3. Main JS (assets/js/main.js)
    wp_enqueue_script( 
        'duybs-main-js', 
        get_template_directory_uri() . '/assets/js/main.js', 
        array(), // jQuery bağımlılığı yok (Vanilla JS yazdık)
        '1.0', 
        true     // ÖNEMLİ: true = Footer'da yükle (HTML yüklendikten sonra çalışsın)
    );
}

// Kancayı (Hook) çalıştır
add_action( 'wp_enqueue_scripts', 'duybs_theme_scripts' );




// Admin Panel Topluluk Sistemi
require_once get_template_directory() . '/inc/admin-panel/init.php';
require_once get_template_directory() . '/inc/admin-panel/user-fields.php';
require_once get_template_directory() . '/inc/admin-panel/cert-sender.php';
require_once get_template_directory() . '/inc/katilimci-cv.php';

/**
 * Herkese Açık Dosyaları Listeleme Shortcode'u (DÜZELTİLMİŞ VERSİYON)
 * Kullanım: [ybs_dosyalar]
 */
function ybs_public_files_shortcode($atts) {
    $atts = shortcode_atts(['folder' => ''], $atts);
    
    $public_folders = get_option('ybs_public_folders', []);
    $root = ABSPATH . 'dosyalar/';
    $base_url = site_url('/dosyalar/');

    // URL'den veya Shortcode'dan klasörü al
    $req_folder = '';
    if (isset($_GET['ybs_folder']) && !empty($_GET['ybs_folder'])) {
        $req_folder = sanitize_text_field($_GET['ybs_folder']);
    } elseif (!empty($atts['folder'])) {
        $req_folder = $atts['folder'];
    }

    ob_start();
    echo '<div class="ybs-public-drive">';

    // --- DURUM 1: DOSYA LİSTELEME ---
    if ( !empty($req_folder) ) {
        
        if ( in_array($req_folder, $public_folders) && is_dir($root . $req_folder) ) {
            
            // Geri Dön Linki
            $back_link = remove_query_arg('ybs_folder');
            // Eğer sayfa kendisi parametreli geldiyse temizle
            if(strpos($back_link, '?') === false && !empty($_SERVER['QUERY_STRING'])) {
                 $back_link = strtok($_SERVER["REQUEST_URI"], '?');
            }
            
            echo '<div class="yp-header">
                    <a href="'.$back_link.'" class="yp-back">← Klasörlere Dön</a>
                    <h3>'.ucfirst(basename($req_folder)).'</h3>
                  </div>';

            $files = scandir($root . $req_folder);
            echo '<div class="yp-file-list">';
            
            $found = false;
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === 'index.php') continue;
                
                $file_url = $base_url . $req_folder . '/' . $file;
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                $ext_label = strtoupper($ext); // Örn: PDF, JPG
                
                // Türüne Göre Renk Sınıfı
                $badge_class = 'default';
                if(in_array($ext, ['jpg','jpeg','png','gif'])) $badge_class = 'img';
                if($ext === 'pdf') $badge_class = 'pdf';
                if(in_array($ext, ['doc','docx'])) $badge_class = 'word';
                if(in_array($ext, ['xls','xlsx','csv'])) $badge_class = 'excel';
                if(in_array($ext, ['zip','rar'])) $badge_class = 'zip';

                echo '
                <a href="'.$file_url.'" target="_blank" class="yp-file-row">
                    <span class="yp-type-badge '.$badge_class.'">'.$ext_label.'</span>
                    
                    <span class="yp-f-name">'.$file.'</span>
                    <span class="yp-f-action">İndir ↓</span>
                </a>';
                $found = true;
            }
            
            if(!$found) echo '<p class="yp-empty">Bu klasör boş.</p>';
            echo '</div>';

        } else {
            echo '<div class="yp-error">Erişim izni yok veya klasör bulunamadı. <a href="'.remove_query_arg('ybs_folder').'">Geri Dön</a></div>';
        }
    } 
    
    // --- DURUM 2: KLASÖR LİSTELEME ---
    else {
        echo '<h3>Dokümanlar & Dosyalar</h3>';
        if(empty($public_folders)) {
            echo '<p>Henüz paylaşılan klasör yok.</p>';
        } else {
            echo '<div class="yp-grid">';
            foreach ($public_folders as $path) {
                if (is_dir($root . $path)) {
                    $link = add_query_arg('ybs_folder', $path);
                    $name = basename($path);
                    echo '
                    <a href="'.$link.'" class="yp-folder-card">
                        <div class="yp-icon">📂</div>
                        <div class="yp-name">'.ucfirst($name).'</div>
                    </a>';
                }
            }
            echo '</div>';
        }
    }

    echo '</div>'; 

    // CSS STİLLERİ
    echo '
    <style>
        .ybs-public-drive { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 900px; margin: 0 auto; }
        .ybs-public-drive h3 { margin: 0; color: #333; font-weight:700; font-size: 1.2rem; }
        
        /* Klasör Grid */
        .yp-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; margin-top: 20px; }
        .yp-folder-card { 
            background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; 
            padding: 20px 10px; text-align: center; text-decoration: none; color: #334155; 
            transition: all 0.2s; display:block;
        }
        .yp-folder-card:hover { transform: translateY(-3px); border-color: #00B5AD; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        .yp-icon { font-size: 32px; margin-bottom: 8px; }
        .yp-name { font-weight: 600; font-size: 14px; overflow:hidden; text-overflow:ellipsis; }

        /* Dosya Listesi Header */
        .yp-header { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
        .yp-back { 
            background: #f1f5f9; padding: 6px 12px; border-radius: 6px; text-decoration: none; 
            color: #475569; font-size: 13px; font-weight: 600; 
        }
        .yp-back:hover { background: #e2e8f0; color: #0f172a; }

        /* Dosya Satırı */
        .yp-file-list { display: flex; flex-direction: column; gap: 8px; }
        .yp-file-row { 
            display: flex; align-items: center; background: #fff; border: 1px solid #e2e8f0; 
            padding: 10px 15px; border-radius: 6px; text-decoration: none; color: #333; transition: 0.2s; 
        }
        .yp-file-row:hover { border-color: #00B5AD; background: #f0fdfa; }

        /* --- DOSYA TÜRÜ ETİKETİ (BADGE) --- */
        .yp-type-badge {
            display: inline-block;
            font-size: 10px; font-weight: 800; padding: 4px 6px; border-radius: 4px;
            margin-right: 12px; min-width: 35px; text-align: center; color: #fff;
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .yp-type-badge.default { background: #94a3b8; }
        .yp-type-badge.pdf { background: #ef4444; } /* Kırmızı */
        .yp-type-badge.img { background: #8b5cf6; } /* Mor */
        .yp-type-badge.word { background: #3b82f6; } /* Mavi */
        .yp-type-badge.excel { background: #10b981; } /* Yeşil */
        .yp-type-badge.zip { background: #f59e0b; } /* Turuncu */

        .yp-f-name { flex: 1; font-weight: 500; font-size: 14px; }
        .yp-f-action { font-size: 12px; color: #00B5AD; font-weight: 700; white-space: nowrap; }
        
        .yp-error { background: #fee2e2; color: #991b1b; padding: 20px; border-radius: 8px; text-align:center; }
        .yp-empty { color: #64748b; font-style: italic; }
    </style>
    ';

    return ob_get_clean();
}
// Bu satırın functions.php'de mutlaka olması lazım:
 add_shortcode('ybs_dosyalar', 'ybs_public_files_shortcode');



// --- VERİTABANI TABLOSUNU OTOMATİK OLUŞTURMA ---
// Bu fonksiyon artık yalnızca geri uyumluluk için tutulmaktadır.
// Güncel şema yönetimi inc/template-functions.php içindeki ybs_setup_database() tarafından yapılır.
function ybs_create_reservations_table() {
    // ybs_setup_database() zaten tam şemayı yönetiyor; burası ona yönlendirir.
    if ( function_exists('ybs_setup_database') ) {
        ybs_setup_database();
    }
}

// Bu fonksiyonu temanızı aktif ettiğinizde çalıştırır
add_action('after_switch_theme', 'ybs_create_reservations_table');

// Ekstra Güvenlik: Admin paneline girişte tablo yoksa oluştur (Site taşınırsa devreye girer)
add_action('admin_init', 'ybs_check_table_exists');
function ybs_check_table_exists() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_reservations';
    
    // Eğer tablo veritabanında yoksa oluşturma fonksiyonunu çağır
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        ybs_create_reservations_table();
    }
}




// =========================================================================
// YBS ZİRVE - REZERVASYON ADMİN AJAX FONKSİYONLARI (GÜNCEL)
// =========================================================================

// NOT: ybs_admin_manual_bulk_add aksiyonu inc/template-functions.php içindeki
// ybs_admin_manual_bulk_add_func tarafından yönetilmektedir.

// NOT: ybs_admin_toggle_multi ve ybs_admin_delete_seat_all aksiyonları
// inc/template-functions.php içinde yönetilmektedir.

// =========================================================================
// 1. VERİTABANI: SPONSORLUK MAİL KAYITLARI (LOG TABLOSU)
// =========================================================================
add_action('admin_init', 'ybs_setup_sponsor_log_table');
function ybs_setup_sponsor_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_sponsor_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        recipient_email varchar(100) NOT NULL,
        recipient_name varchar(150) NOT NULL,
        company_name varchar(150) NOT NULL,
        status varchar(50) DEFAULT 'success',
        error_msg text,
        sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// =========================================================================
// 2. ADMİN MENÜSÜ VE ARAYÜZ
// =========================================================================
add_action('admin_menu', 'ybs_sponsor_mail_menu');
function ybs_sponsor_mail_menu() {
    add_menu_page(
        'Sponsorluk', 
        'Sponsorluk', 
        'manage_options', 
        'ybs-sponsor-mail', 
        'ybs_sponsor_mail_page', 
        'dashicons-email-alt', 
        7 
    );
}

function ybs_sponsor_mail_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_sponsor_logs';

    // Log Silme İşlemi
    if (isset($_GET['delete_log']) && current_user_can('manage_options')) {
        $wpdb->delete($table, ['id' => intval($_GET['delete_log'])]);
        echo '<div class="notice notice-success is-dismissible"><p>Kayıt başarıyla silindi.</p></div>';
    }

    // Geçmiş Kayıtları Çek
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC");
    ?>
    <div class="wrap" style="font-family: -apple-system, sans-serif;">
        <h1 class="wp-heading-inline">Sponsorluk Maili Gönderimi</h1>
        <hr class="wp-header-end">

        <div style="display: flex; gap: 30px; margin-top: 20px; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 350px; max-width: 450px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Yeni Mail Gönder</h3>
                    <p style="font-size: 13px; color: #666;">Aşağıdaki bilgileri doldurun. Sistem mail şablonunu otomatik oluşturup gönderecektir. Yanıtlar <b>info@duybs.com</b> adresine düşer.</p>

                    <form id="sponsor-mail-form">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Hedef E-Posta Adresi</label>
                            <input type="email" id="sm-email" class="widefat" required placeholder="ornek@sirket.com">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Hitap (Sayın ....)</label>
                            <input type="text" id="sm-hitap" class="widefat" required placeholder="Örn: Ahmet Bey / İnsan Kaynakları Müdürü">
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Kurum / Şirket Adı</label>
                            <input type="text" id="sm-kurum" class="widefat" required placeholder="Örn: X Teknoloji A.Ş.">
                        </div>

                        <button type="submit" id="btn-send-mail" class="button button-primary button-large" style="width: 100%; text-align:center;">Hemen Gönder</button>
                        <div id="sm-response" style="margin-top: 15px; font-weight: bold; text-align: center;"></div>
                    </form>
                </div>
            </div>

            <div style="flex: 2; min-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Gönderim Geçmişi</h3>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Kurum Adı</th>
                                <th>Yetkili</th>
                                <th>E-Posta</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="6" style="text-align:center;">Henüz mail gönderimi yapılmadı.</td></tr>
                            <?php else: ?>
                                <?php foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($log->sent_at)); ?></td>
                                    <td><strong><?php echo esc_html($log->company_name); ?></strong></td>
                                    <td><?php echo esc_html($log->recipient_name); ?></td>
                                    <td><?php echo esc_html($log->recipient_email); ?></td>
                                    <td>
                                        <?php if($log->status == 'success'): ?>
                                            <span style="color:#10b981; font-weight:bold;">✓ Başarılı</span>
                                        <?php else: ?>
                                            <span style="color:#ef4444; font-weight:bold;" title="<?php echo esc_attr($log->error_msg); ?>">❌ Hata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=ybs-sponsor-mail&delete_log=<?php echo $log->id; ?>" style="color:red; text-decoration:none;" onclick="return confirm('Kayıt silinsin mi?');">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('sponsor-mail-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-send-mail');
        const resDiv = document.getElementById('sm-response');
        const email = document.getElementById('sm-email').value;
        const hitap = document.getElementById('sm-hitap').value;
        const kurum = document.getElementById('sm-kurum').value;

        btn.disabled = true;
        btn.innerText = "Gönderiliyor, lütfen bekleyin...";
        resDiv.innerHTML = "";

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_send_sponsor_mail_ajax');
        fd.append('email', email);
        fd.append('hitap', hitap);
        fd.append('kurum', kurum);

        fetch(ajaxurl, {
            method: 'POST',
            body: fd,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                resDiv.style.color = '#10b981';
                resDiv.innerText = "✅ " + res.data;
                setTimeout(() => location.reload(), 1500); // Sayfayı yenile ki tablo güncellensin
            } else {
                resDiv.style.color = '#ef4444';
                resDiv.innerText = "❌ Hata: " + res.data;
                btn.disabled = false;
                btn.innerText = "Hemen Gönder";
            }
        })
        .catch(err => {
            resDiv.style.color = '#ef4444';
            resDiv.innerText = "❌ Sunucu bağlantı hatası.";
            btn.disabled = false;
            btn.innerText = "Hemen Gönder";
        });
    });
    </script>
    <?php
}

// =========================================================================
// 3. AJAX: MAİLİ GÖNDER VE VERİTABANINA KAYDET
// =========================================================================
add_action('wp_ajax_ybs_send_sponsor_mail_ajax', 'ybs_send_sponsor_mail_func');
function ybs_send_sponsor_mail_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    $email = sanitize_email($_POST['email']);
    $hitap = sanitize_text_field($_POST['hitap']);
    $kurum = sanitize_text_field($_POST['kurum']);

    if(empty($email) || empty($hitap) || empty($kurum)) {
        wp_send_json_error('Lütfen tüm alanları doldurun.');
    }

    $subject = '10. Ulusal Yönetim Bilişim Sistemleri Zirvesi Sponsorluk Görüşmesi';
    
    // YANIT ADRESİNİ info@duybs.com OLARAK ZORLUYORUZ
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: 10. Ulusal YBS Zirvesi <info@duybs.com>'
    );

    // PROFESYONEL HTML MAİL TASLAĞI (Değişkenler Entegre Edildi)
    $message = "
    <div style='font-family: Arial, Helvetica, sans-serif; font-size: 15px; line-height: 1.6; color: #333333; max-width: 650px; margin: 0 auto; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;'>
        
        <div style='background-color: #1e3a8a; padding: 25px; text-align: center; color: #ffffff;'>
            <h2 style='margin: 0; font-size: 20px; font-weight: normal; letter-spacing: 1px;'>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu</h2>
        </div>

        <div style='padding: 30px;'>
            <p style='margin-top: 0;'>Sayın <strong>$hitap</strong>,</p>
            
            <p>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu olarak, bölümümüzün ve sektörümüzün en prestijli etkinliklerinden biri olan <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>’ne ev sahipliği yapacak olmanın heyecanını yaşıyoruz.</p>
            
            <p>Türkiye’nin dört bir yanından gelen akademisyenleri, sektör liderlerini ve yüzlerce geleceğin bilişim profesyonelini Düzce’de bir araya getirecek olan bu zirve; sadece üniversitemiz için değil, şehrimizin sanayi ve ticaret potansiyelinin tanıtımı açısından da büyük bir önem taşımaktadır.</p>
            
            <p><strong>$kurum</strong>'nın desteğini yanımızda hissetmek, etkinliğimizin niteliğini ve bölgemize olan katkısını bir üst seviyeye taşıyacaktır. Kurumunuzun vizyonuyla örtüşen sponsorluk kategorilerimiz ve iş birliği modellerimiz hakkında detaylı bilgi paylaşmak, zirvemizin sunduğu görünürlük fırsatlarını aktarmak isteriz.</p>
            
            <div style='text-align: center; margin: 35px 0;'>
                <a href='https://2026.ybszirve.org.tr/dosyalar/Kitapciklar/sponsorluk_kitapcigi.pdf' target='_blank' style='background-color: #1e3a8a; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 15px;'>Sponsorluk Dosyamızı İnceleyin</a>
            </div>
            
            <p>Uygun görmeniz halinde, detayları görüşmek üzere sizleri ziyaret etmekten veya çevrim içi bir toplantıda bir araya gelmekten onur duyarız.</p>
            
            <p>Desteğinizin, bilişim dünyasının geleceğine ve ülkemizin dijital dönüşüm hedeflerine katacağı değer için şimdiden teşekkür ederiz.</p>
            
            <hr style='border: 0; border-top: 1px solid #e5e7eb; margin: 30px 0;'>
            
            <p style='margin-bottom: 5px; font-weight: bold;'>Saygılarımızla,</p>
            <p style='margin-top: 0;'>
                <strong>Adem Demiröz</strong><br>
                <span style='color: #666;'>10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi<br>Genel Koordinatörü</span><br>
                <span style='color: #1e3a8a; font-weight: bold;'>Tel:</span> 0543 873 51 67<br>
                <span style='color: #1e3a8a; font-weight: bold;'>E-posta:</span> ademdemiroz19@gmail.com
            </p>
        </div>

        <div style='background-color: #f3f4f6; padding: 15px; text-align: center; font-size: 12px; color: #9ca3af;'>
            &copy; 2026 YBS Zirvesi Organizasyon Komitesi
        </div>

    </div>
    ";
    // Maili Gönder
    $is_sent = wp_mail($email, $subject, $message, $headers);

    // Veritabanına Logla
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_sponsor_logs';

    if($is_sent) {
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'company_name'    => $kurum,
            'status'          => 'success',
            'error_msg'       => '',
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_success('Mail başarıyla gönderildi ve listeye eklendi.');
    } else {
        global $phpmailer;
        $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'company_name'    => $kurum,
            'status'          => 'error',
            'error_msg'       => $error_msg,
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_error('Mail iletilemedi. Detay: ' . $error_msg);
    }
}


// =========================================================================
// 1. VERİTABANI: ÖZEL DAVET MAİL KAYITLARI (LOG TABLOSU)
// =========================================================================
add_action('admin_init', 'ybs_setup_davet_log_table');
function ybs_setup_davet_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_davet_logs'; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        recipient_email varchar(100) NOT NULL,
        recipient_name varchar(150) NOT NULL,
        company_name varchar(150) NOT NULL,
        status varchar(50) DEFAULT 'success',
        error_msg text,
        sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// =========================================================================
// 2. ADMİN MENÜSÜ VE ARAYÜZ (AYRI BİR EKRAN)
// =========================================================================
add_action('admin_menu', 'ybs_davet_mail_menu');
function ybs_davet_mail_menu() {
    add_menu_page(
        'Özel Davetler', 
        'Özel Davet', 
        'manage_options', 
        'ybs-davet-mail', 
        'ybs_davet_mail_page', 
        'dashicons-tickets-alt', 
        8 
    );
}

function ybs_davet_mail_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_davet_logs';

    if (isset($_GET['delete_log']) && current_user_can('manage_options')) {
        $wpdb->delete($table, ['id' => intval($_GET['delete_log'])]);
        echo '<div class="notice notice-success is-dismissible"><p>Davet kaydı başarıyla silindi.</p></div>';
    }

    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC");
    ?>
    <div class="wrap" style="font-family: -apple-system, sans-serif;">
        <h1 class="wp-heading-inline">Zirve Özel Davet Gönderimi</h1>
        <hr class="wp-header-end">

        <div style="display: flex; gap: 30px; margin-top: 20px; flex-wrap: wrap;">
            
            <div style="flex: 1; min-width: 350px; max-width: 450px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Yeni Davetiye Gönder</h3>
                    <p style="font-size: 13px; color: #666;">Bilgileri doldurduğunuzda sistem modern ve özel tasarımlı davetiyeyi iletecektir. Yanıtlar <b>info@duybs.com</b> adresine düşer.</p>

                    <form id="davet-mail-form">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Hedef E-Posta Adresi</label>
                            <input type="email" id="dm-email" class="widefat" required placeholder="ornek@sirket.com">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Adı Soyadı (Sayın ....)</label>
                            <input type="text" id="dm-hitap" class="widefat" required placeholder="Örn: Ahmet Yılmaz">
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px;">Kurum / Unvan</label>
                            <input type="text" id="dm-kurum" class="widefat" required placeholder="Örn: X Teknoloji CEO'su">
                        </div>

                        <button type="submit" id="btn-send-davet" class="button button-primary button-large" style="width: 100%; text-align:center;">Daveti Gönder</button>
                        <div id="dm-response" style="margin-top: 15px; font-weight: bold; text-align: center;"></div>
                    </form>
                </div>
            </div>

            <div style="flex: 2; min-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Davet Geçmişi</h3>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Kurum / Unvan</th>
                                <th>Adı Soyadı</th>
                                <th>E-Posta</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="6" style="text-align:center;">Henüz davet gönderimi yapılmadı.</td></tr>
                            <?php else: ?>
                                <?php foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($log->sent_at)); ?></td>
                                    <td><strong><?php echo esc_html($log->company_name); ?></strong></td>
                                    <td><?php echo esc_html($log->recipient_name); ?></td>
                                    <td><?php echo esc_html($log->recipient_email); ?></td>
                                    <td>
                                        <?php if($log->status == 'success'): ?>
                                            <span style="color:#10b981; font-weight:bold;">✓ İletildi</span>
                                        <?php else: ?>
                                            <span style="color:#ef4444; font-weight:bold;" title="<?php echo esc_attr($log->error_msg); ?>">❌ Hata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=ybs-davet-mail&delete_log=<?php echo $log->id; ?>" style="color:red; text-decoration:none;" onclick="return confirm('Kayıt silinsin mi?');">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.getElementById('davet-mail-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btn = document.getElementById('btn-send-davet');
        const resDiv = document.getElementById('dm-response');
        const email = document.getElementById('dm-email').value;
        const hitap = document.getElementById('dm-hitap').value;
        const kurum = document.getElementById('dm-kurum').value;

        btn.disabled = true;
        btn.innerText = "Davet İletiliyor...";
        resDiv.innerHTML = "";

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_send_davet_mail_ajax');
        fd.append('email', email);
        fd.append('hitap', hitap);
        fd.append('kurum', kurum);

        fetch(ajaxurl, {
            method: 'POST',
            body: fd,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                resDiv.style.color = '#10b981';
                resDiv.innerText = "✅ " + res.data;
                setTimeout(() => location.reload(), 1500);
            } else {
                resDiv.style.color = '#ef4444';
                resDiv.innerText = "❌ Hata: " + res.data;
                btn.disabled = false;
                btn.innerText = "Daveti Gönder";
            }
        })
        .catch(err => {
            resDiv.style.color = '#ef4444';
            resDiv.innerText = "❌ Sunucu bağlantı hatası.";
            btn.disabled = false;
            btn.innerText = "Daveti Gönder";
        });
    });
    </script>
    <?php
}

// =========================================================================
// 3. AJAX: DAVET MAİLİNİ GÖNDER VE VERİTABANINA KAYDET
// =========================================================================
add_action('wp_ajax_ybs_send_davet_mail_ajax', 'ybs_send_davet_mail_func');
function ybs_send_davet_mail_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    $email = sanitize_email($_POST['email']);
    $hitap = sanitize_text_field($_POST['hitap']);
    $kurum = sanitize_text_field($_POST['kurum']);

    if(empty($email) || empty($hitap) || empty($kurum)) {
        wp_send_json_error('Lütfen tüm alanları doldurun.');
    }

    $subject = '10. Ulusal YBS Öğrenci Zirvesi - Özel Davet';
    
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: 10. Ulusal YBS Zirvesi <info@duybs.com>'
    );

    // =========================================================================
    // MODERN & KURUMSAL MAİL ŞABLONU
    // =========================================================================
    $message = "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>
        
        <div style='background-color: #0f172a; padding: 40px 30px; text-align: center; position: relative;'>
            <div style='margin-bottom: 25px; display: block; text-align: center;'>
                <img src='https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/zirvelogo.png' alt='Zirve Logo' style='height: 45px; vertical-align: middle; margin-right: 15px; filter: brightness(0) invert(1); -webkit-filter: brightness(0) invert(1);'>
                <span style='color: #475569; font-size: 24px; vertical-align: middle;'>|</span>
                <img src='https://2026.ybszirve.org.tr/wp-content/themes/duybs/assets/img/duybs-black.png' alt='DÜYBS Logo' style='height: 45px; vertical-align: middle; margin-left: 15px; filter: brightness(0) invert(1); -webkit-filter: brightness(0) invert(1);'>
            </div>
            
            <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase;'>10. Ulusal YBS Öğrenci Zirvesi</h1>
            <div style='display: inline-block; background: #3b82f6; color: #fff; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; margin-top: 15px;'>Özel Davet</div>
        </div>

        <div style='padding: 40px 30px; color: #334155; font-size: 15px; line-height: 1.8;'>
            <p style='margin-top: 0; font-size: 16px;'>Sayın <strong>$hitap</strong>,</p>
            
            <p>Türkiye’nin teknoloji ve yönetim dünyasını bir araya getiren en köklü etkinliklerinden biri olan <strong>10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi</strong>’ne bu yıl Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu olarak ev sahipliği yapmanın heyecanını yaşıyoruz.</p>
            
            <p>Teknoloji dünyasındaki güncel paylaşımlarınızı ve sektöre kattığınız vizyonu yakından takip eden bir ekip olarak, Türkiye’nin dört bir yanından gelecek yüzlerce bilişim profesyoneli, akademisyen ve teknoloji tutkunu gencin bir araya geleceği bu dev organizasyonda sizi de aramızda görmeyi çok arzu ediyoruz.</p>
            
            <p>Sizin gibi ilham veren isimlerin katılımı, hem etkinliğimizin niteliğini bir üst seviyeye taşıyacak hem de dijital dönüşüm hedeflerimize büyük bir ivme kazandıracaktır.</p>
            
            <p>Zirvemizin sunduğu geniş networking imkanları ve teknoloji odaklı kitlemizle yaratacağımız sinerjiyi, sizinle birlikte daha geniş kitlelere ulaştırmak istiyoruz.</p>
            
            <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin: 35px 0; text-align: center;'>
                <h3 style='margin: 0 0 15px 0; font-size: 16px; color: #0f172a;'>Zirve Detayları & Rehberler</h3>
                
                <div style='margin-bottom: 20px;'>
                    <a href='https://2026.ybszirve.org.tr/program/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);'>📅 Program Akışı</a>
                    
                    <a href='https://2026.ybszirve.org.tr/konusmacilar/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);'>🎤 Konuşmacılar</a>
                    
                    <a href='https://2026.ybszirve.org.tr/ulasim/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);'>📍 Ulaşım & Konaklama</a>
                </div>
                
                <a href='https://2026.ybszirve.org.tr/kitapciklar/?belge=participant' target='_blank' style='display: inline-block; background-color: #3b82f6; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);'>📥 Katılımcı Bilgilendirme Rehberi</a>
            </div>

            <p>Desteğinizin bilişim dünyasının geleceğine ve ülkemizin dijital dönüşüm yolculuğuna katacağı değer için şimdiden teşekkür ederiz.</p>
            
            <p style='margin-top: 30px; margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 16px;'>Saygılarımızla,</p>
            <p style='margin-top: 5px; color: #64748b;'>Düzce Üniversitesi YBS Topluluğu</p>
        </div>

        <div style='background-color: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 25px 30px; text-align: center;'>
            <p style='margin: 0 0 15px 0; font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;'>İletişim Bilgileri</p>
            <p style='margin: 0; font-size: 15px; color: #0f172a; font-weight: 800;'>Adem Demiröz</p>
            <p style='margin: 5px 0 0 0; font-size: 14px; color: #475569;'>Tel: <a href='tel:+905438735167' style='color: #3b82f6; text-decoration: none; font-weight: 600;'>0543 873 51 67</a></p>
            <p style='margin: 5px 0 0 0; font-size: 14px; color: #475569;'>E-posta: <a href='mailto:ademdemiroz19@gmail.com' style='color: #3b82f6; text-decoration: none; font-weight: 600;'>ademdemiroz19@gmail.com</a></p>
        </div>

    </div>
    ";

    // Maili Gönder
    $is_sent = wp_mail($email, $subject, $message, $headers);

    // Veritabanına Logla
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_davet_logs';

    if($is_sent) {
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'company_name'    => $kurum,
            'status'          => 'success',
            'error_msg'       => '',
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_success('Davet başarıyla iletildi ve kayıt altına alındı.');
    } else {
        global $phpmailer;
        $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'company_name'    => $kurum,
            'status'          => 'error',
            'error_msg'       => $error_msg,
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_error('Davet iletilemedi. Detay: ' . $error_msg);
    }
}


// =========================================================================
// 1. KONUŞMACILAR - ARKA PLAN VERİTABANI ALTYAPISI (GİZLİ CPT)
// =========================================================================
add_action('init', 'ybs_register_speakers_cpt_hidden');
function ybs_register_speakers_cpt_hidden() {
    register_post_type('konusmaci', array(
        'public' => false,
        'show_ui' => false, // Standart WP arayüzünü gizliyoruz, kendi sayfamızı yapacağız
        'supports' => array('title', 'thumbnail', 'page-attributes')
    ));
}

// Resim yükleme kütüphanesini (Media Uploader) admin sayfasına dahil et
add_action('admin_enqueue_scripts', 'ybs_speaker_admin_scripts');
function ybs_speaker_admin_scripts($hook) {
    if($hook != 'toplevel_page_ybs-speakers') return;
    wp_enqueue_media();
}

// =========================================================================
// 2. ADMİN MENÜSÜ VE POPUP ARAYÜZÜ
// =========================================================================
add_action('admin_menu', 'ybs_speaker_custom_menu');
function ybs_speaker_custom_menu() {
    add_menu_page('Konuşmacılar', 'Konuşmacılar', 'manage_options', 'ybs-speakers', 'ybs_speakers_page_html', 'dashicons-microphone', 6);
}

function ybs_speakers_page_html() {
    // Tüm konuşmacıları çek
    $args = array('post_type' => 'konusmaci', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC');
    $speakers = new WP_Query($args);
    ?>
    <div class="wrap" style="font-family: -apple-system, sans-serif;">
        <h1 class="wp-heading-inline">Konuşmacı Yönetimi</h1>
        <button class="page-title-action" onclick="openSpeakerModal()">Yeni Ekle</button>
        <hr class="wp-header-end">

        <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; margin-top: 20px; padding: 15px;">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 80px;">Sıra</th>
                        <th style="width: 80px;">Fotoğraf</th>
                        <th style="width: 25%;">Ad Soyad</th>
                        <th style="width: 25%;">Unvan</th>
                        <th>Hakkında / Oturum</th>
                        <th style="width: 150px;">İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$speakers->have_posts()): ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">Henüz konuşmacı eklenmedi.</td></tr>
                    <?php else: ?>
                        <?php while($speakers->have_posts()): $speakers->the_post(); 
                            $id = get_the_ID();
                            $unvan = get_post_meta($id, 'ybs_k_unvan', true);
                            $hakkinda = get_post_meta($id, 'ybs_k_hakkinda', true);
                            $linkedin = get_post_meta($id, 'ybs_k_linkedin', true);
                            $img_url = get_the_post_thumbnail_url($id, 'thumbnail');
                            $img_id = get_post_thumbnail_id($id);
                            $order = get_post_field('menu_order', $id);
                        ?>
                        <tr>
                            <td style="font-weight:bold; font-size:16px;"><?php echo $order; ?></td>
                            <td>
                                <?php if($img_url): ?>
                                    <img src="<?php echo esc_url($img_url); ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:50px; height:50px; border-radius:50%; background:#eee; display:flex; align-items:center; justify-content:center; color:#999; font-size:10px;">Yok</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php the_title(); ?></strong></td>
                            <td><?php echo esc_html($unvan); ?></td>
                            <td><?php echo esc_html($hakkinda); ?></td>
                            <td>
                                <button class="button" onclick="editSpeaker(<?php echo $id; ?>, '<?php echo esc_attr(get_the_title()); ?>', '<?php echo esc_attr($unvan); ?>', '<?php echo esc_attr($hakkinda); ?>', '<?php echo esc_attr($linkedin); ?>', <?php echo $order; ?>, <?php echo $img_id ? $img_id : 0; ?>, '<?php echo esc_url($img_url); ?>')">Düzenle</button>
                                <button class="button" style="color:red; border-color:red;" onclick="deleteSpeaker(<?php echo $id; ?>)">Sil</button>
                            </td>
                        </tr>
                        <?php endwhile; wp_reset_postdata(); ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="speaker-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.7); z-index:99999; justify-content:center; align-items:center;">
        <div style="background:#fff; padding:25px 35px; border-radius:12px; width:450px; max-width:90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <h3 style="margin:0; font-size:18px;" id="modal-title">Konuşmacı Ekle</h3>
                <button type="button" onclick="closeSpeakerModal()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#999;">&times;</button>
            </div>

            <form id="speaker-form">
                <input type="hidden" id="sp-id" value="0">
                <input type="hidden" id="sp-img-id" value="0">

                <div style="display:flex; gap:15px; margin-bottom:15px;">
                    <div style="flex:2;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Ad Soyad *</label>
                        <input type="text" id="sp-name" required class="widefat">
                    </div>
                    <div style="flex:1;">
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Sıra</label>
                        <input type="number" id="sp-order" value="0" class="widefat" placeholder="0">
                    </div>
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Unvan / Şirket *</label>
                    <input type="text" id="sp-unvan" required class="widefat" placeholder="Örn: İş Geliştirme Müdürü">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Hakkında (Oturum Adı vs.)</label>
                    <input type="text" id="sp-hakkinda" class="widefat" placeholder="Örn: Veri Kültürü ile Geleceğini Tasarla">
                </div>

                <div style="margin-bottom:15px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">LinkedIn Linki</label>
                    <input type="url" id="sp-linkedin" class="widefat" placeholder="https://linkedin.com/in/...">
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">Fotoğraf</label>
                    <div style="display:flex; align-items:center; gap:15px;">
                        <img id="sp-img-preview" src="" style="display:none; width:60px; height:60px; border-radius:50%; object-fit:cover; border:2px solid #ccc;">
                        <button type="button" class="button" id="btn-upload-img">Fotoğraf Seç</button>
                    </div>
                </div>

                <div style="text-align:right;">
                    <button type="button" class="button" onclick="closeSpeakerModal()">İptal</button>
                    <button type="submit" id="sp-save-btn" class="button button-primary">Kaydet</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal Aç / Kapat
    function openSpeakerModal() {
        document.getElementById('speaker-form').reset();
        document.getElementById('sp-id').value = "0";
        document.getElementById('sp-img-id').value = "0";
        document.getElementById('sp-img-preview').style.display = "none";
        document.getElementById('modal-title').innerText = "Konuşmacı Ekle";
        document.getElementById('speaker-modal').style.display = 'flex';
    }

    function closeSpeakerModal() {
        document.getElementById('speaker-modal').style.display = 'none';
    }

    // Düzenleme İçin Verileri Modala Çek
    function editSpeaker(id, name, unvan, hakkinda, linkedin, order, imgId, imgUrl) {
        document.getElementById('sp-id').value = id;
        document.getElementById('sp-name').value = name;
        document.getElementById('sp-unvan').value = unvan;
        document.getElementById('sp-hakkinda').value = hakkinda;
        document.getElementById('sp-linkedin').value = linkedin;
        document.getElementById('sp-order').value = order;
        
        document.getElementById('sp-img-id').value = imgId;
        if(imgUrl) {
            document.getElementById('sp-img-preview').src = imgUrl;
            document.getElementById('sp-img-preview').style.display = "block";
        } else {
            document.getElementById('sp-img-preview').style.display = "none";
        }
        
        document.getElementById('modal-title').innerText = "Konuşmacı Düzenle";
        document.getElementById('speaker-modal').style.display = 'flex';
    }

    // WordPress Media Uploader (Fotoğraf Seçici)
    let mediaUploader;
    document.getElementById('btn-upload-img').addEventListener('click', function(e) {
        e.preventDefault();
        if (mediaUploader) { mediaUploader.open(); return; }
        mediaUploader = wp.media({ title: 'Konuşmacı Fotoğrafı Seç', button: { text: 'Bunu Kullan' }, multiple: false });
        mediaUploader.on('select', function() {
            const attachment = mediaUploader.state().get('selection').first().toJSON();
            document.getElementById('sp-img-id').value = attachment.id;
            document.getElementById('sp-img-preview').src = attachment.url;
            document.getElementById('sp-img-preview').style.display = 'block';
        });
        mediaUploader.open();
    });

    // Kaydet İşlemi (AJAX)
    document.getElementById('speaker-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('sp-save-btn');
        btn.disabled = true;
        btn.innerText = "Kaydediliyor...";

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_save_speaker_ajax');
        fd.append('id', document.getElementById('sp-id').value);
        fd.append('name', document.getElementById('sp-name').value);
        fd.append('unvan', document.getElementById('sp-unvan').value);
        fd.append('hakkinda', document.getElementById('sp-hakkinda').value);
        fd.append('linkedin', document.getElementById('sp-linkedin').value);
        fd.append('order', document.getElementById('sp-order').value);
        fd.append('img_id', document.getElementById('sp-img-id').value);

        fetch(ajaxurl, { method: 'POST', body: fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
        .then(r => r.json()).then(res => {
            if(res.success) location.reload();
            else { alert("Hata oluştu."); btn.disabled = false; btn.innerText = "Kaydet"; }
        });
    });

    // Sil İşlemi (AJAX)
    function deleteSpeaker(id) {
        if(!confirm('Bu konuşmacıyı silmek istediğinize emin misiniz?')) return;
        const fd = new URLSearchParams();
        fd.append('action', 'ybs_delete_speaker_ajax');
        fd.append('id', id);
        fetch(ajaxurl, { method: 'POST', body: fd, headers: { 'Content-Type': 'application/x-www-form-urlencoded' } })
        .then(r => r.json()).then(res => { if(res.success) location.reload(); });
    }
    </script>
    <?php
}

// =========================================================================
// 3. AJAX ARKA PLAN İŞLEMLERİ (KAYDET & SİL)
// =========================================================================
add_action('wp_ajax_ybs_save_speaker_ajax', 'ybs_save_speaker_ajax_func');
function ybs_save_speaker_ajax_func() {
    if (!current_user_can('manage_options')) wp_die();

    $id = intval($_POST['id']);
    $name = sanitize_text_field($_POST['name']);
    $unvan = sanitize_text_field($_POST['unvan']);
    $hakkinda = sanitize_text_field($_POST['hakkinda']);
    $linkedin = esc_url_raw($_POST['linkedin']);
    $order = intval($_POST['order']);
    $img_id = intval($_POST['img_id']);

    $post_data = array(
        'post_title' => $name,
        'post_type' => 'konusmaci',
        'post_status' => 'publish',
        'menu_order' => $order
    );

    if($id > 0) {
        $post_data['ID'] = $id;
        $post_id = wp_update_post($post_data);
    } else {
        $post_id = wp_insert_post($post_data);
    }

    update_post_meta($post_id, 'ybs_k_unvan', $unvan);
    update_post_meta($post_id, 'ybs_k_hakkinda', $hakkinda);
    update_post_meta($post_id, 'ybs_k_linkedin', $linkedin);
    
    if($img_id > 0) set_post_thumbnail($post_id, $img_id);
    else delete_post_thumbnail($post_id);

    wp_send_json_success();
}

add_action('wp_ajax_ybs_delete_speaker_ajax', 'ybs_delete_speaker_ajax_func');
function ybs_delete_speaker_ajax_func() {
    if (!current_user_can('manage_options')) wp_die();
    wp_delete_post(intval($_POST['id']), true);
    wp_send_json_success();
}


// =========================================================================
// 4. ÖN YÜZ TASARIMI VE KISA KOD: [ybs_konusmacilar] (Kullanıcı Tasarımı - Güncel)
// =========================================================================
add_shortcode('ybs_konusmacilar', 'ybs_render_speakers_shortcode');
function ybs_render_speakers_shortcode() {
    // Veritabanından konuşmacıları çek
    $args = array(
        'post_type' => 'konusmaci',
        'posts_per_page' => -1,
        'orderby' => 'menu_order title',
        'order' => 'ASC'
    );
    $speakers = new WP_Query($args);

    if (!$speakers->have_posts()) return '<p style="text-align:center;">Henüz konuşmacı eklenmedi.</p>';

    ob_start();
    ?>
    <style>
        .speakers-section {
            padding: 64px 0;
            background-color: transparent; /* Site arka planına uyması için transparan yapıldı */
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .speakers-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 16px;
        }

        @media (min-width: 640px) { .speakers-container { padding: 0 24px; } }
        @media (min-width: 1024px) { .speakers-container { padding: 0 32px; } }

        /* Grid Layout */
        .speakers-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 32px;
        }
        @media (min-width: 768px) { .speakers-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1024px) { .speakers-grid { grid-template-columns: repeat(3, 1fr); } }

        /* Speaker Card */
        .speaker-card {
            background: white;
            border: 1px solid #f3f4f6;
            border-radius: 12px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            transition: all 0.2s ease-out; /* Animasyon hızını biraz düşürdük */
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.5s ease forwards;
        }

        .speaker-card:hover {
            /* Hover efekti inceltildi */
            border-color: #e5e7eb; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); 
        }

        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Avatar Container - Yuvarlaklık Garantisi ve Boşlukların Kaldırılması */
        .speaker-avatar-wrapper { 
            position: relative; 
            width: 128px; 
            height: 128px; 
            margin-bottom: 16px; 
            border-radius: 50%; 
            overflow: hidden; /* Taşmaları gizle */
            border: 4px solid #f3f4f6; /* Çerçeveyi dışa aldık */
            transition: border-color 0.2s ease;
        }
        
        .speaker-avatar {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Resmi bozmadan çerçeveye sığdırır */
            aspect-ratio: 1/1; /* 1:1 oranını korur */
            display: block;
            border-radius: 50%; /* Resmi de yuvarladık */
            padding: 0; /* Boşlukları kaldırdık */
			margin-top: 0 !important
        }

        /* Hover'da çerçevenin hafif renk değiştirmesi */
        .speaker-card:hover .speaker-avatar-wrapper { border-color: #e5e7eb; } 

        /* Speaker Info */
        .speaker-name { font-size: 1.125rem; font-weight: 700; color: #111827; margin: 0 !important; }
        .speaker-title { font-size: 0.875rem; font-weight: 600; color: #4b5563; margin: 0 0 8px 0; }
        .speaker-bio { font-size: 0.75rem; color: #6b7280; line-height: 1.5; margin: 0; }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .speakers-section { padding: 48px 0; }
            .speakers-header p { font-size: 1rem; }
            .speaker-avatar-wrapper { width: 112px; height: 112px; }
        }
    </style>

    <div class="speakers-section">
        <div class="speakers-container">

            <div class="speakers-grid">
                <?php 
                $delay = 0; // İlk kartın animasyon gecikmesi 0 saniye
                
                while($speakers->have_posts()): $speakers->the_post(); 
                    $post_id = get_the_ID();
                    $unvan   = get_post_meta($post_id, 'ybs_k_unvan', true);
                    $hakkinda= get_post_meta($post_id, 'ybs_k_hakkinda', true);
                    
                    // Öne çıkan görsel yoksa varsayılan resim
                    $img_url = get_the_post_thumbnail_url($post_id, 'large') ?: 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&s=300';
                ?>
                    
                    <div class="speaker-card" style="animation-delay: <?php echo $delay; ?>s;">
                        <div class="speaker-avatar-wrapper">
                            <img src="<?php echo esc_url($img_url); ?>" alt="<?php the_title(); ?>" class="speaker-avatar">
                        </div>
                        <h3 class="speaker-name"><?php the_title(); ?></h3>
                        
                        <?php if(!empty($unvan)): ?>
                            <p class="speaker-title"><?php echo esc_html($unvan); ?></p>
                        <?php endif; ?>

                        <?php if(!empty($hakkinda)): ?>
                            <p class="speaker-bio"><?php echo esc_html($hakkinda); ?></p>
                        <?php endif; ?>
                    </div>

                <?php 
                    $delay += 0.1; // Her döngüde animasyon süresini 0.1 saniye artır (Kusursuz dalga efekti)
                endwhile; 
                wp_reset_postdata(); 
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


// =========================================================================
// PROFİL GÜNCELLEME AJAX İŞLEMİ (TÜM ALANLAR VE DOSYA YÜKLEME DAHİL)
// =========================================================================
add_action('wp_ajax_ybs_update_full_profile_ajax', 'ybs_update_full_profile_ajax_func');
function ybs_update_full_profile_ajax_func() {
    check_ajax_referer('ybs_profile_nonce', 'security');

    if (!is_user_logged_in()) {
        wp_send_json_error('Oturum süreniz dolmuş, lütfen sayfayı yenileyip tekrar giriş yapın.');
    }

    $user_id = get_current_user_id();

    // 1. Temel Bilgiler (Ad, Soyad)
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name  = sanitize_text_field($_POST['last_name']);
    
    wp_update_user([
        'ID'         => $user_id,
        'first_name' => $first_name,
        'last_name'  => $last_name,
        'display_name'=> trim($first_name . ' ' . $last_name)
    ]);

    // 2. Metaların Güncellenmesi (Sadece doluysa/gönderilmişse günceller)
    if(isset($_POST['ybs_tc_kimlik'])) update_user_meta($user_id, 'ybs_tc_kimlik', sanitize_text_field($_POST['ybs_tc_kimlik']));
    if(isset($_POST['ybs_dogum_tarihi'])) update_user_meta($user_id, 'ybs_dogum_tarihi', sanitize_text_field($_POST['ybs_dogum_tarihi']));
    if(isset($_POST['ybs_student_no'])) update_user_meta($user_id, 'ybs_student_no', sanitize_text_field($_POST['ybs_student_no']));
    if(isset($_POST['ybs_sehir'])) update_user_meta($user_id, 'ybs_sehir', sanitize_text_field($_POST['ybs_sehir']));
    if(isset($_POST['ybs_beden'])) update_user_meta($user_id, 'ybs_beden', sanitize_text_field($_POST['ybs_beden']));
    if(isset($_POST['ybs_linkedin'])) update_user_meta($user_id, 'ybs_linkedin', esc_url_raw($_POST['ybs_linkedin']));
    
    if(isset($_POST['ybs_departman'])) update_user_meta($user_id, 'ybs_departman', sanitize_text_field($_POST['ybs_departman']));
    if(isset($_POST['ybs_gorev_tanimi'])) update_user_meta($user_id, 'ybs_gorev_tanimi', sanitize_text_field($_POST['ybs_gorev_tanimi']));
    
    if(isset($_POST['ybs_acil_kisi'])) update_user_meta($user_id, 'ybs_acil_kisi', sanitize_text_field($_POST['ybs_acil_kisi']));
    if(isset($_POST['ybs_acil_telefon'])) update_user_meta($user_id, 'ybs_acil_telefon', sanitize_text_field($_POST['ybs_acil_telefon']));
    if(isset($_POST['ybs_acil_yakinlik'])) update_user_meta($user_id, 'ybs_acil_yakinlik', sanitize_text_field($_POST['ybs_acil_yakinlik']));
    
    if(isset($_POST['blood_type'])) update_user_meta($user_id, 'ybs_kan_grubu', sanitize_text_field($_POST['blood_type']));
    if(isset($_POST['ybs_beslenme'])) update_user_meta($user_id, 'ybs_beslenme', sanitize_text_field($_POST['ybs_beslenme']));
    if(isset($_POST['health_notes'])) update_user_meta($user_id, 'ybs_health_notes', sanitize_text_field($_POST['health_notes']));

    // Telefon Özel Temizliği
    if(isset($_POST['ybs_telefon'])) {
        $clean_phone = preg_replace('/[^0-9]/', '', $_POST['ybs_telefon']);
        update_user_meta($user_id, 'ybs_telefon', sanitize_text_field($clean_phone));
    }

    // 3. Dosya Güncellemeleri (Opsiyonel)
    if ( ! function_exists( 'wp_handle_upload' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
    }
    $upload_overrides = array( 'test_form' => false );
    $upload_errors = array();

    // Profil Fotoğrafı Güncelleme
    if ( ! empty( $_FILES['profile_photo']['name'] ) ) {
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
    }

    // CV Güncelleme
    if ( ! empty( $_FILES['cv_file']['name'] ) ) {
        $file_type = wp_check_filetype(basename($_FILES['cv_file']['name']));
        if(strtolower($file_type['ext']) !== 'pdf') {
            $upload_errors[] = "CV dosyası sadece PDF formatında olmalıdır.";
        } else {
            $cv_move = wp_handle_upload( $_FILES['cv_file'], $upload_overrides );
            if ( $cv_move && ! isset( $cv_move['error'] ) ) {
                update_user_meta( $user_id, 'ybs_cv_dosyasi', $cv_move['url'] );
            } else {
                $upload_errors[] = "CV yüklenemedi: " . $cv_move['error'];
            }
        }
    }

    if ( ! empty($upload_errors) ) {
        wp_send_json_error("Bilgiler güncellendi ancak dosyalarda sorun çıktı:<br>• " . implode("<br>• ", $upload_errors));
    }

    wp_send_json_success();
}


// =========================================================================
// ORGANİZASYON EKİBİ KISA KODU: [ybs_ekip_uyeleri include_ids="1,2"]
// =========================================================================
add_shortcode('ybs_ekip_uyeleri', 'ybs_render_team_members_shortcode');
function ybs_render_team_members_shortcode($atts) {
    
    // Kısa kod özelliklerini al
    $atts = shortcode_atts( array(
        'include_ids' => '17, 1, 29, 28, 54, 47, 43, 81, 97, 118, 78', // Örn: "1,5,12"
    ), $atts );

    $include_ids_array = !empty($atts['include_ids']) ? array_map('intval', explode(',', $atts['include_ids'])) : array();

    // 1. Sadece 'aktif' durumdaki 'topluluk_uyesi' rolüne sahip kişileri çekiyoruz
    $args1 = array(
        'role'       => 'topluluk_uyesi',
        'meta_query' => array(
            array(
                'key'     => 'ybs_status',
                'value'   => 'aktif',
                'compare' => '='
            )
        )
    );
    $user_query1 = new WP_User_Query($args1);
    $members = $user_query1->get_results();

    // 2. Elle girilen özel ID'ler (Koordinatörler) varsa onları çekiyoruz
    if ( !empty($include_ids_array) ) {
        $args2 = array(
            'include' => $include_ids_array
        );
        $user_query2 = new WP_User_Query($args2);
        $extra_members = $user_query2->get_results();

        // İki listeyi birleştir ve mükerrer kayıtları engelle
        $merged_members = array();
        $member_ids = array();

        foreach ($extra_members as $em) {
            if (!in_array($em->ID, $member_ids)) {
                $merged_members[] = $em;
                $member_ids[] = $em->ID;
            }
        }
        foreach ($members as $m) {
            if (!in_array($m->ID, $member_ids)) {
                $merged_members[] = $m;
                $member_ids[] = $m->ID;
            }
        }
        $members = $merged_members;
    }

    if (empty($members)) {
        return '<p style="text-align:center; color:#64748b; padding:40px;">Şu an için listelenecek aktif ekip üyesi bulunmuyor.</p>';
    }

    // 3. --- LİSTEYİ AYIRMA VE SIRALAMA MANTIĞI ---
    $coordinators = array();
    $regulars = array();

    // Üyeleri gruplara ayır (Koordinatör mü, normal üye mi?)
    foreach ($members as $user) {
        if (in_array($user->ID, $include_ids_array)) {
            $coordinators[] = $user;
        } else {
            $regulars[] = $user;
        }
    }

    // Koordinatörleri kısa koda yazılan sıraya göre diz (Örn: 1,3,2 yazıldıysa tam olarak o sırada çıkarlar)
    $ordered_coords = array();
    foreach ($include_ids_array as $cid) {
        foreach ($coordinators as $c) {
            if ($c->ID == $cid) {
                $ordered_coords[] = $c;
                break;
            }
        }
    }

    // Geri kalan (Normal) üyeleri harf sırasına (Alfabetik) göre diz
    usort($regulars, function($a, $b) {
        return strcasecmp($a->display_name, $b->display_name);
    });

    // Önce Koordinatörler, Sonra Normal Üyeler olacak şekilde listeyi son haline getir
    $members = array_merge($ordered_coords, $regulars);
    // ---------------------------------------------

    ob_start();
    ?>
    <style>
        .ybs-team-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 15px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        
        .ybs-team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 30px;
        }

        .ybs-tm-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 35px 20px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        /* Koordinatörlere özel hafif vurgu (İsteğe bağlı) */
        .ybs-tm-card.coordinator-card {
            background: #f8fafc;
            border-color: #bfdbfe;
        }

        .ybs-tm-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.08);
            transform: translateY(-4px);
        }

        /* Koordinatör Etiketi (Badge) */
        .ybs-tm-badge {
            background: #e3e3e3;
            color: #000;
            font-size: 10px;
            font-weight: 600;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .ybs-tm-avatar {
            width: 110px;
            height: 110px;
            margin: 0 auto 20px auto;
            border-radius: 50%;
            overflow: hidden;
            background: #f1f5f9;
            border: 4px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .ybs-tm-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .ybs-tm-name {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 6px 0;
            letter-spacing: -0.3px;
        }

        .ybs-tm-role {
            font-size: 13px;
            font-weight: 700;
            color: #0284c7;
            margin: 0 0 20px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ybs-tm-actions {
            display: flex;
            gap: 12px;
            margin-top: auto;
        }

        .ybs-tm-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #ffffff;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }

        .ybs-tm-btn:hover {
            transform: scale(1.1);
        }

        .ybs-tm-btn.email:hover { background: #ef4444; color: #fff; border-color: #ef4444; }
        .ybs-tm-btn.linkedin:hover { background: #0a66c2; color: #fff; border-color: #0a66c2; }
        .ybs-tm-btn.cv:hover { background: #10b981; color: #fff; border-color: #10b981; }

        @media (max-width: 600px) {
            .ybs-team-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="ybs-team-wrapper">
        <div class="ybs-team-grid">
            
            <?php foreach ($members as $user) : 
                $user_id = $user->ID;
                
                // Kullanıcı Koordinatör mü?
                $is_coordinator = in_array($user_id, $include_ids_array);

                // Metaları çekiyoruz
                $img_url  = get_user_meta($user_id, 'ybs_fotograf', true) ?: 'https://www.gravatar.com/avatar/'.md5($user->user_email).'?d=mp&s=300';
                $dept     = get_user_meta($user_id, 'ybs_departman', true);
                $linkedin = get_user_meta($user_id, 'ybs_linkedin', true);
                $cv_url   = get_user_meta($user_id, 'ybs_cv_dosyasi', true);
                $email    = $user->user_email;

                $role_text = $dept ? $dept : 'Organizasyon Ekibi';
            ?>
            
            <div class="ybs-tm-card <?php echo $is_coordinator ? 'coordinator-card' : ''; ?>">
                
                <?php if($is_coordinator): ?>
                    <div class="ybs-tm-badge">Koordinatör</div>
                <?php endif; ?>

                <div class="ybs-tm-avatar">
                    <img src="<?php echo esc_url($img_url); ?>" alt="<?php echo esc_attr($user->display_name); ?>">
                </div>
                
                <h3 class="ybs-tm-name"><?php echo esc_html($user->display_name); ?></h3>
                <p class="ybs-tm-role"><?php echo esc_html($role_text); ?></p>
                
                <div class="ybs-tm-actions">
                    <a href="mailto:<?php echo esc_attr($email); ?>" class="ybs-tm-btn email" title="E-Posta Gönder">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                    </a>
                    
                    <?php if (!empty($linkedin)): ?>
                    <a href="<?php echo esc_url($linkedin); ?>" target="_blank" class="ybs-tm-btn linkedin" title="LinkedIn Profili">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                    </a>
                    <?php endif; ?>

                    <?php if (!empty($cv_url)): ?>
                    <a href="<?php echo esc_url($cv_url); ?>" target="_blank" class="ybs-tm-btn cv" title="CV / Özgeçmiş Görüntüle" download>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php endforeach; ?>

        </div>
    </div>
    <?php
    return ob_get_clean();
}

// =========================================================================
// ÖZEL ŞİFRE SIFIRLAMA SİSTEMİ (/profil ÜZERİNDEN)
// =========================================================================

// 1. Şifre sıfırlama mailini ve linkini özelleştir
add_filter( 'retrieve_password_message', 'ybs_custom_reset_pass_msg', 10, 4 );
function ybs_custom_reset_pass_msg( $message, $key, $user_login, $user_data ) {
    $message  = "Birisi aşağıdaki hesap için şifre sıfırlama talebinde bulundu:\r\n\r\n";
    $message .= site_url() . "\r\n\r\n";
    $message .= sprintf("Kullanıcı Adı: %s", $user_login) . "\r\n\r\n";
    $message .= "Eğer bu bir hataysa, bu e-postayı görmezden gelin.\r\n\r\n";
    $message .= "Şifrenizi sıfırlamak için aşağıdaki bağlantıya tıklayın:\r\n\r\n";
    // Standart wp-login.php yerine profil sayfamıza yönlendiriyoruz:
    $message .= site_url( "/profil/?action=rp&key=$key&login=" . rawurlencode( $user_login ) ) . "\r\n";
    return $message;
}

// 2. Şifre sıfırlama talebi (E-posta gönderme) AJAX İşlemi
add_action('wp_ajax_nopriv_ybs_ajax_lost_password', 'ybs_ajax_lost_password');
function ybs_ajax_lost_password() {
    check_ajax_referer('ybs_profile_nonce', 'security');
    $user_login = sanitize_text_field($_POST['user_login']);
    
    if(empty($user_login)) wp_send_json_error('Lütfen e-posta adresinizi girin.');
    
    $user = get_user_by('email', $user_login);
    if(!$user) wp_send_json_error('Bu e-posta adresiyle kayıtlı bir hesap bulunamadı.');
    
    $key = get_password_reset_key($user);
    if(is_wp_error($key)) wp_send_json_error('Şifre sıfırlama anahtarı oluşturulamadı.');
    
    $message = apply_filters( 'retrieve_password_message', '', $key, $user->user_login, $user );
    $title = 'YBS Zirvesi - Şifre Sıfırlama Talebi';
    
    if (wp_mail($user->user_email, $title, $message)) {
        wp_send_json_success('Şifre sıfırlama bağlantısı e-posta adresinize gönderildi. Lütfen gelen kutunuzu (ve Spam klasörünü) kontrol edin.');
    } else {
        wp_send_json_error('E-posta gönderilirken bir hata oluştu.');
    }
}

// 3. Yeni Şifreyi Kaydetme AJAX İşlemi
add_action('wp_ajax_nopriv_ybs_ajax_reset_password', 'ybs_ajax_reset_password');
function ybs_ajax_reset_password() {
    check_ajax_referer('ybs_profile_nonce', 'security');
    
    $key = sanitize_text_field($_POST['key']);
    $login = sanitize_text_field($_POST['login']);
    $pass1 = $_POST['pass1'];
    $pass2 = $_POST['pass2'];
    
    if($pass1 !== $pass2) wp_send_json_error('Şifreler birbiriyle eşleşmiyor.');
    if(strlen($pass1) < 6) wp_send_json_error('Şifreniz güvenlik için en az 6 karakter olmalıdır.');
    
    $user = check_password_reset_key($key, $login);
    if(is_wp_error($user)) wp_send_json_error('Sıfırlama bağlantısı geçersiz veya süresi dolmuş. Lütfen tekrar sıfırlama talebinde bulunun.');
    
    reset_password($user, $pass1);
    wp_send_json_success('Şifreniz başarıyla güncellendi! Şimdi giriş yapabilirsiniz.');
}


// =========================================================================
// SKS MAİLLERİ - 1. VERİTABANI TABLOSU
// =========================================================================
add_action('admin_init', 'ybs_setup_sks_log_table');
function ybs_setup_sks_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_sks_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        recipient_email varchar(100) NOT NULL,
        recipient_name varchar(150) NOT NULL,
        daire_adi varchar(200) NOT NULL,
        status varchar(50) DEFAULT 'success',
        error_msg text,
        sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// =========================================================================
// SKS MAİLLERİ - 2. ADMİN MENÜSÜ
// =========================================================================
add_action('admin_menu', 'ybs_sks_mail_menu');
function ybs_sks_mail_menu() {
    add_menu_page(
        'SKS Mailleri',
        'SKS Mailleri',
        'manage_options',
        'ybs-sks-mail',
        'ybs_sks_mail_page',
        'dashicons-building',
        9
    );
}

// =========================================================================
// SKS MAİLLERİ - 3. SAYFA ARAYÜZÜ
// =========================================================================
function ybs_sks_mail_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_sks_logs';

    if (isset($_GET['delete_log']) && current_user_can('manage_options')) {
        $wpdb->delete($table, ['id' => intval($_GET['delete_log'])]);
        echo '<div class="notice notice-success is-dismissible"><p>SKS mail kaydı başarıyla silindi.</p></div>';
    }

    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC");
    $sent_emails = array_map(function($l) { return strtolower(trim($l->recipient_email)); }, $logs);

    $sks_listesi = [
        'sksdb@aybu.edu.tr',
        'sks@mu.edu.tr',
        'sksiletisim@hbv.edu.tr',
        'sks@istinye.edu.tr',
        'sks@marmara.edu.tr',
        'sks@istanbul.edu.tr',
        'saglikkultur@29mayis.edu.tr',
        'sks@adu.edu.tr',
        'sksd@akdeniz.edu.tr',
        'sks@altinbas.edu.tr',
        'info@ankarabilim.edu.tr',
        'sks@ankaramedipol.edu.tr',
        'ibrahimcememir@arel.edu.tr',
        'sks@aksaray.edu.tr',
        'iletisim@adiguzel.edu.tr',
        'sksdbsk@atauni.edu.tr',
        'sks@atu.edu.tr',
        'sksdb@bakircay.edu.tr',
        'sks@bandirma.edu.tr',
        'sks@bartin.edu.tr',
        'sks@baskent.edu.tr',
        'skspdb@beykent.edu.tr',
        'saglikkultur@bilecik.edu.tr',
        'sks@bingol.edu.tr',
        'sks@bogazici.edu.tr',
        'sks@cu.edu.tr',
        'sks@deu.edu.tr',
        'info@dogus.edu.tr',
        'saglikkultur@duzce.edu.tr',
        'sks@firat.edu.tr',
        'info@gedik.edu.tr',
        'sks@gumushane.edu.tr',
        'sks@halic.edu.tr',
        'iletisim@idu.edu.tr',
        'sksdb@kafkas.edu.tr',
        'saglikkultur@ktun.edu.tr',
        'medikososyal@ktu.edu.tr',
        'sks@kapadokya.edu.tr',
        'sksdb@mehmetakif.edu.tr',
        'sksdb@mersin.edu.tr',
        'sksdb@mku.edu.tr',
        'sks@alparslan.edu.tr',
        'sks@nisantasi.edu.tr',
        'sks@ostimteknik.edu.tr',
        'sksdb.yaziisleri@pau.edu.tr',
        'sks@sakarya.edu.tr',
        'selcuksks@selcuk.edu.tr',
        'sks@tarsus.edu.tr',
        'sks@topkapi.edu.tr',
        'skdb@trakya.edu.tr',
        'sksdb@uludag.edu.tr',
        'sks@yasar.edu.tr',
        'mediko@gazi.edu.tr',
        'sks@yiu.edu.tr',
        'sksdb@adiyaman.edu.tr',
        'skultur@aku.edu.tr',
        'sks@agri.edu.tr',
        'sksdb@amasya.edu.tr',
        'sks@mgu.edu.tr',
        'sks@asbu.edu.tr',
        'sks@ankara.edu.tr',
        'info@atilim.edu.tr',
        'kultur@cankaya.edu.tr',
        'sksdb@hacettepe.edu.tr',
        'sks@lokmanhekim.edu.tr',
        'sksdb@metu.edu.tr',
        'ssm@etu.edu.tr',
        'sksm@thk.edu.tr',
        'info@tju.edu.tr',
        'info@ufuk.edu.tr',
        'sks@alanya.edu.tr',
        'sks@belek.edu.tr',
        'abu.sks@antalya.edu.tr',
        'sksdb@ardahan.edu.tr',
        'sks@artvin.edu.tr',
        'sks@balikesir.edu.tr',
        'saglikkulturspor@batman.edu.tr',
        'sks@bayburt.edu.tr',
        'sks@beu.edu.tr',
        'saglikkultur@ibu.edu.tr',
        'sksdb@btu.edu.tr',
        'bilgi@mudanya.edu.tr',
        'sks@comu.edu.tr',
        'sks@karatekin.edu.tr',
        'sks@hitit.edu.tr',
        'sks.dicle@dicle.edu.tr',
        'sksdb@erzincan.edu.tr',
        'sks@erzurum.edu.tr',
        'skssporsube@anadolu.edu.tr',
        'sks@ogu.edu.tr',
        'sks@eskisehir.edu.tr',
        'mailsks@gibtu.edu.tr',
        'sksdb@gantep.edu.tr',
        'sks@hku.edu.tr',
        'info@sanko.edu.tr',
        'sksdb@giresun.edu.tr',
        'sks@hakkari.edu.tr',
        'sksdb@iste.edu.tr',
        'sks@igdir.edu.tr',
        'sksdb@isparta.edu.tr',
        'sksdb@sdu.edu.tr',
        'info@acibadem.edu.tr',
        'bahcesehiruniversitesi@hs01.kep.tr',
        'bilgi@beykoz.edu.tr',
        'info@bezmialem.edu.tr',
        'info@biruni.edu.tr',
        'info@demiroglu.bilim.edu.tr',
        'fsm@fsm.edu.tr',
        'iletisim@fbu.edu.tr',
        'sksdairebaskanligi@gsu.edu.tr',
        'sosyalkultur@isikun.edu.tr',
        'info@ihu.edu.tr',
        'info@atlas.edu.tr',
        'sks@aydin.edu.tr',
        'info@bilgi.edu.tr',
        'tanitim@esenyurt.edu.tr',
        'info@galata.edu.tr',
        'sksdb@gelisim.edu.tr',
        'info@kent.edu.tr',
        'kultur@iku.edu.tr',
        'istanbulmedeniyetuni@hs01.kep.tr',
        'sks@medipol.edu.tr',
        'okan@okan.edu.tr',
        'sks@rumeli.edu.tr',
        'bilgi@izu.edu.tr',
        'istun@hs02.kep.tr',
        'sksbask@itu.edu.tr',
        'bilgi@ticaret.edu.tr',
        'sks@iuc.edu.tr',
        'sks@yeniyuzyil.edu.tr',
        'khas@hs01.kep.tr',
        'information@ku.edu.tr',
        'maltepe@maltepe.edu.tr',
        'ilgarm@mef.edu.tr',
        'msr@hs01.kep.tr',
        'aysegul.tuna@msgsu.edu.tr',
        'info@ozyegin.edu.tr',
        'bilgi@pirireis.edu.tr',
        'sabanciuniversitesi@hs03.kep.tr',
        'sks@sbu.edu.tr',
        'sksdb@tau.edu.tr',
        'sadik.paksoy@uskudar.edu.tr',
        'dos@yeditepe.edu.tr',
        'sksdb@yildiz.edu.tr',
        'sksdb@mail.ege.edu.tr',
        'sks@ieu.edu.tr',
        'sks@ikcu.edu.tr',
        'info@konak.edu.tr',
        'sks@tinaztepe.edu.tr',
        'sks@iyte.edu.tr',
        'sks@istiklal.edu.tr',
        'sks@ksu.edu.tr',
        'sks@karabuk.edu.tr',
        'sks@kmu.edu.tr',
        'sksdb@kastamonu.edu.tr',
        'erusksd@erciyes.edu.tr',
        'sksd@kayseri.edu.tr',
        'info@nny.edu.tr',
        'sks@kku.edu.tr',
        'sks@klu.edu.tr',
        'sks@ahievran.edu.tr',
        'sksdb@kilis.edu.tr',
        'saglik@gtu.edu.tr',
        'sks@kocaelisaglik.edu.tr',
        'info@gidatarim.edu.tr',
        'bilgi@karatay.edu.tr',
        'saglik@erbakan.edu.tr',
        'sks@dpu.edu.tr',
        'sks@ksbu.edu.tr',
        'sksdb@inonu.edu.tr',
        'sks@ozal.edu.tr',
        'sks.baskanlik@cbu.edu.tr',
        'sks@artuklu.edu.tr',
        'leyla@cag.edu.tr',
        'saglikkulturspor@toros.edu.tr',
        'sks@nevsehir.edu.tr',
        'sks@ohu.edu.tr',
        'sksdb@odu.edu.tr',
        'genelsekreterlik@osmaniye.edu.tr',
        'sks@erdogan.edu.tr',
        'sks@subu.edu.tr',
        'saglikkultur@omu.edu.tr',
        'sksdb@samsun.edu.tr',
        'siu@siirt.edu.tr',
        'sksd@sinop.edu.tr',
        'sks@sivas.edu.tr',
        'sksdb.baskanlik@cumhuriyet.edu.tr',
        'sks@harran.edu.tr',
        'sks@sirnak.edu.tr',
        'sks@nku.edu.tr',
        'skultur@gop.edu.tr',
        'iletisim@avrasya.edu.tr',
        'sks@trabzon.edu.tr',
        'sks@munzur.edu.tr',
        'sks@usak.edu.tr',
        'saglikkultur@yyu.edu.tr',
        'sks@yalova.edu.tr',
        'sks@yobu.edu.tr',
        'sks@beun.edu.tr',
    ];

    $total = count($sks_listesi);
    $sent_count = count(array_unique(array_filter($sent_emails)));
    ?>
    <div class="wrap" style="font-family: -apple-system, sans-serif;">
        <h1 class="wp-heading-inline">SKS Mailleri Gönderimi</h1>
        <span style="margin-left:12px; background:#e0f2fe; color:#0369a1; font-size:13px; padding:3px 10px; border-radius:20px; font-weight:600;">
            <?php echo $sent_count; ?> / <?php echo $total; ?> gönderildi
        </span>
        <hr class="wp-header-end">

        <div style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; align-items: flex-start;">

            <!-- KİŞİ LİSTESİ -->
            <div style="width: 300px; flex-shrink: 0;">
                <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden;">
                    <div style="padding: 15px 15px 10px; border-bottom: 1px solid #eee;">
                        <h3 style="margin: 0 0 10px; color: #1d2327; font-size: 14px;">SKS Listesi
                            <span style="font-weight:normal; color:#666;">(<?php echo $total; ?> kurum)</span>
                        </h3>
                        <input type="text" id="sks-search" placeholder="E-posta veya domain ara..." class="widefat" style="font-size:12px;">
                    </div>
                    <div id="sks-list" style="max-height: 560px; overflow-y: auto;">
                        <?php foreach($sks_listesi as $email):
                            $email = trim($email);
                            $already = in_array(strtolower($email), $sent_emails);
                            $domain = explode('@', $email)[1] ?? $email;
                            $uni = explode('.', $domain)[0] ?? $domain;
                        ?>
                        <div class="sks-kisi <?php echo $already ? 'sks-sent' : ''; ?>"
                            data-email="<?php echo esc_attr($email); ?>"
                            data-domain="<?php echo esc_attr($domain); ?>"
                            style="padding: 9px 14px; border-bottom: 1px solid #f5f5f5; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background .1s;"
                            onmouseenter="if(!this.classList.contains('sks-active')) this.style.background='#f0f7ff';"
                            onmouseleave="if(!this.classList.contains('sks-active')) this.style.background='';"
                            onclick="sksSec(this)">
                            <div style="flex: 1; min-width: 0; overflow: hidden;">
                                <div style="font-size: 12px; font-weight: 600; color: #0369a1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?php echo esc_html(strtoupper($uni)); ?>
                                </div>
                                <div style="font-size: 11px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 1px;">
                                    <?php echo esc_html($email); ?>
                                </div>
                            </div>
                            <?php if($already): ?>
                                <span style="color: #10b981; font-size: 15px; flex-shrink: 0;" title="Mail gönderildi">✓</span>
                            <?php else: ?>
                                <span style="color: #d1d5db; font-size: 15px; flex-shrink: 0;">○</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <div style="flex: 1; min-width: 300px; max-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Mail Gönder</h3>
                    <p style="font-size: 13px; color: #666; margin-top: 0;">Soldan kurum seçin ya da bilgileri elle girin. Yanıtlar <b>info@duybs.com</b> adresine düşer.</p>

                    <div id="sks-secili" style="display:none; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px 14px; margin-bottom:16px; font-size:13px; color:#166534;">
                        Seçili: <strong id="sks-secili-email"></strong>
                    </div>

                    <form id="sks-mail-form">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Hedef E-Posta Adresi</label>
                            <input type="email" id="sks-email" class="widefat" required placeholder="ornek@duzce.edu.tr">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Daire Başkanı / İlgili Kişi Adı (Sayın ...)</label>
                            <input type="text" id="sks-hitap" class="widefat" required placeholder="Örn: Ahmet Yılmaz" value="Sağlık Kültür ve Spor Daire Başkanlığı">
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Daire / Birim Adı</label>
                            <input type="text" id="sks-daire" class="widefat" required placeholder="Örn: Sağlık Kültür ve Spor Daire Başkanlığı" value="Sağlık Kültür ve Spor Daire Başkanlığı">
                        </div>

                        <button type="submit" id="btn-send-sks" class="button button-primary button-large" style="width: 100%; text-align:center;">Maili Gönder</button>
                        <div id="sks-response" style="margin-top: 15px; font-weight: bold; text-align: center;"></div>
                    </form>
                </div>
            </div>

            <!-- GEÇMİŞ -->
            <div style="flex: 2; min-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Gönderim Geçmişi</h3>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>E-Posta</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="4" style="text-align:center;">Henüz SKS mail gönderimi yapılmadı.</td></tr>
                            <?php else: ?>
                                <?php foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($log->sent_at)); ?></td>
                                    <td style="font-size:12px;"><?php echo esc_html($log->recipient_email); ?></td>
                                    <td>
                                        <?php if($log->status == 'success'): ?>
                                            <span style="color:#10b981; font-weight:bold;">✓ İletildi</span>
                                        <?php else: ?>
                                            <span style="color:#ef4444; font-weight:bold;" title="<?php echo esc_attr($log->error_msg); ?>">❌ Hata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=ybs-sks-mail&delete_log=<?php echo $log->id; ?>" style="color:red; text-decoration:none;" onclick="return confirm('Kayıt silinsin mi?');">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
    function sksSec(el) {
        document.querySelectorAll('.sks-kisi').forEach(function(k) {
            k.classList.remove('sks-active');
            k.style.background = k.classList.contains('sks-sent') ? '#f0fdf4' : '';
        });
        el.classList.add('sks-active');
        el.style.background = '#dbeafe';

        document.getElementById('sks-email').value = el.dataset.email;

        const seciliDiv = document.getElementById('sks-secili');
        document.getElementById('sks-secili-email').innerText = el.dataset.email;
        seciliDiv.style.display = 'block';

        document.getElementById('sks-response').innerHTML = '';
    }

    document.getElementById('sks-search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.sks-kisi').forEach(function(k) {
            const metin = (k.dataset.email + ' ' + k.dataset.domain).toLowerCase();
            k.style.display = metin.includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.sks-sent').forEach(function(k) {
        k.style.background = '#f0fdf4';
    });

    document.getElementById('sks-mail-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn    = document.getElementById('btn-send-sks');
        const resDiv = document.getElementById('sks-response');
        const email  = document.getElementById('sks-email').value;
        const hitap  = document.getElementById('sks-hitap').value;
        const daire  = document.getElementById('sks-daire').value;

        btn.disabled = true;
        btn.innerText = 'Gönderiliyor, lütfen bekleyin...';
        resDiv.innerHTML = '';

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_send_sks_mail_ajax');
        fd.append('email', email);
        fd.append('hitap', hitap);
        fd.append('daire', daire);

        fetch(ajaxurl, {
            method: 'POST',
            body: fd,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                resDiv.style.color = '#10b981';
                resDiv.innerText = '✅ ' + res.data;
                setTimeout(() => location.reload(), 1500);
            } else {
                resDiv.style.color = '#ef4444';
                resDiv.innerText = '❌ Hata: ' + res.data;
                btn.disabled = false;
                btn.innerText = 'Maili Gönder';
            }
        })
        .catch(function() {
            resDiv.style.color = '#ef4444';
            resDiv.innerText = '❌ Sunucu bağlantı hatası.';
            btn.disabled = false;
            btn.innerText = 'Maili Gönder';
        });
    });
    </script>
    <?php
}

// =========================================================================
// SKS MAİLLERİ - 4. AJAX: MAİLİ GÖNDER VE KAYDET
// =========================================================================
add_action('wp_ajax_ybs_send_sks_mail_ajax', 'ybs_send_sks_mail_func');
function ybs_send_sks_mail_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    $email = sanitize_email($_POST['email']);
    $hitap = sanitize_text_field($_POST['hitap']);
    $daire = sanitize_text_field($_POST['daire']);

    if(empty($email) || empty($hitap) || empty($daire)) {
        wp_send_json_error('Lütfen tüm alanları doldurun.');
    }

    $subject = '10. Ulusal Yönetim Bilişim Sistemleri Öğrenci Zirvesi – Davet';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: 10. Ulusal YBS Zirvesi <info@duybs.com>'
    );

    $message = "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>

        <div style='text-align: center; line-height: 0;'>
            <img src='https://2026.ybszirve.org.tr/dosyalar/afis.jpeg' alt='10. Ulusal YBS Zirvesi Afişi' style='width: 100%; max-width: 650px; display: block; margin: 0 auto;'>
        </div>

        <div style='padding: 40px 30px; color: #334155; font-size: 15px; line-height: 1.8;'>
            <p style='margin-top: 0; font-size: 16px;'>Sayın <strong>$hitap</strong>,</p>

            <p>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu tarafından düzenlenen <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>, bu yıl Düzce Üniversitesi ev sahipliğinde gerçekleştirilecektir.</p>

            <p><strong>Gençlik ve Spor Bakanlığı</strong> tarafından desteklenen bu organizasyonda, Türkiye'nin <strong>81 ilinden 650'den fazla üniversite öğrencisi</strong> ile bilişim sektörünün önde gelen şirketlerinin yöneticileri bir araya gelecektir.</p>

            <p>Zirvemizin başarıyla gerçekleşmesinde <strong>$daire</strong>'nın desteği ve iş birliği bizim için son derece değerlidir. Farklı üniversitelerden gelen öğrencilerin konaklaması, ulaşımı ve sosyal etkinlikleri konusundaki deneyiminiz ve kurumsal katkınız, bu büyük organizasyonun olmazsa olmazları arasında yer almaktadır.</p>

            <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; border-radius: 6px; padding: 20px 25px; margin: 30px 0;'>
                <p style='margin: 0; font-size: 14px; color: #065f46; font-weight: 600;'>Bu kapsamda iş birliği teklifimizi değerlendirmenizi ve zirvemize destek vermelerini büyük bir memnuniyetle karşılayacağımızı bildiririz.</p>
            </div>

            <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin: 30px 0; text-align: center;'>
                <h3 style='margin: 0 0 15px 0; font-size: 16px; color: #0f172a;'>Zirve Hakkında Daha Fazla Bilgi</h3>
                <div style='margin-bottom: 20px;'>
                    <a href='https://2026.ybszirve.org.tr/program/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>📅 Program Akışı</a>
                    <a href='https://2026.ybszirve.org.tr/konusmacilar/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>🎤 Konuşmacılar</a>
                </div>
                <a href='https://2026.ybszirve.org.tr' target='_blank' style='display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: bold; font-size: 15px;'>🌐 Zirve Web Sitesini Ziyaret Edin</a>
            </div>

            <p style='margin-top: 30px; margin-bottom: 5px; color: #64748b;'>Desteğiniz bizleri onurlandıracaktır.</p>
            <p style='margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 16px;'>Saygılarımızla,</p>
            <p style='margin-top: 5px; color: #64748b;'>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu</p>
        </div>

        <div style='background-color: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;'>
            <p style='margin: 0 0 6px 0; font-size: 14px; color: #475569;'>E-posta: <a href='mailto:info@duybs.com' style='color: #10b981; text-decoration: none; font-weight: 600;'>info@duybs.com</a></p>
            <p style='margin: 0; font-size: 14px; color: #475569;'>Web: <a href='https://duybs.com' target='_blank' style='color: #10b981; text-decoration: none; font-weight: 600;'>duybs.com</a></p>
            <p style='margin: 10px 0 0 0; font-size: 13px; color: #94a3b8;'>&copy; 2026 YBS Zirvesi Organizasyon Komitesi</p>
        </div>

    </div>
    ";

    $is_sent = wp_mail($email, $subject, $message, $headers);

    global $wpdb;
    $table = $wpdb->prefix . 'ybs_sks_logs';

    if($is_sent) {
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'daire_adi'       => $daire,
            'status'          => 'success',
            'error_msg'       => '',
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_success('Mail başarıyla gönderildi ve listeye eklendi.');
    } else {
        global $phpmailer;
        $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'daire_adi'       => $daire,
            'status'          => 'error',
            'error_msg'       => $error_msg,
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_error('Mail iletilemedi. Detay: ' . $error_msg);
    }
}


// =========================================================================
// BÖLÜM BAŞKANI MAİLLERİ - 1. VERİTABANI TABLOSU
// =========================================================================
add_action('admin_init', 'ybs_setup_bolum_log_table');
function ybs_setup_bolum_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_bolum_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        recipient_email varchar(100) NOT NULL,
        recipient_name varchar(150) NOT NULL,
        bolum_adi varchar(200) NOT NULL,
        status varchar(50) DEFAULT 'success',
        error_msg text,
        sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// =========================================================================
// BÖLÜM BAŞKANI MAİLLERİ - 2. ADMİN MENÜSÜ
// =========================================================================
add_action('admin_menu', 'ybs_bolum_mail_menu');
function ybs_bolum_mail_menu() {
    add_menu_page(
        'Bölüm Başkanı Mailleri',
        'Bölüm Başkanı',
        'manage_options',
        'ybs-bolum-mail',
        'ybs_bolum_mail_page',
        'dashicons-welcome-learn-more',
        10
    );
}

// =========================================================================
// BÖLÜM BAŞKANI MAİLLERİ - 3. SAYFA ARAYÜZÜ
// =========================================================================
function ybs_bolum_mail_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_bolum_logs';

    if (isset($_GET['delete_log']) && current_user_can('manage_options')) {
        $wpdb->delete($table, ['id' => intval($_GET['delete_log'])]);
        echo '<div class="notice notice-success is-dismissible"><p>Bölüm başkanı mail kaydı başarıyla silindi.</p></div>';
    }

    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC");
    $sent_emails = array_map(function($l) { return strtolower(trim($l->recipient_email)); }, $logs);

    $bolum_baslari = [
        ['ad' => 'Prof. Dr. HÜSEYİN DEMİREL',              'email' => 'huseyindemirel@aybu.edu.tr'],
        ['ad' => 'Prof. Dr. Mine ŞENEL',                    'email' => 'minesenel@mu.edu.tr'],
        ['ad' => 'Prof. Dr. Latif ÖZTÜRK',                  'email' => 'latif.ozturk@hbv.edu.tr'],
        ['ad' => 'Doç. Dr. Şebnem Özdemir',                 'email' => 'sebnem.ozdemir@istinye.edu.tr'],
        ['ad' => 'Prof. Dr. Deniz Herand',                  'email' => 'denizherand@marmara.edu.tr'],
        ['ad' => 'Prof. Dr. RASİM ÖZCAN',                   'email' => 'rasim.ozcan@istanbul.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Hediye Gamze TÜRKMEN',     'email' => 'hgturkmen@29mayis.edu.tr'],
        ['ad' => 'Prof. Dr. Mustafa ÇETİN',                 'email' => 'mcetin@adu.edu.tr'],
        ['ad' => 'Prof. Dr. SEZGİN IRMAK',                  'email' => 'sezgin@akdeniz.edu.tr'],
        ['ad' => 'Doç. Dr. Muzaffer AYDEMİR',               'email' => 'muzaffer.aydemir@altinbas.edu.tr'],
        ['ad' => 'Dr. Mehmet Alper AKDEMİR',                'email' => 'alper.akdemir@ankarabilim.edu.tr'],
        ['ad' => 'Prof. Dr. Gökhan SİLAHTAROĞLU',           'email' => 'gsilahtaroglu@medipol.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Ali Anıl ÜNSAL',           'email' => 'alianilunsal@arel.edu.tr'],
        ['ad' => 'Prof. Dr. Hülya BAKIRTAŞ',                'email' => 'hbakirtas@aksaray.edu.tr'],
        ['ad' => 'Öğr. Gör. Kadriye BEKTAŞ',                'email' => 'kadriyebektas@adiguzel.edu.tr'],
        ['ad' => 'Prof. Dr. Üstün Özen',                    'email' => 'uozen@atauni.edu.tr'],
        ['ad' => 'Prof. Dr. Fatma Nur İPLİK',               'email' => 'nuriplik@atu.edu.tr'],
        ['ad' => 'Prof. Dr. Kadir Hızıroğlu',               'email' => 'kadir.hiziroglu@bakircay.edu.tr'],
        ['ad' => 'Prof. Dr. ÖZER YILMAZ',                   'email' => 'oyilmaz@bandirma.edu.tr'],
        ['ad' => 'Doç. Dr. Fatma SÖNMEZ ÇAKIR',             'email' => 'fsonmez@bartin.edu.tr'],
        ['ad' => 'Prof. Dr. MURAT PAŞA UYSAL',              'email' => 'mpuysal@baskent.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Şükran ORUÇ',              'email' => 'sukranoruc@beykent.edu.tr'],
        ['ad' => 'Prof. Dr. TOLGA TORUN',                   'email' => 'tolga.torun@bilecik.edu.tr'],
        ['ad' => 'Doç. Dr. ETHEM KILIÇ',                    'email' => 'ekilic@bingol.edu.tr'],
        ['ad' => 'Prof. Dr. Mehmet Nafiz Aydın',            'email' => 'mehmetn.aydin@bogazici.edu.tr'],
        ['ad' => 'Prof. Dr. Vahap TECİM',                   'email' => 'vahap.tecim@deu.edu.tr'],
        ['ad' => 'Doç. Dr. Senem ALTAN',                    'email' => 'saltan@dogus.edu.tr'],
        ['ad' => 'Prof. Dr. Bilal SOLAK',                   'email' => 'bsolak@firat.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Halime Suvay Eker',        'email' => 'halime.eker@gedik.edu.tr'],
        ['ad' => 'Prof. Dr. Handan Çam',                    'email' => 'hcam@gumushane.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi MUHAMMET SAİT BOZİK',      'email' => 'muhammetsaitbozik@halic.edu.tr'],
        ['ad' => 'Doç. Dr. Serkan ÇANKAYA',                 'email' => 'serkan.cankaya@idu.edu.tr'],
        ['ad' => 'Prof. Dr. Gökhan Kerse',                  'email' => 'gokhankerse@kafkas.edu.tr'],
        ['ad' => 'Prof. Dr. BÜNYAMİN ER',                  'email' => 'ber@ktu.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Gözde SUNMAN',             'email' => 'gozde.sunman@kapadokya.edu.tr'],
        ['ad' => 'Prof. Dr. Adnan KALKAN',                  'email' => 'adnankalkan@mehmetakif.edu.tr'],
        ['ad' => 'Öğr. Gör. Enes SULAK',                    'email' => 'enessulak@mersin.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Ali İhsan BENZER',         'email' => 'alibenzer@mku.edu.tr'],
        ['ad' => 'Prof. Dr. Murat Türk',                    'email' => 'murat.turk@nisantasi.edu.tr'],
        ['ad' => 'Doç. Dr. Faruk Güven',                    'email' => 'faruk.guven@ostimteknik.edu.tr'],
        ['ad' => 'Prof. Dr. SELÇUK BURAK HAŞILOĞLU',        'email' => 'hasiloglu@pau.edu.tr'],
        ['ad' => 'Prof. Dr. Aykut Hamit Turan',             'email' => 'ahturan@sakarya.edu.tr'],
        ['ad' => 'Doç. Dr. Özlem İPEK',                     'email' => 'ozlemipek@tarsus.edu.tr'],
        ['ad' => 'Prof. Dr. Ali Fazlı YILDIRIM',            'email' => 'fazliyildirim@topkapi.edu.tr'],
        ['ad' => 'Prof. Dr. Erdem UÇAR',                    'email' => 'erdemucar@trakya.edu.tr'],
        ['ad' => 'Prof. Dr. MELİH ENGİN',                   'email' => 'melihengin@uludag.edu.tr'],
        ['ad' => 'Prof. Dr. Ahmet Tuncay Ercan',            'email' => 'tuncay.ercan@yasar.edu.tr'],
        ['ad' => 'Doç. Dr. Mustafa TANRIVERDİ',             'email' => 'mustafatanriverdi@gazi.edu.tr'],
        ['ad' => 'Doç. Dr. Fatih Özdinç',                   'email' => 'fozdinc@aku.edu.tr'],
        ['ad' => 'Prof. Dr. İbrahim ÖZKAN',                 'email' => 'iozkan@cankaya.edu.tr'],
        ['ad' => 'Prof. Dr. Aral EGE',                      'email' => 'aral.ege@ufuk.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi ENDER ŞAHİNASLAN',        'email' => 'ender.sahinaslan@mudanya.edu.tr'],
        ['ad' => 'Prof. Dr. UĞUR KESKİN',                  'email' => 'ugurkeskin@anadolu.edu.tr'],
        ['ad' => 'Doç. Dr. Sibel DİNÇ AYDEMİR',            'email' => 'sdaydemir@fsm.edu.tr'],
        ['ad' => 'Prof. Dr. Erdal ŞEN',                     'email' => 'erdal.sen@fbu.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Şahin Aydın',             'email' => 'sahin.aydin@isikun.edu.tr'],
        ['ad' => 'Prof. Dr. Orhan İŞCAN',                  'email' => 'oiscan@gelisim.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi BURÇİN ATASEVEN DOĞRU',   'email' => 'b.ataseven@iku.edu.tr'],
        ['ad' => 'Doç. Dr. Özge DOĞUÇ KARDEŞ',             'email' => 'odoguc@medipol.edu.tr'],
        ['ad' => 'Doç. Dr. UMUT AYDIN',                    'email' => 'uaydin@ticaret.edu.tr'],
        ['ad' => 'Doç. Dr. OĞUZHAN CEYLAN',                'email' => 'oguzhan.ceylan@khas.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Ali KILINÇ',              'email' => 'akilinc@pirireis.edu.tr'],
        ['ad' => 'Doç. Dr. Büşra ÖZDENİZCİ KÖSE',          'email' => 'busraozdenizci@gtu.edu.tr'],
        ['ad' => 'Dr. Öğr. Üyesi Ayhan AYDOĞDU',           'email' => 'ayhan.aydogdu@gidatarim.edu.tr'],
        ['ad' => 'Prof. Dr. HALİT BULUTHAN ÇETİNTAŞ',      'email' => 'halitbuluthan.cetintas@erbakan.edu.tr'],
        ['ad' => 'Doç. Dr. Aslı BORU İPEK',                'email' => 'asli.ipek@dpu.edu.tr'],
        ['ad' => 'Doç. Dr. Özge KORKMAZ',                  'email' => 'ozge.korkmaz@ozal.edu.tr'],
        ['ad' => 'Prof. Dr. Mustafa Fedai ÇAVUŞ',          'email' => 'mfcavus@osmaniye.edu.tr'],
        ['ad' => 'Prof. Dr. MEHMET ALİ ALAN',              'email' => 'alan@cumhuriyet.edu.tr'],
    ];

    $total = count($bolum_baslari);
    $sent_count = count(array_unique(array_filter($sent_emails)));
    ?>
    <div class="wrap" style="font-family: -apple-system, sans-serif;">
        <h1 class="wp-heading-inline">Bölüm Başkanı Mailleri Gönderimi</h1>
        <span style="margin-left:12px; background:#e0f2fe; color:#0369a1; font-size:13px; padding:3px 10px; border-radius:20px; font-weight:600;">
            <?php echo $sent_count; ?> / <?php echo $total; ?> gönderildi
        </span>
        <hr class="wp-header-end">

        <div style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; align-items: flex-start;">

            <!-- KİŞİ LİSTESİ -->
            <div style="width: 300px; flex-shrink: 0;">
                <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden;">
                    <div style="padding: 15px 15px 10px; border-bottom: 1px solid #eee;">
                        <h3 style="margin: 0 0 10px; color: #1d2327; font-size: 14px;">Kişi Listesi
                            <span style="font-weight:normal; color:#666;">(<?php echo $total; ?> kişi)</span>
                        </h3>
                        <input type="text" id="bb-search" placeholder="İsim veya e-posta ara..." class="widefat" style="font-size:12px;">
                    </div>
                    <div id="bb-list" style="max-height: 560px; overflow-y: auto;">
                        <?php foreach($bolum_baslari as $i => $bb):
                            $already = in_array(strtolower(trim($bb['email'])), $sent_emails);
                            $domain = explode('@', $bb['email'])[1] ?? '';
                        ?>
                        <div class="bb-kisi <?php echo $already ? 'bb-sent' : ''; ?>"
                            data-email="<?php echo esc_attr($bb['email']); ?>"
                            data-ad="<?php echo esc_attr($bb['ad']); ?>"
                            data-domain="<?php echo esc_attr($domain); ?>"
                            style="padding: 10px 14px; border-bottom: 1px solid #f5f5f5; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background .1s;"
                            onmouseenter="if(!this.classList.contains('bb-active')) this.style.background='#f0f7ff';"
                            onmouseleave="if(!this.classList.contains('bb-active')) this.style.background='';"
                            onclick="bbSec(this)">
                            <div style="flex: 1; min-width: 0; overflow: hidden;">
                                <div style="font-size: 12px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: #1d2327;">
                                    <?php echo esc_html($bb['ad']); ?>
                                </div>
                                <div style="font-size: 11px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">
                                    <?php echo esc_html($bb['email']); ?>
                                </div>
                            </div>
                            <?php if($already): ?>
                                <span style="color: #10b981; font-size: 15px; flex-shrink: 0;" title="Mail gönderildi">✓</span>
                            <?php else: ?>
                                <span style="color: #d1d5db; font-size: 15px; flex-shrink: 0;">○</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <div style="flex: 1; min-width: 300px; max-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Mail Gönder</h3>
                    <p style="font-size: 13px; color: #666; margin-top: 0;">Soldan kişi seçin ya da bilgileri elle girin. Yanıtlar <b>info@duybs.com</b> adresine düşer.</p>

                    <div id="bb-secili" style="display:none; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px 14px; margin-bottom:16px; font-size:13px; color:#166534;">
                        Seçili: <strong id="bb-secili-ad"></strong>
                    </div>

                    <form id="bolum-mail-form">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Hedef E-Posta Adresi</label>
                            <input type="email" id="bb-email" class="widefat" required placeholder="ornek@duzce.edu.tr">
                        </div>
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Bölüm Başkanı Adı (Sayın ...)</label>
                            <input type="text" id="bb-hitap" class="widefat" required placeholder="Örn: Prof. Dr. Ahmet Yılmaz">
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Bölüm Adı</label>
                            <input type="text" id="bb-bolum" class="widefat" required placeholder="Örn: Yönetim Bilişim Sistemleri Bölümü" value="Yönetim Bilişim Sistemleri Bölümü">
                        </div>

                        <button type="submit" id="btn-send-bolum" class="button button-primary button-large" style="width: 100%; text-align:center;">Maili Gönder</button>
                        <div id="bb-response" style="margin-top: 15px; font-weight: bold; text-align: center;"></div>
                    </form>
                </div>
            </div>

            <!-- GEÇMİŞ -->
            <div style="flex: 2; min-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Gönderim Geçmişi</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Bölüm Başkanı</th>
                                <th>E-Posta</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="5" style="text-align:center;">Henüz bölüm başkanı mail gönderimi yapılmadı.</td></tr>
                            <?php else: ?>
                                <?php foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($log->sent_at)); ?></td>
                                    <td><?php echo esc_html($log->recipient_name); ?></td>
                                    <td style="font-size:12px;"><?php echo esc_html($log->recipient_email); ?></td>
                                    <td>
                                        <?php if($log->status == 'success'): ?>
                                            <span style="color:#10b981; font-weight:bold;">✓ İletildi</span>
                                        <?php else: ?>
                                            <span style="color:#ef4444; font-weight:bold;" title="<?php echo esc_attr($log->error_msg); ?>">❌ Hata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=ybs-bolum-mail&delete_log=<?php echo $log->id; ?>" style="color:red; text-decoration:none;" onclick="return confirm('Kayıt silinsin mi?');">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <script>
    function bbSec(el) {
        document.querySelectorAll('.bb-kisi').forEach(function(k) {
            k.classList.remove('bb-active');
            k.style.background = k.classList.contains('bb-sent') ? '#f0fdf4' : '';
        });
        el.classList.add('bb-active');
        el.style.background = '#dbeafe';

        document.getElementById('bb-email').value  = el.dataset.email;
        document.getElementById('bb-hitap').value  = el.dataset.ad;

        const seciliDiv = document.getElementById('bb-secili');
        document.getElementById('bb-secili-ad').innerText = el.dataset.ad;
        seciliDiv.style.display = 'block';

        document.getElementById('bb-response').innerHTML = '';
    }

    document.getElementById('bb-search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.bb-kisi').forEach(function(k) {
            const metin = (k.dataset.ad + ' ' + k.dataset.email + ' ' + k.dataset.domain).toLowerCase();
            k.style.display = metin.includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.bb-sent').forEach(function(k) {
        k.style.background = '#f0fdf4';
    });

    document.getElementById('bolum-mail-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn    = document.getElementById('btn-send-bolum');
        const resDiv = document.getElementById('bb-response');
        const email  = document.getElementById('bb-email').value;
        const hitap  = document.getElementById('bb-hitap').value;
        const bolum  = document.getElementById('bb-bolum').value;

        btn.disabled = true;
        btn.innerText = 'Gönderiliyor, lütfen bekleyin...';
        resDiv.innerHTML = '';

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_send_bolum_mail_ajax');
        fd.append('email', email);
        fd.append('hitap', hitap);
        fd.append('bolum', bolum);

        fetch(ajaxurl, {
            method: 'POST',
            body: fd,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                resDiv.style.color = '#10b981';
                resDiv.innerText = '✅ ' + res.data;
                setTimeout(() => location.reload(), 1500);
            } else {
                resDiv.style.color = '#ef4444';
                resDiv.innerText = '❌ Hata: ' + res.data;
                btn.disabled = false;
                btn.innerText = 'Maili Gönder';
            }
        })
        .catch(function() {
            resDiv.style.color = '#ef4444';
            resDiv.innerText = '❌ Sunucu bağlantı hatası.';
            btn.disabled = false;
            btn.innerText = 'Maili Gönder';
        });
    });
    </script>
    <?php
}

// =========================================================================
// BÖLÜM BAŞKANI MAİLLERİ - 4. AJAX: MAİLİ GÖNDER VE KAYDET
// =========================================================================
add_action('wp_ajax_ybs_send_bolum_mail_ajax', 'ybs_send_bolum_mail_func');
function ybs_send_bolum_mail_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    $email = sanitize_email($_POST['email']);
    $hitap = sanitize_text_field($_POST['hitap']);
    $bolum = sanitize_text_field($_POST['bolum']);

    if(empty($email) || empty($hitap) || empty($bolum)) {
        wp_send_json_error('Lütfen tüm alanları doldurun.');
    }

    $subject = '10. Ulusal Yönetim Bilişim Sistemleri Zirvesi – Katılım Daveti';

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: 10. Ulusal YBS Zirvesi <info@duybs.com>'
    );

    $message = "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>

        <div style='text-align: center; line-height: 0;'>
            <img src='https://2026.ybszirve.org.tr/dosyalar/afis.jpeg' alt='10. Ulusal YBS Zirvesi Afişi' style='width: 100%; max-width: 650px; display: block; margin: 0 auto;'>
        </div>

        <div style='padding: 40px 30px; color: #334155; font-size: 15px; line-height: 1.8;'>
            <p style='margin-top: 0; font-size: 16px;'>Sayın <strong>$hitap</strong>,</p>

            <p>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu tarafından düzenlenen <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>, bu yıl Düzce Üniversitesi ev sahipliğinde gerçekleştirilecektir.</p>

            <p><strong>Gençlik ve Spor Bakanlığı</strong> tarafından desteklenen bu organizasyonda, Türkiye'nin <strong>81 ilinden 650'den fazla üniversite öğrencisi</strong> ile bilişim sektörünün önde gelen şirketlerinin yöneticileri bir araya gelecektir.</p>

            <p>Teknoloji, yönetim ve bilişim alanlarında bilgi paylaşımını ve sektör–öğrenci etkileşimini güçlendirmeyi amaçlayan zirvemizde, üniversitemizi ve şehrimizi ulusal ölçekte temsil edecek önemli oturumlar, konuşmalar ve networking etkinlikleri gerçekleştirilecektir.</p>

            <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; border-radius: 6px; padding: 20px 25px; margin: 30px 0;'>
                <p style='margin: 0; font-size: 14px; color: #065f46; font-weight: 600;'>Bu kapsamda sizleri, <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>'nde aramızda görmekten onur duyarız. Katılımınız, hem etkinliğimize değer katacak hem de öğrencilerimiz için önemli bir motivasyon kaynağı olacaktır.</p>
            </div>

            <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin: 30px 0; text-align: center;'>
                <h3 style='margin: 0 0 15px 0; font-size: 16px; color: #0f172a;'>Zirve Hakkında Daha Fazla Bilgi</h3>
                <div style='margin-bottom: 20px;'>
                    <a href='https://2026.ybszirve.org.tr/program/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>📅 Program Akışı</a>
                    <a href='https://2026.ybszirve.org.tr/konusmacilar/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>🎤 Konuşmacılar</a>
                </div>
                <a href='https://2026.ybszirve.org.tr' target='_blank' style='display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: bold; font-size: 15px;'>🌐 Zirve Web Sitesini Ziyaret Edin</a>
            </div>

            <p style='margin-top: 30px; margin-bottom: 5px; color: #64748b;'>Katılımınız bizleri onurlandıracaktır.</p>
            <p style='margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 16px;'>Saygılarımızla,</p>
            <p style='margin-top: 5px; color: #64748b;'>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu</p>
        </div>

        <div style='background-color: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;'>
            <p style='margin: 0 0 6px 0; font-size: 14px; color: #475569;'>E-posta: <a href='mailto:info@duybs.com' style='color: #10b981; text-decoration: none; font-weight: 600;'>info@duybs.com</a></p>
            <p style='margin: 0; font-size: 14px; color: #475569;'>Web: <a href='https://duybs.com' target='_blank' style='color: #10b981; text-decoration: none; font-weight: 600;'>duybs.com</a></p>
            <p style='margin: 10px 0 0 0; font-size: 13px; color: #94a3b8;'>&copy; 2026 YBS Zirvesi Organizasyon Komitesi</p>
        </div>

    </div>
    ";

    $is_sent = wp_mail($email, $subject, $message, $headers);

    global $wpdb;
    $table = $wpdb->prefix . 'ybs_bolum_logs';

    if($is_sent) {
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'bolum_adi'       => $bolum,
            'status'          => 'success',
            'error_msg'       => '',
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_success('Mail başarıyla gönderildi ve listeye eklendi.');
    } else {
        global $phpmailer;
        $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'bolum_adi'       => $bolum,
            'status'          => 'error',
            'error_msg'       => $error_msg,
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_error('Mail iletilemedi. Detay: ' . $error_msg);
    }
}

// =========================================================================
// UNI HOCALARI DAVET MAİLLERİ - 1. VERİTABANI TABLOSU
// =========================================================================
function ybs_unihoca_get_people() {
    static $cache = null;
    if ($cache !== null) return $cache;

    // 1) Sunucuda dosya varsa onu kullan.
    $file_path = trailingslashit(get_stylesheet_directory()) . 'unihocalar.txt';
    if (!file_exists($file_path)) {
        $file_path = trailingslashit(get_template_directory()) . 'unihocalar.txt';
    }

    $parse_people = function($lines) {
        $people = [];
        if (!is_array($lines)) return $people;
        foreach ($lines as $line) {
            $line = (string) $line;
            if (trim($line) === '') continue;
            $parts = explode('|', $line, 2);
            if (count($parts) < 2) continue;
            $name = trim($parts[0]);
            $email = trim($parts[1]);
            if ($email === '-' || $email === '') continue;
            $email = sanitize_email($email);
            if (empty($name) || empty($email) || !is_email($email)) continue;
            $people[] = ['ad' => $name, 'email' => $email];
        }
        return $people;
    };

    if (file_exists($file_path)) {
        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $people = $parse_people($lines);
        if (!empty($people)) {
            $cache = $people;
            return $cache;
        }
    }

    // 2) Dosya yoksa / okunamadıysa: fallback olarak gömülü listeyi kullan.
    $embedded = <<<'TXT'
Prof. Dr. Abdulvahap BAYDAŞ | abdulvahapbaydas@duzce.edu.tr
Prof. Dr. Enver BOZDEMİR | enverbozdemir@duzce.edu.tr
Prof. Dr. İsmail Hakkı ERASLAN | hakkieraslan@duzce.edu.tr
Prof. Dr. İzzet KILINÇ | izzetkilinc@duzce.edu.tr
Prof. Dr. Mehmet Akif ÖNCÜ | mehmetakifoncu@duzce.edu.tr
Prof. Dr. Mehmet Nurullah KURUTKAN | nurullahkurutkan@duzce.edu.tr
Prof. Dr. Mehmet Selami YILDIZ | selamiyildiz@duzce.edu.tr
Prof. Dr. Nigar DEMİRCAN ÇAKAR | nigarcakar@duzce.edu.tr
Prof. Dr. Öznur BOZKURT | oznurbozkurt@duzce.edu.tr
Prof. Dr. Yalçın KARAGÖZ | yalcinkaragoz@duzce.edu.tr
Prof. Dr. Emel İŞTAR IŞIKLI | emelistar@duzce.edu.tr
Prof. Dr. Hakan Murat ARSLAN | muratarslan@duzce.edu.tr
Prof. Dr. Oğuz KARA | oguzkara@duzce.edu.tr
Prof. Dr. İstemi ÇÖMLEKÇİ | istemicomlekci@duzce.edu.tr
Doç. Dr. Fuat YALMAN | fuatyalman@duzce.edu.tr
Doç. Dr. Gülçin ERSÖZ DEMİR | gulcinersozdemir@duzce.edu.tr
Doç. Dr. Dilek ŞAHİN | dileksahin@duzce.edu.tr
Doç. Dr. Ali ÖZER | aliozer@duzce.edu.tr
Doç. Dr. Murat BAYAT | muratbayat@duzce.edu.tr
Doç. Dr. Nevin ÖZER | nevinozer@duzce.edu.tr
Doç. Dr. Remzi BAŞAR | remzibasar@duzce.edu.tr
Doç. Dr. Emel FAİZ | emelgokmenoglu@duzce.edu.tr
Doç. Dr. Zeynep KARAŞ | zeynepkaras@duzce.edu.tr
Doç. Dr. Faruk Kerem ŞENTÜRK | keremsenturk@duzce.edu.tr
Doç. Dr. İsmail DURAK | ismaildurak@duzce.edu.tr
Doç. Dr. Özkan ŞAHİN | ozkansahin@duzce.edu.tr
Doç. Dr. Yunus Emre TAŞGİT | yunusemretasgit@duzce.edu.tr
Doç. Dr. Abdullah Kutalmış YALÇIN | kutalmisyalcin@duzce.edu.tr
Doç. Dr. Yusuf ÖCEL | yusufocel@duzce.edu.tr
Dr. Öğr. Üyesi Önder ULU | onderulu@duzce.edu.tr
Dr. Öğr. Üyesi Ahmet AKCAN | ahmetakcan@duzce.edu.tr
Dr. Öğr. Üyesi Ali AKAYTAY | aliakaytay@duzce.edu.tr
Dr. Öğr. Üyesi Oğuz DEMİREL | oguzdemirel@duzce.edu.tr
Dr. Öğr. Üyesi Emine ŞENBABAOĞLU DANACI | eminesenbabaoglu@duzce.edu.tr
Dr. Öğr. Üyesi Mustafa YANARTAŞ | mustafayanartas@duzce.edu.tr
Dr. Öğr. Üyesi Okan BÜTÜNER | okanbutuner@duzce.edu.tr
Dr. Öğr. Üyesi Osman KARTAL | osmankartal@duzce.edu.tr
Dr. Öğr. Üyesi Said ALTINIŞIK | saidaltinisik@duzce.edu.tr
Dr. Öğr. Üyesi Abdulaziz SEZER | abdulazizsezer@duzce.edu.tr
Dr. Öğr. Üyesi Serhat ATA | serhatata@duzce.edu.tr
Dr. Öğr. Üyesi Tuğçe KARAYEL | tugceaslan@duzce.edu.tr
Dr. Öğr. Üyesi Melek TERZİ ÖZMEN | melekterzi@duzce.edu.tr
Arş. Gör. Dr. Ali GÜVEN | aliguven@duzce.edu.tr
Arş. Gör. Dr. Bilgin ZENGİN | bilginzengin@duzce.edu.tr
Arş. Gör. Dr. Esengül ÖZDEMİR ALTINIŞIK | esengulaltinisik@duzce.edu.tr
Arş. Gör. Dr. Gülizar ÖZÇELİK | gulizarozcelik@duzce.edu.tr
Arş. Gör. Dr. Mustafa POLAT | mustafapolat@duzce.edu.tr
Arş. Gör. Dr. Nazife Bahar ÖZDERE | baharozdere@duzce.edu.tr
Arş. Gör. Dr. Seydi Ahmet ÖZKAYA | ahmetozkaya@duzce.edu.tr
Arş. Gör. Dr. Sinan KIZILTOPRAK | sinankiziltoprak@duzce.edu.tr
Arş. Gör. Dr. Talha FIRAT | talhafirat@duzce.edu.tr
Öğr. Gör. Şerife Büşra ÜMİT IŞIK | sbusraumitisik@duzce.edu.tr
Öğr. Gör. Yelda ÜNVER | yeldakale@duzce.edu.tr
Arş. Gör. Tuğba Emine BEYHAN | tugbabeyhan@duzce.edu.tr
Arş. Gör. İsmail Nurullah MUTLU | ismailnurullahmutlu@duzce.edu.tr
Arş. Gör. Öznur OCAK | oznurocak@duzce.edu.tr
Prof. Dr. Resul KARA | resulkara@duzce.edu.tr
Prof. Dr. Pakize ERDOĞMUŞ | pakizeerdogmus@duzce.edu.tr
Prof. Dr. İbrahim YÜCEDAĞ | ibrahimyucedag@duzce.edu.tr
Prof. Dr. Yusuf ALTUN | yusufaltun@duzce.edu.tr
Prof. Dr. Ali ÇALHAN | alicalhan@duzce.edu.tr
Doç. Dr. Serdar BİROĞUL | serdarbirogul@duzce.edu.tr
Doç. Dr. Esra ŞATIR | esrasatir@duzce.edu.tr
Doç. Dr. Fatih KAYAALP | fatihkayaalp@duzce.edu.tr
Doç. Dr. Abdullah Talha KABAKUŞ | talhakabakus@duzce.edu.tr
Doç. Dr. Arafat ŞENTÜRK | arafatsenturk@duzce.edu.tr
Doç. Dr. Zehra KARAPINAR ŞENTÜRK | zehrakarapinar@duzce.edu.tr
Doç. Dr. Serdar KIRIŞOĞLU | serdarkirisoglu@duzce.edu.tr
Dr. Öğr. Üyesi Ahmet ALBAYRAK | ahmetalbayrak@duzce.edu.tr
Dr. Öğr. Üyesi Büşra TAKGİL | busratakgil@duzce.edu.tr
Dr. Öğr. Üyesi Ekrem BAŞER | ekrembaser@duzce.edu.tr
Dr. Öğr. Üyesi Sultan ZAVRAK | sultanzavrak@duzce.edu.tr
Dr. Öğr. Üyesi Hüseyin BODUR | huseyinbodur@duzce.edu.tr
Dr. Öğr. Üyesi Şeyhmus YILMAZ | seyhmusyilmaz@duzce.edu.tr
Arş. Gör. Dr. Ezgi KARA TİMUÇİN | ezgikara@duzce.edu.tr
Dr. Öğr. Üyesi Tunahan TİMUÇİN | tunahantimucin@duzce.edu.tr
Dr. Öğr. Üyesi Tuba KARAGÜL YILDIZ | tubakaragul@duzce.edu.tr
Dr. Öğr. Üyesi Sümeyye BAYRAKDAR | sumeyyebayrakdar@duzce.edu.tr
Arş. Gör. Hacer BAYIROĞLU | hacerbayiroglu@duzce.edu.tr
Dr. Öğr. Üyesi Enes ASLAN | enesaslan@duzce.edu.tr
Prof. Dr. Nurcan ÇALIŞ AÇIKBAŞ | nurcancalisacikbas@duzce.edu.tr
Arş. Gör. Yasin TÜRKYILMAZ | yasinturkyilmaz@duzce.edu.tr
Dr. Öğr. Üyesi Yaşar ŞEN | yasarsen@duzce.edu.tr
Dr. Öğr. Üyesi İkrime ORKAN UÇAR | ikrimeucar@duzce.edu.tr
Doç. Dr. Emine GÜVEN | emine.guven@duzce.edu.tr
Dr. Öğr. Üyesi Pınar Deniz TOSUN | pinardeniztosun@duzce.edu.tr
Arş. Gör. Melahat Sevgül BAKAY | melahatbakay@duzce.edu.tr
Arş. Gör. Sümeyye ARIKAN | sumeyyaarikan@duzce.edu.tr
Prof. Dr. Şeref KESKİN | serefkeskin@duzce.edu.tr
Prof. Dr. Emine MALKOÇ | eminemalkoc@duzce.edu.tr
Prof. Dr. Fatih TAŞPINAR | fatihtaspinar@duzce.edu.tr
Doç. Dr. Murat SOLAK | muratsolak@duzce.edu.tr
Doç. Dr. Fatih AKTAŞ | fatihaktas@duzce.edu.tr
Doç. Dr. Zehra BOZKURT | zehrabozkurt@duzce.edu.tr
Doç. Dr. Pınar SEVİM ELİBOL | pinarsevim@duzce.edu.tr
Dr. Öğr. Üyesi Nilüfer ÜLGÜDÜR | niluferulgudur@duzce.edu.tr
Prof. Dr. Ali ÖZTÜRK | aliozturk@duzce.edu.tr
Prof. Dr. Filiz BİRBİR ÜNAL | filizbirbir@duzce.edu.tr
Prof. Dr. Murat KALE | muratkale@duzce.edu.tr
Prof. Dr. İsmail ERCAN | ismailercan@duzce.edu.tr
Prof. Dr. Uğur GÜVENÇ | ugurguvenc@duzce.edu.tr
Prof. Dr. Uğur HASIRCI | ugurhasirci@duzce.edu.tr
Prof. Dr. Emre ÇELİK | emrecelik@duzce.edu.tr
Prof. Dr. M. Kenan DÖŞOĞLU | kenandosoglu@duzce.edu.tr
Prof. Dr. Salih TOSUN | salihtosun@duzce.edu.tr
Prof. Dr. Yunus BİÇEN | yunusbicen@duzce.edu.tr
Dr. Öğr. Üyesi Mehmet UÇAR | mehmetucar@duzce.edu.tr
Doç. Dr. Selman KULAÇ | selmankulac@duzce.edu.tr
Doç. Dr. Musa ÇADIRCI | musacadirci@duzce.edu.tr
Doç. Dr. Emin YILDIRIZ | eminyildiriz@duzce.edu.tr
Doç. Dr. Fatih EVRAN | fatihevran@duzce.edu.tr
Dr. Öğr. Üyesi Nur SARMA | nursarma@duzce.edu.tr
Doç. Dr. Mustafa DURSUN | mustafadursun@duzce.edu.tr
Dr. Öğr. Üyesi M. Mustafa ERTAY | mustafaertay@duzce.edu.tr
Dr. Öğr. Üyesi Oğuzhan DEMİRYÜREK | oguzhandemiryurek@duzce.edu.tr
Doç. Dr. Furkan AKAR | furkanakar@duzce.edu.tr
Doç. Dr. Erdem ELİBOL | erdemelibol@duzce.edu.tr
Dr. Öğr. Üyesi Emre AVCI | emreavci@duzce.edu.tr
Dr. Öğr. Üyesi Mehmet DUMAN | mehmetduman@duzce.edu.tr
Arş. Gör. Dinçer MADEN | dincermaden@duzce.edu.tr
Dr. Öğr. Üyesi Osman DİKMEN | osmandikmen@duzce.edu.tr
Arş. Gör. Melih AKTAŞ | melihaktas@duzce.edu.tr
Arş. Gör. Dr. Hamdullah YOKUŞ | hamdullahyokus@duzce.edu.tr
Arş. Gör. Seda SAVAŞÇI ŞEN | sedasavascisen@duzce.edu.tr
Dr. Öğr. Üyesi Enes KAYMAZ | eneskaymaz@duzce.edu.tr
Arş. Gör. Bayram KÜÇÜK | bayramkucuk@duzce.edu.tr
Arş. Gör. Dr. Yunus HINISLIOĞLU | yunushinislioglu@duzce.edu.tr
Dr. Öğr. Üyesi M. Merih LEBLEBİCİ | merihleblebici@duzce.edu.tr
Dr. Öğr. Üyesi Çağdaş TUNCEROĞLU | cagdastunceroglu@duzce.edu.tr
Doç. Dr. İrem DÜZDAR | iremduzdar@duzce.edu.tr
Dr. Öğr. Üyesi Barış KANTOĞLU | barıskantoglu@duzce.edu.tr
Dr. Öğr. Üyesi Ahmet CİHAN | ahmetcihan@duzce.edu.tr
Doç. Dr. Melike ERDOĞAN | melikeerdogan@duzce.edu.tr
Dr. Öğr. Üyesi Pınar ÇÖMEZ | pinarcomez@duzce.edu.tr
Prof. Dr. Ayhan ŞAMANDAR | ayhansamandar@duzce.edu.tr
Prof. Dr. Serkan SUBAŞI | serkansubasi@duzce.edu.tr
Prof. Dr. Yılmaz KOÇAK | yilmazkocak@duzce.edu.tr
Prof. Dr. Mehmet Emin ARSLAN | mehmeteminarslan@duzce.edu.tr
Doç. Dr. Bayram POYRAZ | bayrampoyraz@duzce.edu.tr
Doç. Dr. Latif Onur UĞUR | latifugur@duzce.edu.tr
Doç. Dr. Bekir ÇOMAK | bekircomak@duzce.edu.tr
Dr. Öğr. Üyesi Mustafa DAYI | mustafadayi@duzce.edu.tr
Arş. Gör. Rasim Cem SAKA | cemsaka@duzce.edu.tr
Dr. Öğr. Üyesi Adil GÜLTEKİN | adilgultekin@duzce.edu.tr
Arş. Gör. Uğur Mahir TÜRKEL | mahirturkel@duzce.edu.tr
Öğr. Gör. Metin Mevlüt UZUNOĞLU | metinuzunoglu@duzce.edu.tr
Dr. Öğr. Üyesi Batuhan AYKANAT | batuhanaykanat@duzce.edu.tr
Doç. Dr. Emrah YILMAZ | emrahyilmaz@duzce.edu.tr
Arş. Gör. Muhammed ALANKUŞ | muhammedalankus@duzce.edu.tr
Prof. Dr. İlyas UYGUR | ilyasuygur@duzce.edu.tr
Prof. Dr. Hamit SARUHAN | hamitsaruhan@duzce.edu.tr
Prof. Dr. Hüsnü GERENGİ | husnugerengi@duzce.edu.tr
Prof. Dr. Ali GÜRSEL | aligursel@duzce.edu.tr
Prof. Dr. Suat SARIDEMİR | suatsaridemir@duzce.edu.tr
Prof. Dr. Ethem TOKLU | ethemtoklu@duzce.edu.tr
Prof. Dr. Turgay KIVAK | turgaykivak@duzce.edu.tr
Prof. Dr. Fuat KARA | fuatkara@duzce.edu.tr
Prof. Dr. Nuri ŞEN | nurisen@duzce.edu.tr
Dr. Öğr. Üyesi Mert KILINÇEL | mertkilincel@duzce.edu.tr
Doç. Dr. Fikret POLAT | fikretpolat@duzce.edu.tr
Doç. Dr. Serkan APAY | serkanapay@duzce.edu.tr
Doç. Dr. Mustafa AYYILDIZ | mustafaayyildiz@duzce.edu.tr
Dr. Öğr. Üyesi Şenol MERT | senolmert@duzce.edu.tr
Dr. Öğr. Üyesi Ömer ERKAN | omererkan@duzce.edu.tr
Öğr. Gör. Dr. Ender NALÇACIOĞLU | endernalcacioglu@duzce.edu.tr
Arş. Gör. Dr. Rıdvan ONGUN | ridvanongun@duzce.edu.tr
Arş. Gör. Ebubekir Can GÜNEŞ | ebubekircangunes@duzce.edu.tr
Prof. Dr. Mert YILDIRIM | mertyildirim@duzce.edu.tr
Prof. Dr. Gürcan SAMTAŞ | gurcansamtas@duzce.edu.tr
Öğr. Gör. Dr. Ali İhsan AYGÜN | aliihsanaygun@duzce.edu.tr
Dr. Öğr. Üyesi Ayşe Bengü SÜNBÜL GÜNER | bengusunbulguner@duzce.edu.tr
Arş. Gör. Beyzanur YAVUZ | beyzanuryavuz@duzce.edu.tr
Prof. Dr. Bülent AYDEMİR | bulentaydemir@duzce.edu.tr
Öğr. Gör. Dr. Büşra KESİCİ | busrakesici@duzce.edu.tr
Arş. Gör. Ecem TÜMSEKÇALI | ecemtumsekcali@duzce.edu.tr
Arş. Gör. Elif Eda TAKGİL | elifedatakgil@duzce.edu.tr
Arş. Gör. Dr. Emrah UYSAL | emrahuysal@duzce.edu.tr
Arş. Gör. Emre PAZARLI | emrepazarli@duzce.edu.tr
Dr. Öğr. Üyesi Enver Küçükkülahlı | enverkucukkulahli@duzce.edu.tr
Doç. Dr. Ferzan KATIRCIOĞLU | ferzankatircioglu@duzce.edu.tr
Arş. Gör. Fethiye Sultan ÖZPEHLİVAN | fethiyeozpehlivanay@duzce.edu.tr
Doç. Dr. Harun Bayrakdar | harunbayrakdar@duzce.edu.tr
Arş. Gör. Hilal SAZOĞLU | hilalsazoglu@duzce.edu.tr
Arş. Gör. İsmail Enes TIĞLI | ismailenestigli@duzce.edu.tr
Doç. Dr. Kadir SAYGIN | kadirsaygin@duzce.edu.tr
Arş. Gör. Lutfullah Enes GÖĞŞEN | enesgogsen@duzce.edu.tr
Prof. Dr. M. Enes BAYRAKDAR | muhammedbayrakdar@duzce.edu.tr
Prof. Dr. Mesut GÖKTEN | mesutgokten@duzce.edu.tr
Arş. Gör. Muhammet Talha TEPE | muhammettalhatepe@duzce.edu.tr
Doç. Dr. Murat BULUT | muratbulut@duzce.edu.tr
Dr. Öğr. Üyesi Mustafa İsa DOĞAN | mustafaisadogan@duzce.edu.tr
Arş. Gör. Oğuz EROL | oguzerol@duzce.edu.tr
Arş. Gör. Oktay ÇAVUŞOĞLU | oktaycavusoglu@duzce.edu.tr
Prof. Dr. Oktay ELKOCA | oktayelkoca@duzce.edu.tr
Dr. Öğr. Üyesi Osman AKBULUT | osmanakbulut@duzce.edu.tr
Arş. Gör. Ozan İbrahim Ethem BAĞRIYANIK | ozanbagriyanik@duzce.edu.tr
Dr. Öğr. Üyesi Sabri UZUNER | sabriuzuner@duzce.edu.tr
Dr. Öğr. Üyesi Sibel YILMAZ EKİNCİ | sibelyilmaz@duzce.edu.tr
Doç. Dr. Şenol Şirin | senolsirin@duzce.edu.tr
Doç. Dr. Tijen ÖVER ÖZÇELİK | -
Arş. Gör. Tolgahan CİVEK | tolgahancivek@duzce.edu.tr
Dr. Öğr. Üyesi Vildan Zülal SÖNMEZ | zulalsonmez@duzce.edu.tr
Prof. Dr. Ali ERTUĞRUL | aliertugrul@duzce.edu.tr
Prof. Dr. Arzu ÖZKOÇ ÖZTÜRK | arzuozkoc@duzce.edu.tr
Prof. Dr. Başaran DÜLGER | basarandulger@duzce.edu.tr
Prof. Dr. Deniz YAĞLIOĞLU | denizyaglioglu@duzce.edu.tr
Prof. Dr. Dilek NARTOP | dileknartop@duzce.edu.tr
Prof. Dr. Duygu EKİNCİ | duyguekinci@duzce.edu.tr
Prof. Dr. Emel COŞKUN | emelcoskun@duzce.edu.tr
Prof. Dr. Emine TEKİN | eminetekin@duzce.edu.tr
Prof. Dr. Emrah Evren KARA | eevrenkara@duzce.edu.tr
Prof. Dr. Emre OKAN | emreokan@duzce.edu.tr
Prof. Dr. Ernaz ALTUNDAĞ ÇAKIR | ernazaltundag@duzce.edu.tr
Prof. Dr. Ersin ORHAN | ersinorhan@duzce.edu.tr
Prof. Dr. Fatma Gül BOYACI SAN | fatmagulboyacisan@duzce.edu.tr
Prof. Dr. Fuat USTA | fuatusta@duzce.edu.tr
Prof. Dr. İlhame AMİRALİ | ilhameamirali@duzce.edu.tr
Prof. Dr. İlhan GENÇ | ilhangenc@duzce.edu.tr
Prof. Dr. Kadir GÖKŞEN | kadirgoksen@duzce.edu.tr
Prof. Dr. Mecit AKSU | mecitaksu@duzce.edu.tr
Prof. Dr. Mehmet Emin ULUDAĞ | mehmeteminuludag@duzce.edu.tr
Prof. Dr. Mehmet Zeki SARIKAYA | mzekisarikaya@duzce.edu.tr
Prof. Dr. Meral KEKEÇOĞLU | meralkekecoglu@duzce.edu.tr
Prof. Dr. Metin AKKUŞ | metinakkus@duzce.edu.tr
Prof. Dr. Metin KILIÇ | metinkilic@duzce.edu.tr
Prof. Dr. Mira KHACHEMİZOVA | mira@duzce.edu.tr
Prof. Dr. Muhammet ÖZDEMİR | muhammetozdemir@duzce.edu.tr
Prof. Dr. Muharrem GÖKÇEN | muharremgokcen@duzce.edu.tr
Prof. Dr. Oğuz KÖYSAL | oguzkoysal@duzce.edu.tr
Prof. Dr. Pınar GÖÇ RASGELE | pinarrasgele@duzce.edu.tr
Prof. Dr. Recai ÖZCAN | recaiozcan@duzce.edu.tr
Prof. Dr. Sabit DOKUYAN | sabitdokuyan@duzce.edu.tr
Prof. Dr. Bayram POYRAZ | bayrampoyraz@duzce.edu.tr
Prof. Dr. Sefa DURMUŞ | sefadurmus@duzce.edu.tr
Prof. Dr. Şerife Gülsün KIRANKAYA | gulsunkirankaya@duzce.edu.tr
Prof. Dr. Ümit ERGUN | umitergun@duzce.edu.tr
Doç. Dr. Abdulkadir ALLI | abdulkadiralli@duzce.edu.tr
Doç. Dr. Adnan ESENYEL | adnanesenyel@duzce.edu.tr
Doç. Dr. Ahmet BİLİR | ahmetbilir@duzce.edu.tr
Doç. Dr. Ahmet DEMİR | ahmetdemir@duzce.edu.tr
Doç. Dr. Alparslan ATAHAN | alparslanatahan@duzce.edu.tr
Doç. Dr. Aysun AYDIN | aysunaydin@duzce.edu.tr
Doç. Dr. Barış GÜLCÜ | barisgulcu@duzce.edu.tr
Doç. Dr. Cihan ERTAN | cihanertan@duzce.edu.tr
Doç. Dr. Eda TOK | edatok@duzce.edu.tr
Doç. Dr. Erol UĞUR | erolugur@duzce.edu.tr
Doç. Dr. Eva TANİA | evataniia@duzce.edu.tr
Doç. Dr. Fatih HEZENCİ | fatihhezenci@duzce.edu.tr
Doç. Dr. Fatima KVARCHELL | fatimakvarcheliia@duzce.edu.tr
Doç. Dr. Gülhan AYAR | gulhanayar@duzce.edu.tr
Doç. Dr. Hamdi ÖZDİŞ | hamdiozdis@duzce.edu.tr
Doç. Dr. Hande BULUT | handebulut@duzce.edu.tr
Doç. Dr. İsmail Alper KUMSAR | alperkumsar@duzce.edu.tr
Prof. Dr. İsmail YAŞAYANLAR | ismailyasayanlar@duzce.edu.tr
Doç. Dr. İzzettin DEMİR | izzettindemir@duzce.edu.tr
Doç. Dr. Maka SALİA BEŞİROĞLU | makasalia@duzce.edu.tr
Doç. Dr. Mehmet HAZAR | mehmethazar@duzce.edu.tr
Doç. Dr. Melek Zeynep ESENYEL | zeynepesenyel@duzce.edu.tr
Prof. Dr. Merve İLKHAN KARA | merveilkhan@duzce.edu.tr
Doç. Dr. Orhan KILIÇARSLAN | orhankilicarslan@duzce.edu.tr
Doç. Dr. Pınar PINARCIK | pinarpinarcik@duzce.edu.tr
Doç. Dr. Ruzana DOLEVA | ruzana@duzce.edu.tr
Doç. Dr. Salih Tunç KAYA | salihtunckaya@duzce.edu.tr
Doç. Dr. Sedef ÜNSAL SEYDOOĞULLARI | sedefunsalseydoogullari@duzce.edu.tr
Doç. Dr. Sema ALLI | semaalli@duzce.edu.tr
Doç. Dr. Sezen SİVRİKAYA ÖZAK | sezensivrikaya@duzce.edu.tr
Doç. Dr. Shorena LOMAIA | shorenalomaia@duzce.edu.tr
Prof. Dr. Sibel BAYRAM | sibelbayram@duzce.edu.tr
Doç. Dr. Songül TARAN | songultaran@duzce.edu.tr
Doç. Dr. Susana SHKHALAKHOVA | susana@duzce.edu.tr
Doç. Dr. Ritsa OTYRBA | ritsaotyrba@duzce.edu.tr
Doç. Dr. Tuba TUNÇ | tubatunc@duzce.edu.tr
Doç. Dr. Utku ÖZMAKAS | utkuozmakas@duzce.edu.tr
Doç. Dr. Ümit Özgür DEMİRCİ | umitdemirci@duzce.edu.tr
Doç. Dr. Yasemin YILMAZ | yaseminyilmaz@duzce.edu.tr
Doç. Dr. Zakir DENİZ | zakirdeniz@duzce.edu.tr
Doç. Dr. Ahmet Furkan TOSYALI | ahmetfurkantosyali@duzce.edu.tr
Doç. Dr. Aybüke Betül DOĞAN | betulkiymaz@duzce.edu.tr
Dr. Öğr. Üyesi Aynur AĞÖREN | aynuragoren@duzce.edu.tr
Dr. Öğr. Üyesi Belgin ÜSTÜN GÜLLÜ | belginustungullu@duzce.edu.tr
Dr. Öğr. Üyesi Emel ŞENGÖNÜL ARAS | emelaras@duzce.edu.tr
Dr. Öğr. Üyesi Emine ŞAHİN | eminesahin@duzce.edu.tr
Dr. Öğr. Üyesi Fatih Alper TAŞBAŞ | fatihtasbas@duzce.edu.tr
Dr. Öğr. Üyesi Fehmi ALTIN | fehmialtin@duzce.edu.tr
Dr. Öğr. Üyesi Furkan KÜLÜNK | furkankulunk@duzce.edu.tr
Dr. Öğr. Üyesi Gülsüm KOCAKÜLAH | gulsumkocakulah@duzce.edu.tr
Dr. Öğr. Üyesi Hasan KARA | hasankara@duzce.edu.tr
Dr. Öğr. Üyesi Hüsnü GERELEGİZ | husnugerelegiz@duzce.edu.tr
Dr. Öğr. Üyesi Kubilay İNCİ | kubilayinci@duzce.edu.tr
Dr. Öğr. Üyesi Mariam SULKHANISHVILI | mariam@duzce.edu.tr
Dr. Öğr. Üyesi Mehmet KARAASLAN | mehmetkaraaslan@duzce.edu.tr
Dr. Öğr. Üyesi Mustafa BİÇER | mustafabicer@duzce.edu.tr
Dr. Öğr. Üyesi Mümin TOPCU | mumintopcu@duzce.edu.tr
Dr. Öğr. Üyesi Nurperi AYENGİN | nurperiayengin@duzce.edu.tr
Dr. Öğr. Üyesi Özlem ÜNLÜ | ozlemilkin@duzce.edu.tr
Dr. Öğr. Üyesi Onur ÇAPAR | onurcapar@duzce.edu.tr
Dr. Öğr. Üyesi Onur Sadık KARAKUŞ | onursadikkarakus@duzce.edu.tr
Doç. Dr. Pınar ZENGİN ALP | pinarzengin@duzce.edu.tr
Dr. Öğr. Üyesi Safiye AYDIN | safiyeaydin@duzce.edu.tr
Dr. Öğr. Üyesi Saida ABREGOVA | saidaabregova@duzce.edu.tr
Dr. Öğr. Üyesi Sibel KÜÇÜKKÜLAHLI | sibelkucukkulahli@duzce.edu.tr
Dr. Öğr. Üyesi Şevket Ercan KIZILAY | sevketercankizilay@duzce.edu.tr
Dr. Öğr. Üyesi Tuğba AKGÜR | tubaakgur@duzce.edu.tr
Dr. Öğr. Üyesi Vahdet TARAKÇI | vahdettarakci@duzce.edu.tr
Öğr. Gör. Dr. Emrullah SEVİM | emrullahsevim@duzce.edu.tr
Dr. Öğr. Üyesi Furkan DÜZENLİ | furkanduzenli@duzce.edu.tr
Arş. Gör. Dr. Bahar AYKAÇ | baharaykac@duzce.edu.tr
Arş. Gör. Dr. Başak GENÇTÜRK OĞHAN | basakgencturk@duzce.edu.tr
Dr. Öğr. Üyesi Buşra AŞIK BİRLİK | busraasikbirlik@duzce.edu.tr
Arş. Gör. Dr. Meltem YILDIRIM BAŞOĞLU | meltemyildirim@duzce.edu.tr
Dr. Öğr. Üyesi Özge ÖZARSLAN | ozgesarialioglu@duzce.edu.tr
Arş. Gör. Ayşe Kaya | ayse.kaya@duzce.edu.tr
Arş. Gör. Betül PAKSOY | betuluysal@duzce.edu.tr
Arş. Gör. Burcu FEDAKAR | burcufedakar@duzce.edu.tr
Arş. Gör. Buşra KOÇASLAN | busrakocaslan@duzce.edu.tr
Arş. Gör. Eren Burak İŞGÖREN | erenburakisgoren@duzce.edu.tr
Arş. Gör. Fatma ÇELİK TANRIVERDİ | fatmacelik@duzce.edu.tr
Arş. Gör. Mübarek ATAN | mubarekatan@duzce.edu.tr
Arş. Gör. Osman Furkan AYDIN | osmanfurkanaydin@duzce.edu.tr
Dr. Öğr. Üyesi Pınar KARAGÜL | pinarkaragul@duzce.edu.tr
Arş. Gör. Semra SAYGILI | semrayilmaz@duzce.edu.tr
Arş. Gör. Sunay YILMAZ | sunayyilmaz@duzce.edu.tr
Arş. Gör. Tuğba KILIÇ | tugbakilic@duzce.edu.tr
Arş. Gör. Umut BOSTANCI | umutbostanci@duzce.edu.tr
Arş. Gör. Yusuf ŞAFAK | yusufsafak@duzce.edu.tr
Arş. Gör. Zehra İŞBİLİR | zehraisbilir@duzce.edu.tr
Arş. Gör. Meliha AKSOY GÖKALP | melihaaksoygokalp@duzce.edu.tr
TXT;

    $embedded_lines = preg_split("/\\R/u", $embedded);
    $people = $parse_people($embedded_lines);
    $cache = $people;
    return $cache;
}

add_action('admin_init', 'ybs_setup_unihoca_log_table');
function ybs_setup_unihoca_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ybs_unihoca_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        recipient_email varchar(100) NOT NULL,
        recipient_name varchar(150) NOT NULL,
        status varchar(50) DEFAULT 'success',
        error_msg text,
        sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// =========================================================================
// UNI HOCALARI DAVET MAİLLERİ - 2. ADMİN MENÜSÜ
// =========================================================================
add_action('admin_menu', 'ybs_unihoca_mail_menu');
function ybs_unihoca_mail_menu() {
    add_menu_page(
        'Uni Hocaları Davet Mailleri',
        'Uni Hocaları Davet',
        'manage_options',
        'ybs-uni-hoca-mail',
        'ybs_unihoca_mail_page',
        'dashicons-email',
        11
    );
}

// =========================================================================
// UNI HOCALARI DAVET MAİLLERİ - 3. SAYFA ARAYÜZÜ
// =========================================================================
function ybs_unihoca_mail_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'ybs_unihoca_logs';

    if (isset($_GET['delete_log']) && current_user_can('manage_options')) {
        $wpdb->delete($table, ['id' => intval($_GET['delete_log'])]);
        echo '<div class="notice notice-success is-dismissible"><p>Uni hocaları mail kaydı başarıyla silindi.</p></div>';
    }

    // unihocalar.txt dosyası yoksa bile, fallback olarak gömülü listeden alır.
    $people = ybs_unihoca_get_people();

    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC");
    $sent_emails = array_map(function($l) { return strtolower(trim($l->recipient_email)); }, $logs);

    $total = count($people);
    $sent_count = count(array_unique(array_filter($sent_emails)));
    ?>
    <div class="wrap" style="font-family: -apple-system, sans-serif;">
        <h1 class="wp-heading-inline">Uni Hocaları Davet Mailleri Gönderimi</h1>
        <span style="margin-left:12px; background:#e0f2fe; color:#0369a1; font-size:13px; padding:3px 10px; border-radius:20px; font-weight:600;">
            <?php echo $sent_count; ?> / <?php echo $total; ?> gönderildi
        </span>
        <hr class="wp-header-end">

        <div style="display: flex; gap: 20px; margin-top: 20px; flex-wrap: wrap; align-items: flex-start;">
            <!-- KİŞİ LİSTESİ -->
            <div style="width: 320px; flex-shrink: 0;">
                <div style="background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); overflow: hidden;">
                    <div style="padding: 15px 15px 10px; border-bottom: 1px solid #eee;">
                        <h3 style="margin: 0 0 10px; color: #1d2327; font-size: 14px;">Kişi Listesi
                            <span style="font-weight:normal; color:#666;">(<?php echo $total; ?> kişi)</span>
                        </h3>
                        <input type="text" id="uh-search" placeholder="İsim veya e-posta ara..." class="widefat" style="font-size:12px;">
                    </div>

                    <div id="uh-list" style="max-height: 560px; overflow-y: auto;">
                        <?php foreach($people as $uh):
                            $already = in_array(strtolower(trim($uh['email'])), $sent_emails);
                            $domain = explode('@', $uh['email'])[1] ?? '';
                        ?>
                        <div class="uh-kisi <?php echo $already ? 'uh-sent' : ''; ?>"
                             data-email="<?php echo esc_attr($uh['email']); ?>"
                             data-ad="<?php echo esc_attr($uh['ad']); ?>"
                             data-domain="<?php echo esc_attr($domain); ?>"
                             style="padding: 10px 14px; border-bottom: 1px solid #f5f5f5; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: background .1s;">
                             <div style="flex:1; min-width:0;">
                                <div style="font-weight:700; color:#111827; font-size:13px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?php echo esc_html($uh['ad']); ?>
                                </div>
                                <div style="font-size:12px; color:#6b7280; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?php echo esc_html($uh['email']); ?>
                                </div>
                             </div>
                             <?php if($already): ?>
                                <span style="color:#10b981; font-size: 15px; flex-shrink: 0;" title="Mail gönderildi">✓</span>
                             <?php else: ?>
                                <span style="color:#d1d5db; font-size: 15px; flex-shrink: 0;">○</span>
                             <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <div style="flex: 1; min-width: 300px; max-width: 420px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Mail Gönder</h3>
                    <p style="font-size: 13px; color: #666; margin-top: 0;">
                        Soldan kişi seçin. Yanıtlar <b>info@duybs.com</b> adresine düşer.
                    </p>

                    <div id="uh-secili" style="display:none; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:6px; padding:10px 14px; margin-bottom:16px; font-size:13px; color:#166534;">
                        Seçili: <strong id="uh-secili-ad"></strong>
                    </div>

                    <form id="unihoca-mail-form">
                        <div style="margin-bottom: 15px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Hedef E-Posta Adresi</label>
                            <input type="email" id="uh-email" class="widefat" required placeholder="ornek@duzce.edu.tr">
                        </div>
                        <div style="margin-bottom: 25px;">
                            <label style="display:block; font-weight:bold; margin-bottom:5px; font-size:13px;">Hitap (Sayın ...)</label>
                            <input type="text" id="uh-hitap" class="widefat" required placeholder="Örn: Prof. Dr. Ahmet Yılmaz">
                        </div>

                        <button type="submit" id="btn-send-uh" class="button button-primary button-large" style="width: 100%; text-align:center;">Maili Gönder</button>
                        <div id="uh-response" style="margin-top: 15px; font-weight: bold; text-align: center;"></div>
                    </form>

                    <div style="margin-top: 16px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:10px; padding:14px;">
                        <div style="font-size:12px; font-weight:900; color:#374151; text-transform:uppercase; margin-bottom:10px;">
                            Toplu Gönder
                        </div>

                        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:10px;">
                            <button type="button" id="btn-uh-bulk-send" class="button button-secondary" style="flex:1; min-width: 160px;">
                                Gönderilmeyenleri Toplu Gönder
                            </button>
                            <button type="button" id="btn-uh-bulk-stop" class="button" disabled style="flex:1; min-width: 120px;">
                                Durdur
                            </button>
                        </div>

                        <div style="display:flex; gap:10px; align-items:center; margin-bottom:10px;">
                            <div style="font-size:12px; color:#6b7280; font-weight:700; white-space:nowrap;">Mail Aralığı (ms)</div>
                            <input type="number" id="uh-bulk-delay-ms" value="24000" style="width: 140px;" min="0" step="1000">
                        </div>

                        <div id="uh-bulk-status" style="font-size:12px; color:#6b7280; font-weight:800;">
                            Hazır.
                        </div>
                    </div>
                </div>
            </div>

            <!-- GEÇMİŞ -->
            <div style="flex: 2; min-width: 400px;">
                <div style="background: #fff; padding: 25px; border-radius: 8px; border: 1px solid #ccd0d4; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
                    <h3 style="margin-top: 0; border-bottom: 1px solid #eee; padding-bottom: 10px; color: #1d2327;">Gönderim Geçmişi</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Tarih</th>
                                <th>Uni Hoca</th>
                                <th>E-Posta</th>
                                <th>Durum</th>
                                <th>İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($logs)): ?>
                                <tr><td colspan="5" style="text-align:center;">Henüz uni hocaları mail gönderimi yapılmadı.</td></tr>
                            <?php else: ?>
                                <?php foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo date('d.m.Y H:i', strtotime($log->sent_at)); ?></td>
                                    <td><?php echo esc_html($log->recipient_name); ?></td>
                                    <td style="font-size:12px;"><?php echo esc_html($log->recipient_email); ?></td>
                                    <td>
                                        <?php if($log->status == 'success'): ?>
                                            <span style="color:#10b981; font-weight:bold;">✓ İletildi</span>
                                        <?php else: ?>
                                            <span style="color:#ef4444; font-weight:bold;" title="<?php echo esc_attr($log->error_msg); ?>">❌ Hata</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?page=ybs-uni-hoca-mail&delete_log=<?php echo $log->id; ?>" style="color:red; text-decoration:none;" onclick="return confirm('Kayıt silinsin mi?');">Sil</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    function uhSec(el) {
        document.querySelectorAll('.uh-kisi').forEach(function(k) {
            k.classList.remove('uh-active');
            k.style.background = k.classList.contains('uh-sent') ? '#f0fdf4' : '';
        });
        el.classList.add('uh-active');
        el.style.background = '#dbeafe';

        document.getElementById('uh-email').value = el.dataset.email;
        document.getElementById('uh-hitap').value = el.dataset.ad;

        const seciliDiv = document.getElementById('uh-secili');
        document.getElementById('uh-secili-ad').innerText = el.dataset.ad;
        seciliDiv.style.display = 'block';

        document.getElementById('uh-response').innerHTML = '';
    }

    document.getElementById('uh-search').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.uh-kisi').forEach(function(k) {
            const metin = (k.dataset.ad + ' ' + k.dataset.email + ' ' + k.dataset.domain).toLowerCase();
            k.style.display = metin.includes(q) ? '' : 'none';
        });
    });

    document.querySelectorAll('.uh-sent').forEach(function(k) { k.style.background = '#f0fdf4'; });

    document.getElementById('unihoca-mail-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('btn-send-uh');
        const resDiv = document.getElementById('uh-response');
        const email = document.getElementById('uh-email').value;
        const hitap = document.getElementById('uh-hitap').value;

        btn.disabled = true;
        btn.innerText = 'Gönderiliyor, lütfen bekleyin...';
        resDiv.innerHTML = '';

        const fd = new URLSearchParams();
        fd.append('action', 'ybs_send_unihoca_mail_ajax');
        fd.append('email', email);
        fd.append('hitap', hitap);

        fetch(ajaxurl, {
            method: 'POST',
            body: fd,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                resDiv.style.color = '#10b981';
                resDiv.innerText = '✅ ' + res.data;
                setTimeout(() => location.reload(), 1500);
            } else {
                resDiv.style.color = '#ef4444';
                resDiv.innerText = '❌ Hata: ' + res.data;
                btn.disabled = false;
                btn.innerText = 'Maili Gönder';
            }
        })
        .catch(function() {
            resDiv.style.color = '#ef4444';
            resDiv.innerText = '❌ Sunucu bağlantı hatası.';
            btn.disabled = false;
            btn.innerText = 'Maili Gönder';
        });
    });

    // UNI HOCALARI - TOPLU GÖNDERİM
    let __uh_bulk_cancelled = false;
    function setUhBulkUI(running) {
        const btnSend = document.getElementById('btn-uh-bulk-send');
        const btnStop = document.getElementById('btn-uh-bulk-stop');
        const st = document.getElementById('uh-bulk-status');
        if(!btnSend || !btnStop || !st) return;
        btnSend.disabled = !!running;
        btnStop.disabled = !running;
        st.innerText = running ? 'Gönderim başladı...' : 'Hazır.';
    }

    function updateUhBulkStatus(text) {
        const st = document.getElementById('uh-bulk-status');
        if(st) st.innerText = text;
    }

    function startUhBulkSend() {
        const delayMs = parseInt(document.getElementById('uh-bulk-delay-ms').value || '0', 10);
        const btnSend = document.getElementById('btn-uh-bulk-send');
        const btnStop = document.getElementById('btn-uh-bulk-stop');
        if(!btnSend || !btnStop) return;

        __uh_bulk_cancelled = false;
        setUhBulkUI(true);

        let offset = 0;
        let total = null;
        let okCount = 0;
        let errCount = 0;

        function step() {
            if (__uh_bulk_cancelled) {
                setUhBulkUI(false);
                updateUhBulkStatus('İptal edildi.');
                return;
            }

            const fd = new URLSearchParams();
            fd.append('action', 'ybs_send_unihoca_bulk_ajax');
            fd.append('offset', String(offset));
            fd.append('limit', '1');

            updateUhBulkStatus(total === null ? 'Toplam hesaplanıyor...' : `Gönderiliyor... ${okCount} OK / ${errCount} Hata / Kalan: ${Math.max(0, total - offset)}`);

            fetch(ajaxurl, {
                method: 'POST',
                body: fd,
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    __uh_bulk_cancelled = true;
                    setUhBulkUI(false);
                    updateUhBulkStatus('❌ Hata: ' + (res.data || 'Bilinmeyen hata'));
                    return;
                }

                const data = res.data || {};
                if (total === null && typeof data.total_remaining === 'number') total = data.total_remaining;

                const results = Array.isArray(data.results) ? data.results : [];
                results.forEach(item => {
                    if (item && item.ok) okCount++;
                    else errCount++;
                });

                const hasMore = !!data.has_more;
                const sentNow = results.length;
                offset += sentNow;

                if (hasMore) {
                    setTimeout(step, delayMs);
                } else {
                    setUhBulkUI(false);
                    updateUhBulkStatus(`✅ Tamamlandı. ${okCount} OK, ${errCount} Hata.`);
                    setTimeout(() => location.reload(), 1500);
                }
            })
            .catch(() => {
                __uh_bulk_cancelled = true;
                setUhBulkUI(false);
                updateUhBulkStatus('❌ Sunucu bağlantı hatası.');
            });
        }

        step();
    }

    const btnBulkSend = document.getElementById('btn-uh-bulk-send');
    const btnBulkStop = document.getElementById('btn-uh-bulk-stop');
    if (btnBulkSend) btnBulkSend.addEventListener('click', startUhBulkSend);
    if (btnBulkStop) btnBulkStop.addEventListener('click', function() {
        __uh_bulk_cancelled = true;
    });

    document.querySelectorAll('.uh-kisi').forEach(function(el){
        el.addEventListener('click', function(){ uhSec(el); });
    });
    </script>
    <?php
}

// =========================================================================
// UNI HOCALARI DAVET MAİLLERİ - 4. AJAX: MAİLİ GÖNDER VE KAYDET
// =========================================================================
add_action('wp_ajax_ybs_send_unihoca_mail_ajax', 'ybs_send_unihoca_mail_func');
function ybs_send_unihoca_mail_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    $email = sanitize_email($_POST['email']);
    $hitap = sanitize_text_field($_POST['hitap']);

    if(empty($email) || empty($hitap)) {
        wp_send_json_error('Lütfen tüm alanları doldurun.');
    }

    $subject = '10. Ulusal Yönetim Bilişim Sistemleri Zirvesi – Katılım Daveti';
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: 10. Ulusal YBS Zirvesi <info@duybs.com>'
    );

    $message = "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>

        <div style='text-align: center; line-height: 0;'>
            <img src='https://2026.ybszirve.org.tr/dosyalar/afis.jpeg' alt='10. Ulusal YBS Zirvesi Afişi' style='width: 100%; max-width: 650px; display: block; margin: 0 auto;'>
        </div>

        <div style='padding: 40px 30px; color: #334155; font-size: 15px; line-height: 1.8;'>
            <p style='margin-top: 0; font-size: 16px;'>Sayın <strong>$hitap</strong>,</p>

            <p>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu tarafından düzenlenen <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>, bu yıl Düzce Üniversitesi ev sahipliğinde gerçekleştirilecektir.</p>

            <p><strong>Gençlik ve Spor Bakanlığı</strong> tarafından desteklenen bu organizasyonda, Türkiye'nin <strong>81 ilinden 650'den fazla üniversite öğrencisi</strong> ile bilişim sektörünün önde gelen şirketlerinin yöneticileri bir araya gelecektir.</p>

            <p>Teknoloji, yönetim ve bilişim alanlarında bilgi paylaşımını ve sektör–öğrenci etkileşimini güçlendirmeyi amaçlayan zirvemizde, üniversitemizi ve şehrimizi ulusal ölçekte temsil edecek önemli oturumlar, konuşmalar ve networking etkinlikleri gerçekleştirilecektir.</p>

            <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; border-radius: 6px; padding: 20px 25px; margin: 30px 0;'>
                <p style='margin: 0; font-size: 14px; color: #065f46; font-weight: 600;'>Bu kapsamda sizleri, <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>'nde aramızda görmekten onur duyarız. Katılımınız, hem etkinliğimize değer katacak hem de öğrencilerimiz için önemli bir motivasyon kaynağı olacaktır.</p>
            </div>

            <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin: 30px 0; text-align: center;'>
                <h3 style='margin: 0 0 15px 0; font-size: 16px; color: #0f172a;'>Zirve Hakkında Daha Fazla Bilgi</h3>
                <div style='margin-bottom: 20px;'>
                    <a href='https://2026.ybszirve.org.tr/program/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>📅 Program Akışı</a>
                    <a href='https://2026.ybszirve.org.tr/konusmacilar/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>🎤 Konuşmacılar</a>
                </div>
                <a href='https://2026.ybszirve.org.tr' target='_blank' style='display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: bold; font-size: 15px;'>🌐 Zirve Web Sitesini Ziyaret Edin</a>
            </div>

            <p style='margin-top: 30px; margin-bottom: 5px; color: #64748b;'>Katılımınız bizleri onurlandıracaktır.</p>
            <p style='margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 16px;'>Saygılarımızla,</p>
            <p style='margin-top: 5px; color: #64748b; font-weight: 600; font-size: 16px;'>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu</p>
        </div>

        <div style='background-color: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;'>
            <p style='margin: 0 0 6px 0; font-size: 14px; color: #475569;'>E-posta: <a href='mailto:info@duybs.com' style='color: #10b981; text-decoration: none; font-weight: 600;'>info@duybs.com</a></p>
            <p style='margin: 0; font-size: 14px; color: #475569;'>Web: <a href='https://duybs.com' target='_blank' style='color: #10b981; text-decoration: none; font-weight: 600;'>duybs.com</a></p>
            <p style='margin: 10px 0 0 0; font-size: 13px; color: #94a3b8;'>&copy; 2026 YBS Zirvesi Organizasyon Komitesi</p>
        </div>
    </div>
    ";

    $is_sent = wp_mail($email, $subject, $message, $headers);

    global $wpdb;
    $table = $wpdb->prefix . 'ybs_unihoca_logs';

    if ($is_sent) {
        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'status'          => 'success',
            'error_msg'       => '',
            'sent_at'         => current_time('mysql')
        ]);
        wp_send_json_success('Mail başarıyla gönderildi ve listeye eklendi.');
    } else {
        global $phpmailer;
        $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';

        $wpdb->insert($table, [
            'recipient_email' => $email,
            'recipient_name'  => $hitap,
            'status'          => 'error',
            'error_msg'       => $error_msg,
            'sent_at'         => current_time('mysql')
        ]);

        wp_send_json_error('Mail iletilemedi. Detay: ' . $error_msg);
    }
}

// =========================================================================
// UNI HOCALARI - MAİL BODY (TOPLU GÖNDERİM İÇİN)
// =========================================================================
function ybs_unihoca_build_message($hitap) {
    return "
    <div style='font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, Helvetica, Arial, sans-serif; max-width: 650px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background-color: #ffffff; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);'>

        <div style='text-align: center; line-height: 0;'>
            <img src='https://2026.ybszirve.org.tr/dosyalar/afis.jpeg' alt='10. Ulusal YBS Zirvesi Afişi' style='width: 100%; max-width: 650px; display: block; margin: 0 auto;'>
        </div>

        <div style='padding: 40px 30px; color: #334155; font-size: 15px; line-height: 1.8;'>
            <p style='margin-top: 0; font-size: 16px;'>Sayın <strong>$hitap</strong>,</p>

            <p>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu tarafından düzenlenen <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>, bu yıl Düzce Üniversitesi ev sahipliğinde gerçekleştirilecektir.</p>

            <p><strong>Gençlik ve Spor Bakanlığı</strong> tarafından desteklenen bu organizasyonda, Türkiye'nin <strong>81 ilinden 650'den fazla üniversite öğrencisi</strong> ile bilişim sektörünün önde gelen şirketlerinin yöneticileri bir araya gelecektir.</p>

            <p>Teknoloji, yönetim ve bilişim alanlarında bilgi paylaşımını ve sektör–öğrenci etkileşimini güçlendirmeyi amaçlayan zirvemizde, üniversitemizi ve şehrimizi ulusal ölçekte temsil edecek önemli oturumlar, konuşmalar ve networking etkinlikleri gerçekleştirilecektir.</p>

            <div style='background-color: #f0fdf4; border-left: 4px solid #10b981; border-radius: 6px; padding: 20px 25px; margin: 30px 0;'>
                <p style='margin: 0; font-size: 14px; color: #065f46; font-weight: 600;'>Bu kapsamda sizleri, <strong>10. Ulusal Yönetim Bilişim Sistemleri Zirvesi</strong>'nde aramızda görmekten onur duyarız. Katılımınız, hem etkinliğimize değer katacak hem de öğrencilerimiz için önemli bir motivasyon kaynağı olacaktır.</p>
            </div>

            <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 25px; margin: 30px 0; text-align: center;'>
                <h3 style='margin: 0 0 15px 0; font-size: 16px; color: #0f172a;'>Zirve Hakkında Daha Fazla Bilgi</h3>
                <div style='margin-bottom: 20px;'>
                    <a href='https://2026.ybszirve.org.tr/program/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>📅 Program Akışı</a>
                    <a href='https://2026.ybszirve.org.tr/konusmacilar/' target='_blank' style='display: inline-block; background-color: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; text-decoration: none; padding: 10px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; margin: 5px;'>🎤 Konuşmacılar</a>
                </div>
                <a href='https://2026.ybszirve.org.tr' target='_blank' style='display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: bold; font-size: 15px;'>🌐 Zirve Web Sitesini Ziyaret Edin</a>
            </div>

            <p style='margin-top: 30px; margin-bottom: 5px; color: #64748b;'>Katılımınız bizleri onurlandıracaktır.</p>
            <p style='margin-bottom: 0; font-weight: 700; color: #0f172a; font-size: 16px;'>Saygılarımızla,</p>
            <p style='margin-top: 5px; font-weight: 600; color: #64748b; font-size: 16px;'>Düzce Üniversitesi Yönetim Bilişim Sistemleri Topluluğu</p>
        </div>

        <div style='background-color: #f1f5f9; border-top: 1px solid #e2e8f0; padding: 20px 30px; text-align: center;'>
            <p style='margin: 0 0 6px 0; font-size: 14px; color: #475569;'>E-posta: <a href='mailto:info@duybs.com' style='color: #10b981; text-decoration: none; font-weight: 600;'>info@duybs.com</a></p>
            <p style='margin: 0; font-size: 14px; color: #475569;'>Web: <a href='https://duybs.com' target='_blank' style='color: #10b981; text-decoration: none; font-weight: 600;'>duybs.com</a></p>
            <p style='margin: 10px 0 0 0; font-size: 13px; color: #94a3b8;'>&copy; 2026 YBS Zirvesi Organizasyon Komitesi</p>
        </div>
    </div>
    ";
}

// =========================================================================
// UNI HOCALARI - 5. AJAX: TOPLU GÖNDER (1 KİŞİ / CALL)
// =========================================================================
add_action('wp_ajax_ybs_send_unihoca_bulk_ajax', 'ybs_send_unihoca_bulk_func');
function ybs_send_unihoca_bulk_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz işlem');

    global $wpdb;
    $table = $wpdb->prefix . 'ybs_unihoca_logs';

    $offset = max(0, intval($_POST['offset'] ?? 0));
    $limit  = max(1, min(5, intval($_POST['limit'] ?? 1)));

    // Dosya yoksa bile fallback olarak gömülü listeden alır.
    $people = ybs_unihoca_get_people();

    // Gönderilenleri (success olanlar) atla
    $sent_emails = $wpdb->get_col("SELECT recipient_email FROM $table WHERE status='success'");
    $sent_lc = [];
    foreach ($sent_emails as $em) {
        $sent_lc[strtolower(trim((string) $em))] = true;
    }

    $unsent = [];
    foreach ($people as $p) {
        $lc = strtolower(trim((string) $p['email']));
        if (!isset($sent_lc[$lc])) $unsent[] = $p;
    }

    $total = count($unsent);
    if ($total === 0) {
        wp_send_json_success([
            'results' => [],
            'has_more' => false,
            'total_remaining' => 0
        ]);
    }

    if ($offset >= $total) {
        wp_send_json_success([
            'results' => [],
            'has_more' => false,
            'total_remaining' => $total
        ]);
    }

    $batch = array_slice($unsent, $offset, $limit);

    $subject = '10. Ulusal Yönetim Bilişim Sistemleri Zirvesi – Katılım Daveti';
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'Reply-To: 10. Ulusal YBS Zirvesi <info@duybs.com>'
    );

    $results = [];
    foreach ($batch as $p) {
        $email = sanitize_email($p['email']);
        $hitap = sanitize_text_field($p['ad']);
        if (empty($email) || empty($hitap) || !is_email($email)) continue;

        $message = ybs_unihoca_build_message($hitap);
        $ok = wp_mail($email, $subject, $message, $headers);

        if ($ok) {
            $wpdb->insert($table, [
                'recipient_email' => $email,
                'recipient_name'  => $hitap,
                'status'          => 'success',
                'error_msg'       => '',
                'sent_at'         => current_time('mysql')
            ]);
            $results[] = ['email' => $email, 'ok' => true, 'msg' => 'Gönderildi'];
        } else {
            global $phpmailer;
            $error_msg = isset($phpmailer->ErrorInfo) ? $phpmailer->ErrorInfo : 'Bilinmeyen SMTP hatası.';
            $wpdb->insert($table, [
                'recipient_email' => $email,
                'recipient_name'  => $hitap,
                'status'          => 'error',
                'error_msg'       => $error_msg,
                'sent_at'         => current_time('mysql')
            ]);
            $results[] = ['email' => $email, 'ok' => false, 'msg' => $error_msg];
        }
    }

    $new_offset = $offset + count($batch);
    $has_more = $new_offset < $total;

    wp_send_json_success([
        'results' => $results,
        'has_more' => $has_more,
        'total_remaining' => $total
    ]);
}

// =========================================================================
// AJAX: SİSTEMİ KOMPLE AÇ / KAPA (SOLD OUT MANTIĞI)
// =========================================================================
add_action('wp_ajax_ybs_toggle_system_status', 'ybs_toggle_system_status_func');
function ybs_toggle_system_status_func() {
    if (!current_user_can('manage_options')) wp_send_json_error('Yetkisiz');
    
    $current_status = get_option('ybs_system_sold_out', '0'); // 0: Açık, 1: Kapalı (Sold Out)
    $new_status = ($current_status == '1') ? '0' : '1';
    
    update_option('ybs_system_sold_out', $new_status);
    wp_send_json_success(['new_status' => $new_status]);
}