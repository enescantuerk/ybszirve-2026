<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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
                <h1 class="hero-title"><?php the_title(); ?></h1>
            </div>
        </div>
    </header>

    <main class="page-content">
        <div class="container page-container">
            <div class="content-wrapper">
                <?php the_content(); ?>
            </div>
        </div>
    </main>

<?php endwhile; ?>

<?php get_footer(); ?>