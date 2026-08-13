<?php
/**
 * Nu Journey chronological timeline.
 *
 * @package NuVentures
 */

$journey_id    = isset($args['journey_id']) ? (int) $args['journey_id'] : get_the_ID();
$resolve_image = isset($args['resolve_image']) && is_callable($args['resolve_image']) ? $args['resolve_image'] : null;
$timeline_rows = get_field('timeline', $journey_id);

if (!is_array($timeline_rows) || !$timeline_rows) {
    return;
}

$extract_date_parts = static function ($date_value) {
    if ($date_value instanceof DateTimeInterface) {
        return array(
            'year'  => (int) $date_value->format('Y'),
            'month' => (int) $date_value->format('n'),
            'day'   => (int) $date_value->format('j'),
        );
    }

    if (is_array($date_value)) {
        $date_value = isset($date_value['date']) ? $date_value['date'] : reset($date_value);
    }

    $date_value = trim((string) $date_value);

    foreach (array('!Ymd', '!Y-m-d', '!d/m/Y', '!d-m-Y', '!m/d/Y') as $format) {
        $date = DateTimeImmutable::createFromFormat($format, $date_value);
        $errors = DateTimeImmutable::getLastErrors();

        if ($date && (false === $errors || (0 === $errors['warning_count'] && 0 === $errors['error_count']))) {
            return array(
                'year'  => (int) $date->format('Y'),
                'month' => (int) $date->format('n'),
                'day'   => (int) $date->format('j'),
            );
        }
    }

    $timestamp = strtotime($date_value);

    if ($timestamp) {
        return array(
            'year'  => (int) gmdate('Y', $timestamp),
            'month' => (int) gmdate('n', $timestamp),
            'day'   => (int) gmdate('j', $timestamp),
        );
    }

    return array(
        'year'  => 0,
        'month' => 0,
        'day'   => 0,
    );
};

$is_highlighted = static function ($value) {
    if (is_bool($value)) {
        return $value;
    }

    return in_array(strtolower(trim((string) $value)), array('1', 'yes', 'true', 'on'), true);
};

$entries = array();

foreach ($timeline_rows as $row_index => $row) {
    $start_date = $extract_date_parts(isset($row['date']) ? $row['date'] : '');
    $end_date   = $extract_date_parts(isset($row['end_date']) ? $row['end_date'] : '');
    $year       = $start_date['year'];
    $month      = $start_date['month'];
    $end_year   = $end_date['year'];
    $end_month  = $end_date['month'];
    $title      = isset($row['timeline_title']) ? trim((string) $row['timeline_title']) : '';

    if (!$year || !$title) {
        continue;
    }

    $start_month_index = ($year * 12) + ($month - 1);
    $end_month_index   = ($end_year * 12) + ($end_month - 1);

    if (!$end_year || $end_month_index < $start_month_index) {
        $end_year = $year;
        $end_month = $month;
    }

    $entries[] = array(
        'year'      => $year,
        'month'     => $month,
        'end_year'  => $end_year,
        'end_month' => $end_month,
        'date_label' => wp_date('d-F-Y', gmmktime(0, 0, 0, $month, $start_date['day'], $year)),
        'title'     => $title,
        'big_title' => isset($row['timeline_big_title']) ? trim((string) $row['timeline_big_title']) : '',
        'description' => isset($row['timeline_description']) ? (string) $row['timeline_description'] : '',
        'image'     => $resolve_image ? $resolve_image(isset($row['timeline_image']) ? $row['timeline_image'] : '', $title) : array(),
        'highlight' => $is_highlighted(isset($row['highlight']) ? $row['highlight'] : false),
        'order'     => $row_index,
    );
}

if (!$entries) {
    return;
}

usort(
    $entries,
    static function ($first, $second) {
        $first_month  = ($first['year'] * 12) + $first['month'];
        $second_month = ($second['year'] * 12) + $second['month'];

        return $first_month === $second_month
            ? $first['order'] <=> $second['order']
            : $first_month <=> $second_month;
    }
);

