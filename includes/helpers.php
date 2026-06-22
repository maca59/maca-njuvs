<?php
/**
 * Shared helpers for maca Njuvs.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Format a price amount for display.
 *
 * @param float $amount Price amount.
 * @return string
 */
function maca_njuvs_format_price($amount) {
    $amount = (float) $amount;

    if ($amount <= 0) {
        return '';
    }

    return number_format($amount, 0, ',', ' ') . ' kr';
}

/**
 * Weekday order for calendar labels.
 *
 * @param bool $monday_first Start week on Monday.
 * @return array<int, int>
 */
function maca_njuvs_weekday_day_order($monday_first = true) {
    if ($monday_first) {
        return array(1, 2, 3, 4, 5, 6, 0);
    }

    return array(0, 1, 2, 3, 4, 5, 6);
}

/**
 * Short weekday labels (Mon–Sun) keyed by day index.
 *
 * @param bool $monday_first Start week on Monday.
 * @return array<int, string>
 */
function maca_njuvs_weekday_short_labels_ordered($monday_first = true) {
    $short = array(
        0 => __('Sun', 'maca-njuvs'),
        1 => __('Mon', 'maca-njuvs'),
        2 => __('Tue', 'maca-njuvs'),
        3 => __('Wed', 'maca-njuvs'),
        4 => __('Thu', 'maca-njuvs'),
        5 => __('Fri', 'maca-njuvs'),
        6 => __('Sat', 'maca-njuvs'),
    );

    $ordered = array();

    foreach (maca_njuvs_weekday_day_order($monday_first) as $day) {
        if (isset($short[ $day ])) {
            $ordered[ $day ] = $short[ $day ];
        }
    }

    return $ordered;
}
