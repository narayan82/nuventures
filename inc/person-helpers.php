<?php
/**
 * Shared helpers for Person-related content.
 *
 * @package NuVentures
 */

if (!function_exists('nuventures_normalize_related_ids')) {
    /**
     * Normalize ACF relationship values to unique post IDs.
     *
     * @param mixed $value ACF relationship value.
     * @return int[]
     */
    function nuventures_normalize_related_ids($value) {
        $ids = array();

        if ($value instanceof WP_Post) {
            $ids[] = (int) $value->ID;
        } elseif (is_numeric($value)) {
            $ids[] = (int) $value;
        } elseif (is_array($value)) {
            if (isset($value['ID']) || isset($value['id'])) {
                $ids[] = isset($value['ID']) ? (int) $value['ID'] : (int) $value['id'];
            } else {
                foreach ($value as $item) {
                    $ids = array_merge($ids, nuventures_normalize_related_ids($item));
                }
            }
        }

        return array_values(array_unique(array_filter(array_map('absint', $ids))));
    }
}

if (!function_exists('nuventures_get_image_details')) {
    /**
     * Normalize an ACF image value.
     *
     * @param mixed  $value    ACF image value.
     * @param string $fallback Fallback alt text.
     * @return array{id:int,url:string,alt:string}
     */
    function nuventures_get_image_details($value, $fallback = '') {
        $image = array(
            'id'  => 0,
            'url' => '',
            'alt' => $fallback,
        );

        if (is_numeric($value)) {
            $image['id'] = absint($value);
        } elseif (is_array($value)) {
            $image['id']  = isset($value['ID']) ? absint($value['ID']) : (isset($value['id']) ? absint($value['id']) : 0);
            $image['url'] = isset($value['url']) ? (string) $value['url'] : '';
            $image['alt'] = !empty($value['alt']) ? (string) $value['alt'] : $fallback;
        } elseif (is_string($value)) {
            $image['url'] = $value;
        }

        if ($image['id'] && $fallback === $image['alt']) {
            $attachment_alt = get_post_meta($image['id'], '_wp_attachment_image_alt', true);
            $image['alt']   = $attachment_alt ? $attachment_alt : $fallback;
        }

        return $image;
    }
}
