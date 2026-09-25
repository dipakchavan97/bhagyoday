<?php
/**
 * Main Blog Template for Ayur Wellness
 */

get_header();
?>

<div class="ayur-page-header" style="background:#FAF8F5; border-bottom:1px solid #E5E0D8; padding:40px 0;">
    <div class="ayur-container">
        <h1 class="ayur-section-title" style="margin:0; font-size:32px;"><?php _e('Ayurvedic Wellness & Herbal Wisdom Blog', 'ayur-wellness'); ?></h1>
        <p style="color:#6B7280; font-size:15px; margin-top:8px;"><?php _e('Classical Ayurvedic health guides, herbal insights, and daily lifestyle rituals.', 'ayur-wellness'); ?></p>
    </div>
</div>

<div class="ayur-container" style="padding:50px 20px;">
    <?php if (have_posts()) : ?>
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:30px;">
            <?php while (have_posts()) : the_post(); ?>
                <article style="background:#FFFFFF; border:1px solid #E5E0D8; border-radius:12px; overflow:hidden; display:flex; flex-direction:column;">
                    <?php if (has_post_thumbnail()) : ?>
                        <div style="height:200px; overflow:hidden;">
                            <?php the_post_thumbnail('medium', array('style' => 'width:100%; height:100%; object-fit:cover;')); ?>
                        </div>
                    <?php else : ?>
                        <div style="height:140px; background:#F4F2EC; display:flex; align-items:center; justify-content:center; font-size:48px;">
                            📜
                        </div>
                    <?php endif; ?>
                    <div style="padding:24px; display:flex; flex-direction:column; flex:1;">
                        <div style="font-size:12px; color:#A6833D; font-weight:700; text-transform:uppercase; margin-bottom:8px;">
                            <?php echo get_the_date(); ?>
                        </div>
                        <h2 style="font-size:18px; margin-bottom:10px; font-family:'Cinzel', serif;">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div style="font-size:14px; color:#5C6A64; margin-bottom:16px; line-height:1.6;">
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" style="margin-top:auto; font-weight:700; color:#1E3A2F; font-size:13px; text-transform:uppercase;">
                            Read Full Article →
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <div style="margin-top:40px; text-align:center;">
            <?php the_posts_pagination(); ?>
        </div>
    <?php else : ?>
        <p style="text-align:center; color:#6B7280;"><?php _e('No wellness articles published yet.', 'ayur-wellness'); ?></p>
    <?php endif; ?>
</div>

<?php
get_footer();
