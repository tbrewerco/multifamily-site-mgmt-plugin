<?php

/**
 * The template for displaying unit posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package your-plugin-name
 */

get_header();
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        while (have_posts()) :
            the_post();
        ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                </header>

                <div class="entry-content">
                    <?php the_content(); ?>

                    <?php if (get_post_meta(get_the_ID(), '_asset_id', true)) : ?>
                        <p><strong><?php _e('Asset ID:', 'wp11'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_asset_id', true)); ?></p>
                    <?php endif; ?>

                    <?php if (get_post_meta(get_the_ID(), '_building_id', true)) : ?>
                        <p><strong><?php _e('Building ID:', 'wp11'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_building_id', true)); ?></p>
                    <?php endif; ?>

                    <?php if (get_post_meta(get_the_ID(), '_floor_id', true)) : ?>
                        <p><strong><?php _e('Floor ID:', 'wp11'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_floor_id', true)); ?></p>
                    <?php endif; ?>

                    <?php if (get_post_meta(get_the_ID(), '_floor_plan_id', true)) : ?>
                        <p><strong><?php _e('Floor Plan ID:', 'wp11'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_floor_plan_id', true)); ?></p>
                    <?php endif; ?>

                    <?php if (get_post_meta(get_the_ID(), '_area', true)) : ?>
                        <p><strong><?php _e('Area:', 'wp11'); ?></strong> <?php echo esc_html(get_post_meta(get_the_ID(), '_area', true)); ?></p>
                    <?php endif; ?>
                </div>

            <?php endwhile;
            ?>

    </main>
</div>

<?php
get_footer();
