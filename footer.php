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
                <?php // TODO: Replace the legal placeholder URLs when their pages are defined. ?>
                <a href="#"><?php esc_html_e('Terms of Use', 'nuventures'); ?></a>.
                <a href="#"><?php esc_html_e('Disclaimer', 'nuventures'); ?></a>
            </p>
            <p>
                <a href="mailto:NuVConnect@nuware.com">
                    <?php esc_html_e('Write to us at: NuVConnect@nuware.com', 'nuventures'); ?>
                </a>
            </p>
        </div>

        <?php // TODO: Replace the footer navigation placeholder URLs when final routes are defined. ?>
        <nav class="site-footer__navigation" aria-label="<?php esc_attr_e('Footer navigation', 'nuventures'); ?>">
            <ul>
                <li><a href="#"><?php esc_html_e('Home', 'nuventures'); ?></a></li>
                <li><a href="#"><?php esc_html_e('About Us', 'nuventures'); ?></a></li>
                <li><a href="#"><?php esc_html_e('Team', 'nuventures'); ?></a></li>
                <li><a href="#"><?php esc_html_e('Portfolio', 'nuventures'); ?></a></li>
                <li><a href="#"><?php esc_html_e('NuPOD', 'nuventures'); ?></a></li>
            </ul>
        </nav>

        <?php // TODO: Replace the social placeholder URLs with the official profiles. ?>
        <ul class="site-footer__socials" aria-label="<?php esc_attr_e('Social media', 'nuventures'); ?>">
            <li>
                <a href="#" aria-label="<?php esc_attr_e('YouTube', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($footer_asset_path . 'youtube.svg'); ?>" alt="" width="24" height="24">
                </a>
            </li>
            <li>
                <a href="#" aria-label="<?php esc_attr_e('X', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($footer_asset_path . 'x.svg'); ?>" alt="" width="24" height="24">
                </a>
            </li>
            <li>
                <a href="#" aria-label="<?php esc_attr_e('Instagram', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($footer_asset_path . 'instagram.svg'); ?>" alt="" width="24" height="24">
                </a>
            </li>
            <li>
                <a href="#" aria-label="<?php esc_attr_e('LinkedIn', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($footer_asset_path . 'linkedin.svg'); ?>" alt="" width="24" height="24">
                </a>
            </li>
        </ul>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
