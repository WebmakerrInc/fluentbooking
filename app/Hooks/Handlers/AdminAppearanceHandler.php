<?php

namespace FluentBooking\App\Hooks\Handlers;

use FluentBooking\App\App;

defined('ABSPATH') || exit;

class AdminAppearanceHandler
{
    public function register()
    {
        add_action('admin_enqueue_scripts', function () {
            if (!isset($_REQUEST['page']) || $_REQUEST['page'] !== 'booking') { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                return;
            }

            $app = App::getInstance();
            $assets = $app['url.assets'];

            wp_enqueue_style(
                'fluent_booking_admin_ui_enhancements',
                $assets . 'admin/fluent-booking-ui-enhancements.css',
                ['fluent_booing_admin_app'],
                FLUENT_BOOKING_ASSETS_VERSION
            );
        }, 120);
    }
}
