<?php
/* Template Name: İletişim Sayfası */
get_header(); 
?>

<?php while ( have_posts() ) : the_post(); ?>

    <header class="page-hero-modern">
        <div class="hero-decor-dots"></div>
        <div class="container page-container">
            <div class="hero-content">
                <h1 class="hero-title"><?php the_title(); ?></h1>
                <p class="hero-subtitle">Soruların mı var? Sponsorluk veya iş birliği için bize ulaş.</p>
            </div>
        </div>
    </header>

    <main class="page-content contact-page-wrapper">
        <div class="container page-container">
			            
            <div class="contact-grid">
                
                <div class="contact-info-col">
                    <div class="info-card">
                        <h3>Bize Ulaşın</h3>
                        <p class="info-desc">Doğrudan e-posta gönderebilir veya sosyal medya hesaplarımızdan bizi takip edebilirsin.</p>
                        
                        <div class="email-section">
                            <a href="mailto:info@2026.ybszirve.org.tr" class="email-main-link">
                                <div class="email-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path></svg>
                                </div>
                                <div class="email-text">
                                    <span class="label">E-Posta Adresi</span>
                                    <span class="value">info@2026.ybszirve.org.tr</span>
                                </div>
                            </a>
                        </div>

                        <hr class="contact-divider">

                        <div class="social-vertical-list">
                            <h4 class="social-title">Sosyal Medya</h4>
                            
                            <a href="https://www.instagram.com/du.ybs" target="_blank" class="social-btn-horizontal">
                                <div class="btn-icon-box">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                                </div>
                                <span class="btn-username">@du.ybs</span>
                                <span class="btn-arrow">→</span>
                            </a>

                            <a href="https://x.com/DuYbs" target="_blank" class="social-btn-horizontal">
                                <div class="btn-icon-box">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l11.733 16h4.267l-11.733 -16z"></path><path d="M4 20l6.768 -6.768m2.46 -2.46l6.772 -6.772"></path></svg>
                                </div>
                                <span class="btn-username">@du.ybs</span>
                                <span class="btn-arrow">→</span>
                            </a>

                            <a href="https://www.linkedin.com/company/du-y%C3%B6netim-bili%C5%9Fim-sistemleri-%C3%B6%C4%9Frenci-toplulu%C4%9Fu/" target="_blank" class="social-btn-horizontal">
                                <div class="btn-icon-box">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                                </div>
                                <span class="btn-username">@du.ybs</span>
                                <span class="btn-arrow">→</span>
                            </a>
                            
                            <a href="https://www.youtube.com/@duybs" target="_blank" class="social-btn-horizontal">
                                <div class="btn-icon-box">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"></path><path d="m10 15 5-3-5-3z"></path></svg>
                                </div>
                                <span class="btn-username">@du.ybs</span>
                                <span class="btn-arrow">→</span>
                            </a>

                        </div>
                    </div>
                </div>

                <div class="contact-form-col">
                    <div class="form-wrapper">
                        <?php echo do_shortcode('[contact-form-7 id="5d73191" title="İletişim Formu"]'); ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>