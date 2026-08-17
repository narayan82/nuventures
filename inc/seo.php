<?php
/**
 * Centralized SEO metadata and native sitemap controls.
 *
 * @package NuVentures
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Detect an active SEO suite that already owns metadata and schema output.
 *
 * @return bool
 */
function nuventures_has_seo_plugin() {
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('AIOSEO_VERSION')
        || defined('SEOPRESS_VERSION')
        || class_exists('RankMath')
        || class_exists('AIOSEO\\Plugin\\AIOSEO');
}

/**
 * Normalize ACF image return formats to an absolute URL.
 *
 * @param mixed $image ACF image value.
 * @return string
 */
function nuventures_seo_image_url($image) {
    if (is_numeric($image)) {
        return (string) wp_get_attachment_image_url(absint($image), 'full');
    }

    if (is_array($image)) {
        $image_id = !empty($image['ID']) ? absint($image['ID']) : (!empty($image['id']) ? absint($image['id']) : 0);

        if ($image_id) {
            return (string) wp_get_attachment_image_url($image_id, 'full');
        }

        return !empty($image['url']) ? esc_url_raw($image['url']) : '';
    }

    return is_string($image) ? esc_url_raw($image) : '';
}

/**
 * Convert rich text to a concise, sentence-safe metadata value.
 *
 * @param mixed $value  Source value.
 * @param int   $length Maximum characters.
 * @return string
 */
function nuventures_seo_trim($value, $length = 158) {
    if (!is_scalar($value)) {
        return '';
    }

    $text = trim(preg_replace('/\s+/u', ' ', wp_strip_all_tags(strip_shortcodes((string) $value))));

    if ('' === $text) {
        return '';
    }

    if (function_exists('mb_strlen') && mb_strlen($text) > $length) {
        $text = mb_substr($text, 0, $length - 1);
        $text = preg_replace('/\s+\S*$/u', '', $text);
        return rtrim($text, " \t\n\r\0\x0B,.;:-") . '…';
    }

    if (!function_exists('mb_strlen') && strlen($text) > $length) {
        $text = substr($text, 0, $length - 1);
        $text = preg_replace('/\s+\S*$/', '', $text);
        return rtrim($text, " \t\n\r\0\x0B,.;:-") . '…';
    }

    return $text;
}

/**
 * Read ACF safely when ACF is unavailable.
 *
 * @param string $key     Field name.
 * @param int    $post_id Post ID.
 * @return mixed
 */
function nuventures_seo_field($key, $post_id) {
    return function_exists('get_field') ? get_field($key, $post_id) : null;
}

/**
 * Return the page-specific meta description.
 *
 * @return string
 */
function nuventures_seo_description() {
    $post_id = get_queried_object_id();

    if (is_front_page()) {
        return nuventures_seo_trim('NuVentures backs bold founders with operator perspective, long-term conviction, capital, strategic guidance and trusted networks.');
    }

    if (is_page('about')) {
        return nuventures_seo_trim('NuVentures is an early-stage venture fund partnering with founders building what is next across New Jersey and Bengaluru.');
    }

    if (is_page('portfolio')) {
        return nuventures_seo_trim('Explore the founders, companies and NuJourney stories backed by NuVentures as they build technologies and businesses shaping what comes next.');
    }

    if (is_page('initiatives')) {
        return nuventures_seo_trim('Your front row seat to venture capital, innovation, tech and the startups shaping tomorrow.');
    }

    if (is_page('nupod')) {
        return nuventures_seo_trim('Listen to NuPOD conversations about venture capital, innovation, technology and the founders building what comes next.');
    }

    if (is_singular('person')) {
        $parts = array_filter(array(
            nuventures_seo_field('name', $post_id) ?: get_the_title($post_id),
            nuventures_seo_field('designation', $post_id),
            nuventures_seo_field('background_descr', $post_id),
        ));
        return nuventures_seo_trim(implode('. ', $parts));
    }

    if (is_singular('nu-journey')) {
        return nuventures_seo_trim(nuventures_seo_field('description', $post_id));
    }

    if (is_singular('podcast')) {
        return nuventures_seo_trim(
            nuventures_seo_field('podcast_description', $post_id)
                ?: nuventures_seo_field('podcast_title', $post_id)
                ?: get_the_title($post_id)
        );
    }

    if (is_singular('post')) {
        $excerpt = get_the_excerpt($post_id);
        return nuventures_seo_trim($excerpt ?: get_post_field('post_content', $post_id));
    }

    if (is_page()) {
        return nuventures_seo_trim(get_post_field('post_excerpt', $post_id) ?: get_post_field('post_content', $post_id));
    }

    return '';
}

