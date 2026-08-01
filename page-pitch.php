<?php
/**
 * Standalone pitch-flow page template.
 *
 * @package NuVentures
 */
?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class('pitch-page'); ?>>
<?php wp_body_open(); ?>

<?php
$pitch_referrer   = wp_get_referer();
$pitch_return_url = $pitch_referrer
    ? wp_validate_redirect($pitch_referrer, home_url('/'))
    : home_url('/');
get_template_part(
    'template-parts/components/pitch-flow',
    null,
    array('close_url' => $pitch_return_url)
);
?>

<?php wp_footer(); ?>
</body>
</html>
