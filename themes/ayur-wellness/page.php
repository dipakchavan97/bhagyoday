<?php
/**
 * Generic Page Template for Ayur Wellness
 */

get_header();
?>

<div class="ayur-page-header" style="background:#FAF8F5; border-bottom:1px solid #E5E0D8; padding:40px 0;">
    <div class="ayur-container">
        <h1 class="ayur-section-title" style="margin:0; font-size:32px;"><?php the_title(); ?></h1>
    </div>
</div>

<div class="ayur-container" style="padding:50px 20px;">
    <div style="background:#FFFFFF; border:1px solid #E5E0D8; border-radius:12px; padding:36px; box-shadow:var(--ayur-shadow-soft); max-width:960px; margin:0 auto; line-height:1.8;">
        <?php
        while (have_posts()) :
            the_post();
            the_content();
        endwhile;
        ?>
    </div>
</div>

<?php
get_footer();