/**
 * Prefer established ACF display titles on supported single content types.
 *
 * @param array $parts WordPress title parts.
 * @return array
 */
function nuventures_seo_document_title_parts($parts) {
    if (!is_singular(array('person', 'nu-journey', 'podcast'))) {
        return $parts;
    }

    $post_id = get_queried_object_id();
    $field   = is_singular('person') ? 'name' : (is_singular('nu-journey') ? 'long_title' : 'podcast_title');
    $title   = nuventures_seo_field($field, $post_id);

    if (is_scalar($title) && '' !== trim((string) $title)) {
        $parts['title'] = wp_strip_all_tags((string) $title);
    }

    return $parts;
}
add_filter('document_title_parts', 'nuventures_seo_document_title_parts');

/**
 * Resolve the best supported social image for the current request.
 *
 * @return string
 */
function nuventures_seo_social_image() {
    $post_id = get_queried_object_id();

    if (is_singular('person')) {
        return nuventures_seo_image_url(nuventures_seo_field('photo', $post_id));
    }

    if (is_singular('nu-journey')) {
        return nuventures_seo_image_url(nuventures_seo_field('main_photo', $post_id))
            ?: nuventures_seo_image_url(nuventures_seo_field('logo', $post_id));
    }

    if (is_singular('podcast')) {
        return nuventures_seo_image_url(nuventures_seo_field('podcast_image', $post_id));
    }

    if (is_singular('post') && has_post_thumbnail($post_id)) {
        return (string) get_the_post_thumbnail_url($post_id, 'full');
    }

    return '';
}

/**
 * Return the canonical current URL without replacing WordPress canonical tags.
 *
 * @return string
 */
function nuventures_seo_current_url() {
    if (is_front_page()) {
        return home_url('/');
    }

    if (is_singular()) {
        return (string) get_permalink(get_queried_object_id());
    }

    return '';
}

/**
 * Build supported JSON-LD graph entries.
 *
 * @param string $description Current meta description.
 * @param string $image       Current social image URL.
 * @return array
 */
function nuventures_seo_schema_graph($description, $image) {
    $site_url        = home_url('/');
    $organization_id = trailingslashit($site_url) . '#organization';
    $website_id      = trailingslashit($site_url) . '#website';
    $url             = nuventures_seo_current_url();
    $graph           = array(
        array(
            '@type' => 'Organization',
            '@id'   => $organization_id,
            'name'  => get_bloginfo('name') ?: 'NuVentures',
            'url'   => $site_url,
            'logo'  => get_template_directory_uri() . '/assets/images/header/nuventures-logo.svg',
            'sameAs' => array(
                'https://www.youtube.com/@nuventures',
                'https://x.com/NuVentures_in',
                'https://www.instagram.com/nuventures.vc/',
                'https://www.linkedin.com/company/nuventures',
            ),
        ),
        array(
            '@type'     => 'WebSite',
            '@id'       => $website_id,
            'url'       => $site_url,
            'name'      => get_bloginfo('name') ?: 'NuVentures',
            'publisher' => array('@id' => $organization_id),
        ),
    );

    if (!is_singular()) {
        return $graph;
    }

    $post_id = get_queried_object_id();

    if (is_singular('person')) {
        $same_as = array_filter(array(
            nuventures_seo_field('instagram', $post_id),
            nuventures_seo_field('linkedin', $post_id),
            nuventures_seo_field('x', $post_id),
            nuventures_seo_field('youtube', $post_id),
        ), 'is_string');
        $person = array(
            '@type'       => 'Person',
            '@id'         => $url . '#person',
            'name'        => nuventures_seo_field('name', $post_id) ?: get_the_title($post_id),
            'url'         => $url,
            'affiliation' => array('@id' => $organization_id),
        );
        $job_title = nuventures_seo_field('designation', $post_id);
        if ($job_title) {
            $person['jobTitle'] = wp_strip_all_tags((string) $job_title);
        }
        if ($image) {
            $person['image'] = $image;
        }
        if ($same_as) {
            $person['sameAs'] = array_values($same_as);
        }
        $graph[] = $person;
    } elseif (is_singular('post')) {
        $article = array(
            '@type'         => 'BlogPosting',
            '@id'           => $url . '#article',
            'headline'      => get_the_title($post_id),
            'url'           => $url,
            'datePublished' => get_the_date(DATE_W3C, $post_id),
            'dateModified'  => get_the_modified_date(DATE_W3C, $post_id),
            'publisher'     => array('@id' => $organization_id),
        );
        if ($description) {
            $article['description'] = $description;
        }
        if ($image) {
            $article['image'] = $image;
        }
        $graph[] = $article;
    } elseif (is_singular('podcast')) {
        $episode = array(
            '@type'       => 'PodcastEpisode',
            '@id'         => $url . '#podcast-episode',
            'name'        => nuventures_seo_field('podcast_title', $post_id) ?: get_the_title($post_id),
            'url'         => $url,
            'description' => $description,
        );
        $date = nuventures_seo_field('podcast_date', $post_id);
        if ($date) {
            $timestamp = strtotime((string) $date);
            if ($timestamp) {
                $episode['datePublished'] = gmdate(DATE_W3C, $timestamp);
            }
        }
        if ($image) {
            $episode['image'] = $image;
        }
        $graph[] = array_filter($episode);
    } elseif (is_singular('nu-journey')) {
        $work = array(
            '@type'     => 'CreativeWork',
            '@id'       => $url . '#creative-work',
            'name'      => nuventures_seo_field('long_title', $post_id) ?: get_the_title($post_id),
            'url'       => $url,
            'publisher' => array('@id' => $organization_id),
        );
        if ($description) {
            $work['description'] = $description;
        }
        if ($image) {
            $work['image'] = $image;
        }
        $graph[] = $work;
    }

    return $graph;
}

