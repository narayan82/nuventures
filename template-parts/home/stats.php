<?php
/**
 * Homepage company statistics.
 *
 * @package NuVentures
 */
?>

<section class="home-stats" data-stats aria-label="<?php esc_attr_e('NuVentures statistics', 'nuventures'); ?>">
    <ul class="home-stats__grid">
        <li class="home-stats__item" aria-label="<?php esc_attr_e('52+ Investments till date', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="52" data-stat-suffix="+" aria-hidden="true">52</span>
            <span class="home-stats__label"><?php esc_html_e('Investments till date', 'nuventures'); ?></span>
        </li>
        <li class="home-stats__item" aria-label="<?php esc_attr_e('2 Continents', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="2" aria-hidden="true">2</span>
            <span class="home-stats__label"><?php esc_html_e('Continents', 'nuventures'); ?></span>
        </li>
        <li class="home-stats__item" aria-label="<?php esc_attr_e('6 Partners', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="4" data-stat-suffix="+" aria-hidden="true">4</span>
            <span class="home-stats__label"><?php esc_html_e('Unicorns', 'nuventures'); ?></span>
        </li>
        <li class="home-stats__item" aria-label="<?php esc_attr_e('75M dollars Committed Capital', 'nuventures'); ?>">
            <span class="home-stats__value" data-stat-value="75" data-stat-prefix="$" data-stat-suffix="M" aria-hidden="true">$75M</span>
            <span class="home-stats__label"><?php esc_html_e('Committed Capital', 'nuventures'); ?></span>
        </li>
    </ul>
</section>
