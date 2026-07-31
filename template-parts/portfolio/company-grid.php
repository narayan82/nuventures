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
    $normalized = trim(wp_strip_all_tags((string) $value));

    return function_exists('mb_strtolower') ? mb_strtolower($normalized) : strtolower($normalized);
};

while ($portfolio_query->have_posts()) {
    $portfolio_query->the_post();
    $company_id   = get_the_ID();
    $company_name = get_field('company_name', $company_id);
    $name         = is_string($company_name) && trim($company_name) ? trim($company_name) : get_the_title();
    $logo         = $resolve_image(get_field('logo', $company_id), $name . ' logo');
    $website      = get_field('website', $company_id);
    $city         = get_field('city', $company_id);
    $stage_raw    = get_field('stage', $company_id);
    $stage_field  = get_field_object('stage', $company_id);
    $stage_value  = '';
    $stage_label  = '';

    if (!$logo['id'] && !$logo['url']) {
        continue;
    }

    if (is_array($website)) {
        $website = isset($website['url']) ? $website['url'] : '';
    }

    if (is_array($stage_raw)) {
        $stage_value = isset($stage_raw['value']) ? $stage_raw['value'] : '';
        $stage_label = isset($stage_raw['label']) ? $stage_raw['label'] : $stage_value;
    } elseif (is_scalar($stage_raw) && '' !== trim((string) $stage_raw)) {
        $stage_value = trim((string) $stage_raw);
        $stage_label = $stage_value;
        $choices     = is_array($stage_field) && isset($stage_field['choices']) && is_array($stage_field['choices']) ? $stage_field['choices'] : array();

        if (isset($choices[$stage_value])) {
            $stage_label = $choices[$stage_value];
        } else {
            $matching_value = array_search($stage_value, $choices, true);

            if (false !== $matching_value) {
                $stage_value = $matching_value;
                $stage_label = $choices[$matching_value];
            }
        }
    }

    $city_label       = is_scalar($city) ? trim((string) $city) : '';
    $normalized_name  = $normalize_filter_value($name);
    $normalized_city  = $normalize_filter_value($city_label);
    $normalized_stage = $normalize_filter_value($stage_value);

    if ($normalized_city) {
        $city_options[$normalized_city] = $city_label;
    }

    if ($normalized_stage) {
        $stage_options[$normalized_stage] = $stage_label ? $stage_label : $stage_value;
    }

    $companies[] = array(
        'name'             => $name,
        'normalized_name'  => $normalized_name,
        'city'             => $city_label,
        'normalized_city'  => $normalized_city,
        'stage_value'      => $stage_value,
        'stage_label'      => $stage_label,
        'normalized_stage' => $normalized_stage,
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
                <option value=""><?php esc_html_e('Filter by Stage/Series', 'nuventures'); ?></option>
                <?php foreach ($stage_options as $stage_option_value => $stage_option_label) : ?>
                    <option value="<?php echo esc_attr($stage_option_value); ?>"><?php echo esc_html($stage_option_label); ?></option>
                <?php endforeach; ?>
            </select>
            <img src="<?php echo esc_url($asset_path . 'chevron-down.svg'); ?>" alt="" width="24" height="24">
        </label>

        <label class="portfolio-companies__filter">
            <span class="screen-reader-text"><?php esc_html_e('Filter by Location', 'nuventures'); ?></span>
            <select data-portfolio-city>
                <option value=""><?php esc_html_e('Filter by Location', 'nuventures'); ?></option>
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
            <div
                class="portfolio-companies__item"
                data-company-name="<?php echo esc_attr($company['normalized_name']); ?>"
                data-company-city="<?php echo esc_attr($company['normalized_city']); ?>"
                data-company-stage="<?php echo esc_attr($company['normalized_stage']); ?>"
            >
                <?php if ($company['website']) : ?>
                    <a class="portfolio-companies__tile" href="<?php echo esc_url($company['website']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($company['name']); ?>">
                <?php else : ?>
                    <div class="portfolio-companies__tile" role="img" aria-label="<?php echo esc_attr($company['name']); ?>">
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

                <?php if ($company['website']) : ?>
                    </a>
                <?php else : ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p class="portfolio-companies__empty" data-portfolio-empty hidden><?php esc_html_e('No companies found.', 'nuventures'); ?></p>
</section>
