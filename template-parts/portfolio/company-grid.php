<?php
/**
 * Portfolio company search and grid.
 *
 * @package NuVentures
 */

$resolve_image = isset($args['resolve_image']) && is_callable($args['resolve_image']) ? $args['resolve_image'] : null;

if (!$resolve_image) {
    return;
}

$portfolio_query = new WP_Query(
    array(
        'post_type'              => 'company',
        'post_status'            => 'publish',
        'posts_per_page'         => -1,
        'orderby'                => 'title',
        'order'                  => 'ASC',
        'no_found_rows'          => true,
        'ignore_sticky_posts'    => true,
        'update_post_term_cache' => false,
    )
);

$companies = array();
$city_options = array();
$stage_options = array();

$normalize_filter_value = static function ($value) {
    $normalized = html_entity_decode(wp_strip_all_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $normalized = preg_replace('/[\p{Z}\s]+/u', ' ', $normalized);
    $normalized = trim((string) $normalized);

    return function_exists('mb_strtolower') ? mb_strtolower($normalized) : strtolower($normalized);
};

while ($portfolio_query->have_posts()) {
    $portfolio_query->the_post();
    $company_id   = get_the_ID();
    $company_name = get_field('company_name', $company_id);
    $name         = is_string($company_name) && trim($company_name) ? trim($company_name) : get_the_title();
    $description  = get_field('description', $company_id);
    $logo         = $resolve_image(get_field('logo', $company_id), $name . ' logo');
    $website      = get_field('website', $company_id);
    $city         = get_field('city', $company_id);
    $city_field   = get_field_object('city', $company_id);

    // Keep `city` canonical, while supporting company records saved under the
    // field's earlier `country` key before the ACF field name was corrected.
    if (empty($city)) {
        $city = get_field('country', $company_id);
    }
    $stage_raw    = get_field('stage', $company_id);
    $stage_field  = get_field_object('stage', $company_id);
    $stage_choices = is_array($stage_field) && isset($stage_field['choices']) && is_array($stage_field['choices'])
        ? $stage_field['choices']
        : array();
    $stages = array();
    $cities = array();

    if (!$logo['id'] && !$logo['url']) {
        continue;
    }

    if (is_array($website)) {
        $website = isset($website['url']) ? $website['url'] : '';
    }

    $city_choices = is_array($city_field) && isset($city_field['choices']) && is_array($city_field['choices'])
        ? $city_field['choices']
        : array();
    $add_city = static function ($value, $label = '') use (&$cities, $city_choices, $normalize_filter_value) {
        if (!is_scalar($value) || '' === trim((string) $value)) {
            return;
        }

        $city_value = trim((string) $value);
        $city_label = is_scalar($label) && '' !== trim((string) $label)
            ? trim((string) $label)
            : $city_value;

        if (isset($city_choices[$city_value])) {
            $city_label = (string) $city_choices[$city_value];
        } else {
            $matching_value = array_search($city_value, $city_choices, true);

            if (false !== $matching_value) {
                $city_value = (string) $matching_value;
                $city_label = (string) $city_choices[$matching_value];
            }
        }

        $normalized_city = $normalize_filter_value($city_value);

        if ($normalized_city) {
            $cities[$normalized_city] = $city_label;
        }
    };

    if (is_array($city) && (isset($city['value']) || isset($city['label']))) {
        $add_city(
            isset($city['value']) ? $city['value'] : $city['label'],
            isset($city['label']) ? $city['label'] : ''
        );
    } elseif (is_array($city)) {
        foreach ($city as $city_key => $city_item) {
            if (is_array($city_item)) {
                $add_city(
                    isset($city_item['value']) ? $city_item['value'] : (isset($city_item['label']) ? $city_item['label'] : ''),
                    isset($city_item['label']) ? $city_item['label'] : ''
                );
            } elseif (!is_int($city_key) && isset($city_choices[$city_key])) {
                $add_city($city_key, $city_item);
            } else {
                $add_city($city_item);
            }
        }
    } elseif (is_scalar($city)) {
        $add_city($city);
    }

    $add_stage = static function ($value, $label = '') use (&$stages, $stage_choices) {
        if (!is_scalar($value) || '' === trim((string) $value)) {
            return;
        }

        $stage_value = trim((string) $value);
        $stage_label = is_scalar($label) && '' !== trim((string) $label)
            ? trim((string) $label)
            : $stage_value;

        if (isset($stage_choices[$stage_value])) {
            $stage_label = (string) $stage_choices[$stage_value];
        } else {
            $matching_value = array_search($stage_value, $stage_choices, true);

            if (false !== $matching_value) {
                $stage_value = (string) $matching_value;
                $stage_label = (string) $stage_choices[$matching_value];
            }
        }
        $stages[$stage_value] = array(
            'value' => $stage_value,
            'label' => $stage_label,
        );
    };

    if (is_array($stage_raw) && (isset($stage_raw['value']) || isset($stage_raw['label']))) {
        $add_stage(
            isset($stage_raw['value']) ? $stage_raw['value'] : $stage_raw['label'],
            isset($stage_raw['label']) ? $stage_raw['label'] : ''
        );
    } elseif (is_array($stage_raw)) {
        foreach ($stage_raw as $stage_key => $stage_item) {
            if (is_array($stage_item)) {
                $add_stage(
                    isset($stage_item['value']) ? $stage_item['value'] : (isset($stage_item['label']) ? $stage_item['label'] : ''),
                    isset($stage_item['label']) ? $stage_item['label'] : ''
                );
            } elseif (!is_int($stage_key) && isset($stage_choices[$stage_key])) {
                $add_stage($stage_key, $stage_item);
            } else {
                $add_stage($stage_item);
            }
        }
    } elseif (is_scalar($stage_raw)) {
        $add_stage($stage_raw);
    }

    $normalized_name   = $normalize_filter_value($name);
    $normalized_cities = array_keys($cities);
    $normalized_stages = array();
    $stage_badges      = array();

    foreach ($cities as $normalized_city => $city_label) {
        $city_options[$normalized_city] = $city_label;
    }

    foreach ($stages as $stage) {
        $normalized_stage       = $normalize_filter_value($stage['value']);
        $normalized_stage_label = $normalize_filter_value($stage['label']);

        if (!$normalized_stage) {
            continue;
        }

        $normalized_stages[] = $normalized_stage;

        if ('none' !== $normalized_stage && 'none' !== $normalized_stage_label) {
            $stage_options[$normalized_stage] = $stage['label'] ? $stage['label'] : $stage['value'];
        }

        $badge_type = in_array($normalized_stage_label, array('unicorn', 'soonicorn', 'exited'), true)
            ? $normalized_stage_label
            : $normalized_stage;

        if (in_array($badge_type, array('unicorn', 'soonicorn', 'exited'), true)) {
            $stage_badges[$badge_type] = array(
                'type'  => $badge_type,
                'label' => $stage['label'] ? $stage['label'] : $stage['value'],
            );
        }
    }

    $companies[] = array(
        'id'               => $company_id,
        'name'             => $name,
        'description'      => is_scalar($description) ? trim(wp_strip_all_tags((string) $description)) : '',
        'normalized_name'  => $normalized_name,
        'cities'            => array_values($cities),
        'normalized_cities' => $normalized_cities,
        'normalized_stages' => array_values(array_unique($normalized_stages)),
        'stage_badges'      => array_values($stage_badges),
        'logo'             => $logo,
        'website'          => is_string($website) ? $website : '',
    );
}

wp_reset_postdata();

uasort($city_options, 'strnatcasecmp');
uasort($stage_options, 'strnatcasecmp');

$asset_path = get_template_directory_uri() . '/assets/images/portfolio/';
?>

<section class="portfolio-companies" data-portfolio-companies aria-labelledby="portfolio-companies-title">
    <h2 class="portfolio-page__heading" id="portfolio-companies-title"><?php esc_html_e('Companies shaping what comes next', 'nuventures'); ?></h2>

    <div class="portfolio-companies__toolbar">
        <label class="portfolio-companies__search">
            <span class="screen-reader-text"><?php esc_html_e('Search for a company', 'nuventures'); ?></span>
            <img src="<?php echo esc_url($asset_path . 'search.svg'); ?>" alt="" width="24" height="24">
            <input type="search" placeholder="<?php esc_attr_e('Search for a company', 'nuventures'); ?>" data-portfolio-search>
        </label>

        <label class="portfolio-companies__filter">
            <span class="screen-reader-text"><?php esc_html_e('Filter by Stage/Series', 'nuventures'); ?></span>
            <select data-portfolio-stage>
                <option value=""><?php esc_html_e('All Stages', 'nuventures'); ?></option>
                <?php foreach ($stage_options as $stage_option_value => $stage_option_label) : ?>
                    <option value="<?php echo esc_attr($stage_option_value); ?>"><?php echo esc_html($stage_option_label); ?></option>
                <?php endforeach; ?>
            </select>
            <img src="<?php echo esc_url($asset_path . 'chevron-down.svg'); ?>" alt="" width="24" height="24">
        </label>

        <label class="portfolio-companies__filter">
            <span class="screen-reader-text"><?php esc_html_e('Filter by Location', 'nuventures'); ?></span>
            <select data-portfolio-city>
                <option value=""><?php esc_html_e('All Locations', 'nuventures'); ?></option>
                <?php foreach ($city_options as $city_option_value => $city_option_label) : ?>
                    <option value="<?php echo esc_attr($city_option_value); ?>"><?php echo esc_html($city_option_label); ?></option>
                <?php endforeach; ?>
            </select>
            <img src="<?php echo esc_url($asset_path . 'chevron-down.svg'); ?>" alt="" width="24" height="24">
        </label>

        <label class="portfolio-companies__sort">
            <span class="screen-reader-text"><?php esc_html_e('Sort companies', 'nuventures'); ?></span>
            <select data-portfolio-sort>
                <option value="asc"><?php esc_html_e('Sort (A-Z)', 'nuventures'); ?></option>
                <option value="desc"><?php esc_html_e('Sort (Z-A)', 'nuventures'); ?></option>
            </select>
            <img src="<?php echo esc_url($asset_path . 'chevron-down.svg'); ?>" alt="" width="24" height="24">
        </label>
    </div>

    <p class="portfolio-companies__count" aria-live="polite">
        <strong data-portfolio-count><?php echo esc_html(count($companies)); ?></strong>
        <?php esc_html_e('Companies Found.', 'nuventures'); ?>
    </p>

    <div class="portfolio-companies__grid" data-portfolio-grid>
        <?php foreach ($companies as $company) : ?>
            <?php $description_id = 'portfolio-company-description-' . $company['id']; ?>
            <?php $tile_class = 'portfolio-companies__tile' . (($company['website'] || $company['description']) ? ' portfolio-companies__tile--interactive' : ''); ?>
            <div
                class="portfolio-companies__item"
                data-company-name="<?php echo esc_attr($company['normalized_name']); ?>"
                data-company-cities="<?php echo esc_attr(wp_json_encode($company['normalized_cities'])); ?>"
                data-company-stages="<?php echo esc_attr(wp_json_encode($company['normalized_stages'])); ?>"
            >
                <?php if ($company['website']) : ?>
                    <a class="<?php echo esc_attr($tile_class); ?>" href="<?php echo esc_url($company['website']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($company['name']); ?>">
                <?php else : ?>
                    <div class="<?php echo esc_attr($tile_class); ?>" role="img" aria-label="<?php echo esc_attr($company['name']); ?>">
                <?php endif; ?>

                    <?php if ($company['stage_badges']) : ?>
                        <span class="portfolio-companies__stage-badges">
                            <?php foreach ($company['stage_badges'] as $stage_badge) : ?>
                                <span class="portfolio-companies__stage-badge portfolio-companies__stage-badge--<?php echo esc_attr($stage_badge['type']); ?>">
                                    <img
                                        src="<?php echo esc_url($asset_path . $stage_badge['type'] . '.png'); ?>"
                                        alt=""
                                        width="14"
                                        height="14"
                                        aria-hidden="true"
                                    >
                                    <?php echo esc_html($stage_badge['label']); ?>
                                </span>
                            <?php endforeach; ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($company['logo']['id']) : ?>
                        <?php
                        echo wp_get_attachment_image(
                            $company['logo']['id'],
                            'medium',
                            false,
                            array(
                                'class'    => 'portfolio-companies__logo',
                                'alt'      => $company['logo']['alt'],
                                'loading'  => 'lazy',
                                'decoding' => 'async',
                                'sizes'    => '(max-width: 550px) 40vw, 166px',
                            )
                        );
                        ?>
                    <?php else : ?>
                        <img class="portfolio-companies__logo" src="<?php echo esc_url($company['logo']['url']); ?>" alt="<?php echo esc_attr($company['logo']['alt']); ?>" loading="lazy" decoding="async">
                    <?php endif; ?>

                    <?php if ($company['description']) : ?>
                        <span class="portfolio-companies__description" id="<?php echo esc_attr($description_id); ?>">
                            <?php echo esc_html($company['description']); ?>
                        </span>
                    <?php endif; ?>

                <?php if ($company['website']) : ?>
                    </a>
                <?php else : ?>
                    </div>
                <?php endif; ?>

                <?php if ($company['description']) : ?>
                    <button
                        class="portfolio-companies__info"
                        type="button"
                        aria-label="<?php echo esc_attr(sprintf(__('Show information about %s', 'nuventures'), $company['name'])); ?>"
                        aria-controls="<?php echo esc_attr($description_id); ?>"
                        aria-expanded="false"
                        data-company-info
                    >
                        <span aria-hidden="true">i</span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="portfolio-companies__empty" data-portfolio-empty hidden><?php esc_html_e('No companies found.', 'nuventures'); ?></p>
</section>
