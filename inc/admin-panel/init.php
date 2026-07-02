<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// AJAX İşleyicisini Dahil Et
require_once get_template_directory() . '/inc/admin-panel/ajax.php';

// 1. Departmanlar (Custom Post Type)
function ybs_register_department_cpt() {
    register_post_type( 'departman', array(
        'labels' => array(
            'name' => 'Departmanlar',
            'singular_name' => 'Departman',
            'add_new' => 'Yeni Departman Ekle',
            'add_new_item' => 'Yeni Departman Ekle',
            'edit_item' => 'Departmanı Düzenle',
        ),
        'public' => false,  // Sitede herkes görmesin
        'show_ui' => true,  // Admin panelde görünsün
        'show_in_menu' => false, // Kendi özel menümüze ekleyeceğiz
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-groups',
    ));
}
add_action( 'init', 'ybs_register_department_cpt' );

// 2. Özel Üye Rolü Oluşturma
function ybs_add_community_role() {
    add_role( 'topluluk_uyesi', 'Topluluk Üyesi', array( 'read' => true ) );
}
add_action( 'init', 'ybs_add_community_role' );

// 3. Admin Menüsü Oluşturma (GÜNCELLENDİ)
function ybs_community_admin_menu() {
    // Ana Menü
    add_menu_page(
        'Topluluk Yön.', 
        'Topluluk Yön.', 
        'manage_options', 
        'ybs-topluluk-dashboard', 
        'ybs_render_page', 
        'dashicons-networking', 
        3 
    );

    // Alt Menü: Dashboard (Ana menü ile aynı slug'a sahip olmalı ki ilk seçenek olsun)
    add_submenu_page( 
        'ybs-topluluk-dashboard', 
        'Genel Bakış', 
        'Genel Bakış', 
        'manage_options', 
        'ybs-topluluk-dashboard', 
        'ybs_render_page' 
    );
    
    // Alt Menü: Üyeler (Özel Sayfa)
    add_submenu_page( 
        'ybs-topluluk-dashboard', 
        'Üye Listesi', 
        'Üyeler', 
        'manage_options', 
        'ybs-uyeler', 
        'ybs_render_page' 
    );

    // Alt Menü: Departmanlar (Özel Sayfa)
    add_submenu_page( 
        'ybs-topluluk-dashboard', 
        'Departmanlar', 
        'Departmanlar', 
        'manage_options', 
        'ybs-departmanlar', 
        'ybs_render_page' 
    );

    // Alt Menü: Dosya Merkezi (Standart WP Medya Kütüphanesi)
    add_submenu_page( 
        'ybs-topluluk-dashboard', 
        'Dosya Merkezi', 
        'Dosya Merkezi', 
        'manage_options', 
        'ybs-dosyalar', // Slug değişti
        'ybs_render_page' 
    );

    add_submenu_page(
        'ybs-topluluk-dashboard',
        'Katılımcı CVleri',
        'Katılımcı CVleri',
        'manage_options',
        'ybs-katilimci-cv',
        'ybs_render_page'
    );

    add_submenu_page(
        'ybs-topluluk-dashboard',
        'Sertifika Gönder',
        'Sertifika Gönder',
        'manage_options',
        'ybs-sertifika-gonder',
        'ybs_render_page'
    );

    add_submenu_page(
        'ybs-topluluk-dashboard',
        'Ekip Sertifika Gönder',
        'Ekip Sertifika Gönder',
        'manage_options',
        'ybs-ekip-sertifika-gonder',
        'ybs_render_page'
    );
}
// EKSİK OLAN SATIR BUYDU:
add_action( 'admin_menu', 'ybs_community_admin_menu' );

// SAYFA YÖNLENDİRİCİ (ROUTER)
function ybs_render_page() {
    // Hangi sayfada olduğumuzu $_GET['page'] ile daha güvenli alabiliriz
    $page = isset($_GET['page']) ? $_GET['page'] : '';
    
    if ( $page === 'ybs-uyeler' ) {
        require_once get_template_directory() . '/inc/admin-panel/views/list-members.php';
    } elseif ( $page === 'ybs-departmanlar' ) {
        require_once get_template_directory() . '/inc/admin-panel/views/list-departments.php';
    } elseif ( $page === 'ybs-dosyalar' ) {
        require_once get_template_directory() . '/inc/admin-panel/views/file-manager.php';
    } elseif ( $page === 'ybs-katilimci-cv' ) {
        require_once get_template_directory() . '/inc/admin-panel/views/list-katilimci-cv.php';
    } elseif ( $page === 'ybs-sertifika-gonder' ) {
        require_once get_template_directory() . '/inc/admin-panel/views/cert-sender.php';
    } elseif ( $page === 'ybs-ekip-sertifika-gonder' ) {
        require_once get_template_directory() . '/inc/admin-panel/views/cert-sender-org.php';
    } else {
        // Varsayılan olarak Dashboard
        require_once get_template_directory() . '/inc/admin-panel/dashboard.php';
}
}