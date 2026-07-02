<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package YBS_Zirvesi_2026
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;900&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-4GVPTXMRW0"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-4GVPTXMRW0');
</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">

	<header id="masthead" class="site-header">
		<div class="navbar-wrapper">
            <div class="navbar-inner">
                
                <div class="site-branding">
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" class="logo-link">
                        <div class="brand-logos">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/zirvelogo.png" alt="Zirve Logo" class="nav-logo">
                            
                            <span class="logo-sep">|</span>
                            
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/img/duybs-black.png" alt="DÜYBS" class="nav-logo">
                        </div>
                    </a>
                </div>

                <nav id="site-navigation" class="main-navigation">
                    <?php
                    wp_nav_menu(
                        array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'container'      => false,
                            'fallback_cb'    => false, // Menü atanmazsa boş kalsın
                        )
                    );
                    ?>
                    
                    <div class="mobile-menu-cta">
                        <a href="https://2026.ybszirve.org.tr/rezervasyon/" class="btn-cta">
                            <span class="btn-text">Rezervasyon Yap</span>
                            <span class="btn-icon">→</span>
                        </a>
                    </div>
                </nav>

                <div class="header-actions desktop-cta">
                    <a href="https://2026.ybszirve.org.tr/rezervasyon/" class="btn-cta">
                        <span class="btn-text">Rezervasyon Yap</span>
                        <span class="btn-icon">→</span>
                    </a>
                </div>
                
                <button class="menu-toggle" aria-controls="primary-menu" aria-expanded="false">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span> </button>

            </div>
		</div>
	</header>
    
    <div id="content" class="site-content">
		
		
		<style>
		/* Masaüstü Görünümü: Mobil butonu gizle */
.mobile-menu-cta {
    display: none;
}

/* Mobil Görünümü: 991px veya 768px kendi temanın mobil kırılma noktasına (breakpoint) göre ayarlayabilirsin */
@media screen and (max-width: 991px) {
    
    /* Üst sağ köşedeki masaüstü butonunu gizle */
    .header-actions.desktop-cta {
        display: none !important;
    }

    /* Açılır menü içindeki butonu göster */
    .mobile-menu-cta {
        display: block;
        padding: 20px;
        margin-top: 10px;
        border-top: 1px solid #eaeaea30; /* Menü linklerinden ayırmak için ince bir çizgi */
        text-align: center;
    }

    /* Mobil butonun tam genişlikte olmasını istersen */
    .mobile-menu-cta .btn-cta {
        display: flex;
        justify-content: center;
        width: 100%;
        box-sizing: border-box;
    }
}</style>