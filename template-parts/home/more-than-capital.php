<?php
/**
 * Interactive homepage compass section.
 *
 * @package NuVentures
 */

$compass_states = array(
    array(
        'key'         => 'mentorship',
        'eyebrow'     => 'Sharpen the Idea',
        'label'       => 'Personalised Mentorship',
        'description' => 'Practical advice and strategic guidance from experienced investors and industry leaders.',
    ),
    array(
        'key'         => 'gtm',
        'eyebrow'     => 'Validate in the real world',
        'label'       => 'Guided PoC & GTM',
        'description' => 'Practical advice and strategic guidance from experienced investors and industry leaders.',
    ),
    array(
        'key'         => 'community',
        'eyebrow'     => 'Learn and grow together',
        'label'       => 'Founder Community',
        'description' => 'A collaborative network for sharing challenges, insights and opportunities.',
    ),
    array(
        'key'         => 'network',
        'eyebrow'     => 'Open the right doors',
        'label'       => 'Strategic Network',
        'description' => 'Connections to investors, industry leaders, partners and potential customers.',
    ),
);

$asset_path = get_template_directory_uri() . '/assets/images/more-than-capital/';
?>

<section class="more-than-capital" data-more-than-capital aria-labelledby="more-than-capital-title">
    <h2 class="more-than-capital__title" id="more-than-capital-title">
        <strong><?php esc_html_e('More than capital.', 'nuventures'); ?></strong>
        <?php esc_html_e('How we help companies navigate', 'nuventures'); ?>
    </h2>

    <div class="more-than-capital__visual">
        <div class="more-than-capital__controls" role="tablist" aria-label="<?php esc_attr_e('How NuVentures supports companies', 'nuventures'); ?>">
            <?php foreach ($compass_states as $index => $state) : ?>
                <div class="more-than-capital__position more-than-capital__position--<?php echo esc_attr($state['key']); ?><?php echo 0 === $index ? ' is-active' : ''; ?>">
                    <span class="more-than-capital__eyebrow"><?php echo esc_html($state['eyebrow']); ?></span>
                    <button
                        class="more-than-capital__tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
                        id="compass-tab-<?php echo esc_attr($state['key']); ?>"
                        type="button"
                        role="tab"
                        aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
                        aria-controls="compass-panel-<?php echo esc_attr($state['key']); ?>"
                        tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
                        data-compass-state="<?php echo esc_attr($index); ?>"
                    >
                        <?php echo esc_html($state['label']); ?>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="more-than-capital__compass" aria-hidden="true">
            <img
                class="more-than-capital__markings"
                src="<?php echo esc_url($asset_path . 'markings.svg'); ?>"
                alt=""
                width="261"
                height="263"
            >
            <div class="more-than-capital__rotor" data-compass-rotor>
                <img
                    class="more-than-capital__compass-background"
                    src="<?php echo esc_url($asset_path . 'compass_bg.svg'); ?>"
                    alt=""
                    width="1050"
                    height="1050"
                >
                <img
                    class="more-than-capital__face"
                    src="<?php echo esc_url($asset_path . 'compass-face.webp'); ?>"
                    alt=""
                    width="266"
                    height="266"
                >
                <span class="more-than-capital__needle-crop">
                    <img
                        class="more-than-capital__needle"
                        src="<?php echo esc_url($asset_path . 'compass-needle.webp'); ?>"
                        alt=""
                        width="169"
                        height="171"
                    >
                </span>
            </div>
        </div>

        <div class="more-than-capital__descriptions" aria-live="polite">
            <?php foreach ($compass_states as $index => $state) : ?>
                <div
                    class="more-than-capital__description more-than-capital__description--<?php echo esc_attr($state['key']); ?><?php echo 0 === $index ? ' is-active' : ''; ?>"
                    id="compass-panel-<?php echo esc_attr($state['key']); ?>"
                    role="tabpanel"
                    aria-labelledby="compass-tab-<?php echo esc_attr($state['key']); ?>"
                    aria-hidden="<?php echo 0 === $index ? 'false' : 'true'; ?>"
                    data-compass-panel="<?php echo esc_attr($index); ?>"
                >
                    <?php echo esc_html($state['description']); ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="more-than-capital__mobile-nav">
            <button class="more-than-capital__mobile-control more-than-capital__mobile-control--previous" type="button" aria-label="<?php esc_attr_e('Previous compass state', 'nuventures'); ?>" data-compass-previous>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
            </button>
            <button class="more-than-capital__mobile-control" type="button" aria-label="<?php esc_attr_e('Next compass state', 'nuventures'); ?>" data-compass-next>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/investors/chevron-right.svg'); ?>" alt="" width="8" height="14">
            </button>
        </div>
    </div>

    <a
        class="more-than-capital__cta"
        href="<?php echo esc_url(home_url('/pitch/')); ?>"
        aria-haspopup="dialog"
        data-pitch-trigger
    >
        <?php esc_html_e('Start Your Pitch', 'nuventures'); ?>
    </a>
</section>
