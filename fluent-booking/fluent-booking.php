<?php defined('ABSPATH') or die;
/**
Plugin Name: Bookings - Appointment Scheduling & Booking Solution
Description: Bookings is the ultimate solution for booking appointments, meetings, webinars, events, sales calls, and more.
Version: 1.9.11
Author: Appointment & Booking Solution Team - WPManageNinja
Author URI: https://fluentbooking.com
Plugin URI: https://fluentbooking.com/pricing/
License: GPLv2 or later
Text Domain: fluent-booking
Domain Path: /language
*/

if (defined('FLUENT_BOOKING_VERSION')) {
    return;
}

define('FLUENT_BOOKING_DIR', plugin_dir_path(__FILE__));
define('FLUENT_BOOKING_URL', plugin_dir_url(__FILE__));
define('FLUENT_BOOKING_VERSION', '1.9.11');
define('FLUENT_BOOKING_DB_VERSION', '1.0.0');
define('FLUENT_BOOKING_ASSETS_VERSION', '1.9.11');
define('FLUENT_BOOKING_MIN_PRO_VERSION', '1.9.10');

// Keep the lite flag for backward compatibility checks within the codebase.
if (!defined('FLUENT_BOOKING_LITE')) {
    define('FLUENT_BOOKING_LITE', true);
}

// Pro module constants so that the former standalone pro plugin can bootstrap from within this plugin.
define('FLUENT_BOOKING_DIR_FILE', __FILE__);
define('FLUENT_BOOKING_PRO_DIR', FLUENT_BOOKING_DIR . 'pro/');
define('FLUENT_BOOKING_PRO_DIR_FILE', FLUENT_BOOKING_PRO_DIR . 'fluent-booking-pro.php');
define('FLUENT_BOOKING_PRO_VERSION', '1.9.10');
define('FLUENT_BOOKING_PRO_DB_VERSION', '1.0.0');
define('FLUENT_BOOKING_MIN_CORE_VERSION', FLUENT_BOOKING_VERSION);

require __DIR__ . '/vendor/autoload.php';

if (file_exists(FLUENT_BOOKING_PRO_DIR . 'vendor/autoload.php')) {
    require FLUENT_BOOKING_PRO_DIR . 'vendor/autoload.php';
}

require_once __DIR__ . '/functions.php';

call_user_func(function ($bootstrap) {
    $bootstrap(__FILE__);
}, require(__DIR__ . '/boot/app.php'));

if (file_exists(FLUENT_BOOKING_PRO_DIR . 'boot/app.php')) {
    call_user_func(function ($bootstrap) {
        $bootstrap(FLUENT_BOOKING_PRO_DIR_FILE);
    }, require(FLUENT_BOOKING_PRO_DIR . 'boot/app.php'));
}
