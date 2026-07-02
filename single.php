<?php
/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package YBS_Zirvesi_2026
 */
?>
<?php get_header(); ?>

<?php while ( have_posts() ) : the_post(); ?>

    <header class="page-hero-modern">
        
        <div class="hero-decor-dots"></div>

        <div class="container page-container">
            <div class="hero-content">
                
                <div class="post-meta-top">
                    <span class="meta-cat">
                        <?php 
                        // Sadece ilk kategoriyi göster
                        $categories = get_the_category();
                        if ( ! empty( $categories ) ) {
                            echo esc_html( $categories[0]->name );   
                        }
                        ?>
                    </span>
                    <span class="meta-sep">•</span>
                    <span class="meta-date"><?php echo get_the_date('d F Y'); ?></span>
                </div>

                <h1 class="hero-title"><?php the_title(); ?></h1>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container page-container">
            
            <article class="single-post-wrapper">
                
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="post-featured-image">
                        <?php the_post_thumbnail('full'); ?>
                    </div>
                <?php endif; ?>

                <div class="content-wrapper">
                    <?php the_content(); ?>
                </div>

                <footer class="post-footer">
                    <?php if(has_tag()): ?>
                        <div class="post-tags">
                            <span class="tags-label">Etiketler:</span>
                            <?php the_tags('', '', ''); ?>
                        </div>
                    <?php endif; ?>

                    <div class="post-navigation">
                        <div class="nav-previous">
                            <?php previous_post_link('%link', '← Önceki Yazı'); ?>
                        </div>
                        <div class="nav-next">
                            <?php next_post_link('%link', 'Sonraki Yazı →'); ?>
                        </div>
                    </div>
                </footer>

            </article>

        </div>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>