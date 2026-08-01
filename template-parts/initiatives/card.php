<?php
/**
 * Initiative listing card.
 *
 * @package NuVentures
 */

$initiative = isset($args['initiative']) && is_array($args['initiative']) ? $args['initiative'] : array();
$post       = isset($initiative['post']) && $initiative['post'] instanceof WP_Post ? $initiative['post'] : null;

if (!$post) {
    return;
}

$post_id       = $post->ID;
$title         = get_the_title($post);
$featured_image = isset($initiative['featured_image']) ? $initiative['featured_image'] : null;
$categories    = isset($initiative['categories']) && is_array($initiative['categories']) ? $initiative['categories'] : array();
$first_category = $categories ? reset($categories) : null;
$category_slugs = wp_list_pluck($categories, 'slug');
$image_id       = 0;
$image_url      = '';
$image_alt      = '';

if (is_numeric($featured_image)) {
    $image_id = (int) $featured_image;
} elseif (is_array($featured_image)) {
    $image_id  = isset($featured_image['ID']) ? (int) $featured_image['ID'] : (isset($featured_image['id']) ? (int) $featured_image['id'] : 0);
    $image_url = isset($featured_image['url']) ? $featured_image['url'] : '';
    $image_alt = isset($featured_image['alt']) ? $featured_image['alt'] : '';
} elseif (is_string($featured_image)) {
    $image_url = $featured_image;
}

$has_image = (bool) ($image_id || $image_url);
?>

<article
    class="initiative-card<?php echo $has_image ? ' initiative-card--image' : ' initiative-card--text'; ?>"
    data-initiative-card
    data-title="<?php echo esc_attr(wp_strip_all_tags($title)); ?>"
    data-date="<?php echo esc_attr(get_the_date('Y-m-d', $post)); ?>"
    data-categories="<?php echo esc_attr(implode(' ', array_map('sanitize_title', $category_slugs))); ?>"
>
    <a class="initiative-card__link" href="<?php echo esc_url(get_permalink($post)); ?>">
        <span class="initiative-card__content">
            <time class="initiative-card__date" datetime="<?php echo esc_attr(get_the_date('c', $post)); ?>">
                <?php echo esc_html(get_the_date('j-M-Y', $post)); ?>
            </time>

            <span class="initiative-card__title"><?php echo esc_html($title); ?></span>

            <span class="initiative-card__arrow" aria-hidden="true">
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nu-journey/arrow-dark.svg'); ?>"
                    alt=""
                    width="24"
                    height="24"
                >
            </span>

            <?php if ($first_category instanceof WP_Term) : ?>
                <span class="initiative-card__category"><?php echo esc_html($first_category->name); ?></span>
            <?php endif; ?>
        </span>

        <?php if ($has_image) : ?>
            <span class="initiative-card__media">
                <?php if ($image_id) : ?>
                    <?php
                    echo wp_get_attachment_image(
                        $image_id,
                        'large',
                        false,
                        array(
                            'class'    => 'initiative-card__image',
                            'alt'      => $image_alt ? $image_alt : $title,
                            'loading'  => 'lazy',
                            'decoding' => 'async',
                            'sizes'    => '(max-width: 550px) calc(100vw - 40px), (max-width: 900px) calc(100vw - 40px), 285px',
                        )
                    );
                    ?>
                <?php else : ?>
                    <img
                        class="initiative-card__image"
                        src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($image_alt ? $image_alt : $title); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                <?php endif; ?>
            </span>
        <?php endif; ?>
    </a>
</article>