/**
 * Output centralized descriptions, social metadata and schema.
 */
function nuventures_seo_head() {
    if (nuventures_has_seo_plugin() || is_admin() || is_feed() || is_404() || is_search()) {
        return;
    }

    $description = nuventures_seo_description();
    $url         = nuventures_seo_current_url();

    if ('' === $description || '' === $url) {
        return;
    }

    $title = wp_get_document_title();
    $image = nuventures_seo_social_image();
    $type  = is_singular(array('post', 'nu-journey', 'podcast')) ? 'article' : 'website';
    ?>
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:type" content="<?php echo esc_attr($type); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name') ?: 'NuVentures'); ?>">
    <?php if ($image) : ?>
        <meta property="og:image" content="<?php echo esc_url($image); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="<?php echo $image ? 'summary_large_image' : 'summary'; ?>">
    <meta name="twitter:title" content="<?php echo esc_attr($title); ?>">
    <meta name="twitter:description" content="<?php echo esc_attr($description); ?>">
    <?php if ($image) : ?>
        <meta name="twitter:image" content="<?php echo esc_url($image); ?>">
    <?php endif; ?>
    <script type="application/ld+json"><?php echo wp_json_encode(array('@context' => 'https://schema.org', '@graph' => nuventures_seo_schema_graph($description, $image)), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <?php
}
add_action('wp_head', 'nuventures_seo_head', 5);

/**
 * Keep workflow and placeholder CPT singles out of search results.
 *
 * @param array $robots WordPress robots directives.
 * @return array
 */
function nuventures_seo_robots($robots) {
    if (is_page('pitch') || is_singular(array('company', 'podcast'))) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
        unset($robots['index'], $robots['nofollow']);
    }

    return $robots;
}
add_filter('wp_robots', 'nuventures_seo_robots');

/**
 * Exclude internal or currently unsupported post-type singles from core sitemap.
 *
 * @param array $post_types Public sitemap post types.
 * @return array
 */
function nuventures_seo_sitemap_post_types($post_types) {
    foreach (array('testimonial', 'company', 'podcast') as $post_type) {
        unset($post_types[$post_type]);
    }

    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'nuventures_seo_sitemap_post_types');

/**
 * Exclude taxonomies without intentionally designed public archive pages.
 *
 * @param array $taxonomies Public sitemap taxonomies.
 * @return array
 */
function nuventures_seo_sitemap_taxonomies($taxonomies) {
    foreach (array('intiative-category', 'initiative-category', 'partner-interest', 'person-category') as $taxonomy) {
        unset($taxonomies[$taxonomy]);
    }

    return $taxonomies;
}
add_filter('wp_sitemaps_taxonomies', 'nuventures_seo_sitemap_taxonomies');

/**
 * Remove public author/user provider from the native sitemap index.
 *
 * @param WP_Sitemaps_Provider|false $provider Provider instance.
 * @param string                     $name     Provider name.
 * @return WP_Sitemaps_Provider|false
 */
function nuventures_seo_sitemap_provider($provider, $name) {
    return 'users' === $name ? false : $provider;
}
add_filter('wp_sitemaps_add_provider', 'nuventures_seo_sitemap_provider', 10, 2);