$start_years   = wp_list_pluck($entries, 'year');
$end_years     = wp_list_pluck($entries, 'end_year');
$earliest_year = min($start_years);
$latest_year   = max($end_years);
$year_count    = max(1, ($latest_year - $earliest_year) + 1);
$year_spacing  = 140;
$edge_padding  = 0;
$canvas_width  = max(420, (($year_count - 1) * $year_spacing) + ($edge_padding * 2));
$asset_path    = get_template_directory_uri() . '/assets/images/nu-journey/';
$month_initials = array('J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D');
$last_month_index = max(
    array_map(
        static function ($entry) use ($earliest_year) {
            return (($entry['end_year'] - $earliest_year) * 12) + ($entry['end_month'] - 1);
        },
        $entries
    )
);
?>

<section
    class="nu-journey-timeline"
    data-nu-journey-timeline
    data-earliest-year="<?php echo esc_attr($earliest_year); ?>"
    data-latest-year="<?php echo esc_attr($latest_year); ?>"
    aria-label="<?php esc_attr_e('Journey timeline', 'nuventures'); ?>"
>


    <div class="nu-journey-timeline__sticky-years" data-timeline-sticky-years aria-hidden="true">
        <div
            class="nu-journey-timeline__years"
            data-timeline-years
            style="--timeline-width: <?php echo esc_attr($canvas_width); ?>px; --timeline-canvas-width: <?php echo esc_attr($canvas_width); ?>px; --timeline-year-width: <?php echo esc_attr($year_spacing); ?>px; --timeline-edge-padding: <?php echo esc_attr($edge_padding); ?>px;"
        >
            <?php for ($year = $earliest_year; $year <= $latest_year; $year++) : ?>
                <span class="nu-journey-timeline__year-label" style="--year-index: <?php echo esc_attr($year - $earliest_year); ?>;"><?php echo esc_html($year); ?></span>
            <?php endfor; ?>

            <?php for ($month_index = 0; $month_index <= $last_month_index; $month_index++) : ?>
                <span
                    class="nu-journey-timeline__month-label"
                    data-timeline-month-label
                    data-month-index="<?php echo esc_attr($month_index); ?>"
                ><?php echo esc_html($month_initials[$month_index % 12]); ?></span>
            <?php endfor; ?>
        </div>
    </div>

    <div class="nu-journey-timeline__viewport" data-timeline-viewport tabindex="0">
        <div
            class="nu-journey-timeline__canvas"
            data-timeline-canvas
            style="--timeline-width: <?php echo esc_attr($canvas_width); ?>px; --timeline-canvas-width: <?php echo esc_attr($canvas_width); ?>px; --timeline-year-width: <?php echo esc_attr($year_spacing); ?>px; --timeline-edge-padding: <?php echo esc_attr($edge_padding); ?>px;"
        >
            <div class="nu-journey-timeline__grid" aria-hidden="true"></div>

            <ol class="nu-journey-timeline__entries">
                <?php foreach ($entries as $entry_index => $entry) : ?>
                    <?php
                    $tone = $entry['highlight'] ? 'highlight' : ($entry_index % 2 === 0 ? 'light' : 'dark');
                    ?>
                    <li
                        class="nu-journey-timeline__entry nu-journey-timeline__entry--<?php echo esc_attr($tone); ?>"
                        data-timeline-entry
                        data-month-index="<?php echo esc_attr((($entry['year'] - $earliest_year) * 12) + ($entry['month'] - 1)); ?>"
                        data-month-span="<?php echo esc_attr((((($entry['end_year'] * 12) + $entry['end_month']) - (($entry['year'] * 12) + $entry['month'])) + 1)); ?>"
                    >
                        <button
                            class="nu-journey-timeline__trigger<?php echo (!empty($entry['image']['id']) || !empty($entry['image']['url'])) ? ' nu-journey-timeline__trigger--has-image' : ''; ?>"
                            type="button"
                            data-timeline-dialog-trigger
                            data-entry-index="<?php echo esc_attr($entry_index); ?>"
                            aria-haspopup="dialog"
                            aria-label="<?php echo esc_attr(sprintf(__('View milestone details: %s', 'nuventures'), $entry['title'])); ?>"
                        >
                            <?php if (!empty($entry['image']['id'])) : ?>
                                <?php
                                echo wp_get_attachment_image(
                                    $entry['image']['id'],
                                    'medium',
                                    false,
                                    array(
                                        'class'    => 'nu-journey-timeline__image',
                                        'alt'      => $entry['image']['alt'],
                                        'loading'  => 'lazy',
                                        'decoding' => 'async',
                                        'sizes'    => '210px',
                                    )
                                );
                                ?>
                            <?php elseif (!empty($entry['image']['url'])) : ?>
                                <img class="nu-journey-timeline__image" src="<?php echo esc_url($entry['image']['url']); ?>" alt="<?php echo esc_attr($entry['image']['alt']); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>

                            <span class="nu-journey-timeline__card-body">
                                <span class="nu-journey-timeline__card-title"><?php echo esc_html($entry['title']); ?></span>
                                <img
                                    class="nu-journey-timeline__arrow"
                                    src="<?php echo esc_url($asset_path . ('light' === $tone ? 'arrow-dark.svg' : 'arrow-light.svg')); ?>"
                                    alt=""
                                    width="24"
                                    height="24"
                                    aria-hidden="true"
                                >
                            </span>
                        </button>

                        <template data-timeline-dialog-template>
                            <?php if (!empty($entry['image']['id'])) : ?>
                                <?php
                                echo wp_get_attachment_image(
                                    $entry['image']['id'],
                                    'large',
                                    false,
                                    array(
                                        'class'    => 'nu-journey-dialog__image',
                                        'alt'      => $entry['image']['alt'],
                                        'loading'  => 'lazy',
                                        'decoding' => 'async',
                                        'sizes'    => '(max-width: 740px) calc(100vw - 80px), 660px',
                                    )
                                );
                                ?>
                            <?php elseif (!empty($entry['image']['url'])) : ?>
                                <img class="nu-journey-dialog__image" src="<?php echo esc_url($entry['image']['url']); ?>" alt="<?php echo esc_attr($entry['image']['alt']); ?>" loading="lazy" decoding="async">
                            <?php endif; ?>

                            <p class="nu-journey-dialog__date"><?php echo esc_html($entry['date_label']); ?></p>

                            <?php if ($entry['big_title']) : ?>
                                <h2 class="nu-journey-dialog__title"><?php echo esc_html($entry['big_title']); ?></h2>
                            <?php endif; ?>

                            <?php if ($entry['description']) : ?>
                                <div class="nu-journey-dialog__description"><?php echo wp_kses_post(wpautop($entry['description'])); ?></div>
                            <?php endif; ?>
                        </template>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>

    <dialog class="nu-journey-dialog" data-timeline-dialog aria-label="<?php esc_attr_e('Timeline milestone details', 'nuventures'); ?>">
        <div class="nu-journey-dialog__layout">
            <button class="nu-journey-dialog__nav nu-journey-dialog__nav--previous" type="button" data-timeline-dialog-previous aria-label="<?php esc_attr_e('Previous milestone', 'nuventures'); ?>">
                <img src="<?php echo esc_url($asset_path . 'dialog-arrow.svg'); ?>" alt="" width="24" height="24" aria-hidden="true">
            </button>

            <div class="nu-journey-dialog__panel">
                <button class="nu-journey-dialog__close" type="button" data-timeline-dialog-close aria-label="<?php esc_attr_e('Close milestone details', 'nuventures'); ?>">
                    <img src="<?php echo esc_url($asset_path . 'dialog-close.svg'); ?>" alt="" width="24" height="24" aria-hidden="true">
                </button>
                <div class="nu-journey-dialog__content" data-timeline-dialog-content></div>
            </div>

            <button class="nu-journey-dialog__nav nu-journey-dialog__nav--next" type="button" data-timeline-dialog-next aria-label="<?php esc_attr_e('Next milestone', 'nuventures'); ?>">
                <img src="<?php echo esc_url($asset_path . 'dialog-arrow.svg'); ?>" alt="" width="24" height="24" aria-hidden="true">
            </button>
        </div>
    </dialog>
</section>
