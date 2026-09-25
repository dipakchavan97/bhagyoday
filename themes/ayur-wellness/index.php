<?php
/**
 * Main Blog Template for Ayur Wellness
 * Premium D2C Editorial Architecture — Bhagyoday Ayurveda
 */

defined('ABSPATH') || exit;

get_header();
?>

<div class="ayur-page-header">
    <div class="ayur-container">
        <h1 class="ayur-archive-title"><?php _e('Ayurvedic Wellness & Herbal Wisdom Blog', 'ayur-wellness'); ?></h1>
        <p class="ayur-archive-desc"><?php _e('Classical Ayurvedic health guides, herbal insights, and daily lifestyle rituals.', 'ayur-wellness'); ?></p>
    </div>
</div>

<div class="ayur-container ayur-archive-body">
    <?php if (have_posts()) : ?>
        <div class="ayur-blog-grid">
            <?php while (have_posts()) : the_post(); ?>
                <article class="ayur-blog-card">
                    <?php if (has_post_thumbnail()) : ?>
                        <div class="ayur-blog-thumb-wrap">
                            <?php the_post_thumbnail('medium', array('class' => 'ayur-blog-thumb', 'loading' => 'lazy')); ?>
                        </div>
                    <?php else : ?>
                        <div class="ayur-blog-thumb-fallback">
                            <?php echo ayur_svg_icon('leaf', '', 36); ?>
                        </div>
                    <?php endif; ?>
                    <div class="ayur-blog-card-body">
                        <div class="ayur-blog-date">
                            <?php echo get_the_date(); ?>
                        </div>
                        <h2 class="ayur-blog-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="ayur-blog-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="ayur-blog-read-more">
                            <span><?php _e('Read Full Article', 'ayur-wellness'); ?></span>
                            <?php echo ayur_svg_icon('arrow-right', '', 14); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <div class="ayur-pagination-wrap">
            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <div class="ayur-empty-state">
            <div class="ayur-empty-icon"><?php echo ayur_svg_icon('leaf', '', 40); ?></div>
            <p><?php _e('No wellness articles published yet.', 'ayur-wellness'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php
get_footer();
