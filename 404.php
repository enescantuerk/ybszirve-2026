<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package YBS_Zirvesi_2026
 */
?>
<?php get_header(); ?>

<main class="error-page-wrapper">
    
    <div class="hero-decor-dots"></div>

    <div class="container page-container">
        <div class="error-content">
            
            <div class="error-number">404</div>
            
            <div class="error-console">
                <span class="cmd-icon">></span> System.Error: <span class="cmd-highlight">Page_Not_Found</span>
            </div>

            <h1 class="error-title">Aradığınız sayfaya ulaşılamıyor.</h1>
            <p class="error-desc">
                Gitmek istediğiniz sayfa silinmiş, taşınmış veya bağlantı adresi hatalı olabilir. 
                Endişelenmeyin, hala zirve alanındasınız.
            </p>

            <a href="<?php echo home_url(); ?>" class="btn-error-home">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Ana Sayfaya Dön</span>
            </a>

        </div>
    </div>
</main>

<?php get_footer(); ?>