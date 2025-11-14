<?php defined('ABSPATH') or die;
/**
 * FluentBooking Pro module bootstrapper.
 *
 * This file no longer represents a standalone WordPress plugin entry point.
 * It is retained to keep backward compatibility with paths that expect the
 * previous file name while the Pro features are loaded from the unified plugin.
 */

if (defined('FLUENT_BOOKING_PRO_DIR_FILE')) {
    return;
}

define('FLUENT_BOOKING_PRO_DIR_FILE', __FILE__);
define('FLUENT_BOOKING_PRO_DIR', plugin_dir_path(__FILE__));
define('FLUENT_BOOKING_PRO_VERSION', '1.9.10');
define('FLUENT_BOOKING_PRO_DB_VERSION', '1.0.0');
define('FLUENT_BOOKING_MIN_CORE_VERSION', '1.9.10');

require __DIR__ . '/vendor/autoload.php';

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/boot/app.php'));
