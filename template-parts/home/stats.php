<?php
/**
 * Homepage company statistics.
 *
 * @package NuVentures
 */
?>

<section class="home-stats" data-stats aria-label="<?php esc_attr_e('NuVentures statistics', 'nuventures'); ?>">
    <ul class="home-stats__grid">
        <li class="home-stats__item" aria-label="<?php esc_attr_e('42 Founders Backed', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="42" aria-hidden="true">42</span>
            <span class="home-stats__label"><?php esc_html_e('Founders Backed', 'nuventures'); ?></span>
        </li>
        <li class="home-stats__item" aria-label="<?php esc_attr_e('32 Companies Backed', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="32" aria-hidden="true">32</span>
            <span class="home-stats__label"><?php esc_html_e('Companies Backed', 'nuventures'); ?></span>
        </li>
        <li class="home-stats__item" aria-label="<?php esc_attr_e('6 Partners', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="6" aria-hidden="true">6</span>
            <span class="home-stats__label"><?php esc_html_e('Advisors & Partners', 'nuventures'); ?></span>
        </li>
        <li class="home-stats__item" aria-label="<?php esc_attr_e('75M dollars Committed Capital', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="75" data-stat-prefix="$" data-stat-suffix="M" aria-hidden="true">$75M</span>
            <span class="home-stats__label"><?php esc_html_e('Committed Capital', 'nuventures'); ?></span>
        </li>
    </ul>
</section>
