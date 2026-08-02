<?php
/**
 * Global cookie-consent banner.
 *
 * @package NuVentures
 */
?>

<aside
    class="cookie-consent"
    data-cookie-consent
    role="dialog"
    aria-labelledby="cookie-consent-title"
    aria-describedby="cookie-consent-description"
    hidden
>
    <h2 id="cookie-consent-title"><?php esc_html_e('We use cookies', 'nuventures'); ?></h2>
    <p id="cookie-consent-description">
        <?php
        esc_html_e(
            'We use essential cookies to make this website work and optional cookies to understand usage and improve your experience. You can agree or continue with essential cookies only.',
            'nuventures'
        );
        ?>
    </p>

    <div class="cookie-consent__actions">
        <button type="button" data-cookie-consent-choice="all">
            <?php esc_html_e('Agree', 'nuventures'); ?>
        </button>
        <button type="button" data-cookie-consent-choice="essential">
            <?php esc_html_e('Essential only', 'nuventures'); ?>
        </button>
    </div>
</aside>
