<?php
/**
 * Global site footer.
 *
 * @package NuVentures
 */

$footer_asset_path = get_template_directory_uri() . '/assets/images/footer/';
?>

<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__legal">
            <p>
                <?php esc_html_e('Copyright 2026-27. NuVentures Pvt Ltd. All Rights reserved.', 'nuventures'); ?>
                <a href="<?php echo esc_url(home_url('/disclaimer')); ?>"><?php esc_html_e('Disclaimer', 'nuventures'); ?></a> &amp; <a href="<?php echo esc_url(home_url('/privacy-policy')); ?>"><?php esc_html_e('Privacy Policy', 'nuventures'); ?></a>
            </p>
            <p>
                <a href="mailto:NuVConnect@nuware.com">
                    <?php esc_html_e('Write to us at: NuVConnect@nuware.com', 'nuventures'); ?>
                </a>
            </p>
        </div>

        <nav class="site-footer__navigation" aria-label="<?php esc_attr_e('Footer navigation', 'nuventures'); ?>">
            <ul>
                <li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Home', 'nuventures'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/portfolio')); ?>"><?php esc_html_e('Portfolio', 'nuventures'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/about')); ?>"><?php esc_html_e('About', 'nuventures'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/initiatives')); ?>"><?php esc_html_e('Initiatives', 'nuventures'); ?></a></li>
                <li><a href="<?php echo esc_url(home_url('/nupod')); ?>"><?php esc_html_e('NuPOD', 'nuventures'); ?></a></li>
            </ul>
        </nav>

        <?php ?>
        <ul class="site-footer__socials" aria-label="<?php esc_attr_e('Social media', 'nuventures'); ?>">
            <li>
                <a href="https://www.youtube.com/@nuventures" target="_blank" aria-label="<?php esc_attr_e('YouTube', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($footer_asset_path . 'youtube.svg'); ?>" alt="" width="24" height="24">
                </a>
            </li>
            
            <li>
                <a href="https://www.linkedin.com/company/nuventures" aria-label="<?php esc_attr_e('LinkedIn', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($footer_asset_path . 'linkedin.svg'); ?>" alt="" width="24" height="24">
                </a>
            </li>
        </ul>
    </div>
</footer>

<?php get_template_part('template-parts/global/cookie-consent'); ?>

<?php wp_footer(); ?>

</body>
</html>
