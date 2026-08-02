<?php
/**
 * Related Person articles.
 *
 * @package NuVentures
 */

$articles = isset($args['articles']) && is_array($args['articles']) ? $args['articles'] : array();
if (!$articles) {
    return;
}
?>
<section class="person-related person-related--articles" aria-labelledby="person-articles-title">
    <h2 id="person-articles-title"><?php esc_html_e('Articles', 'nuventures'); ?></h2>
    <div class="person-related__grid">
        <?php foreach ($articles as $article) : ?>
            <article class="person-article-card">
                <a href="<?php echo esc_url(get_permalink($article)); ?>">
                    <time datetime="<?php echo esc_attr(get_the_date('c', $article)); ?>">
                        <?php echo esc_html(get_the_date('j-M-Y', $article)); ?>
                    </time>
                    <span class="person-article-card__title"><?php echo esc_html(get_the_title($article)); ?></span>
                    <img
                        class="person-article-card__arrow"
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nu-journey/arrow-dark.svg'); ?>"
                        alt=""
                        width="24"
                        height="24"
                        aria-hidden="true"
                    >
                </a>
            </article>
        <?php endforeach; ?>
    </div>
</section>
