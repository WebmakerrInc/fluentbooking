<?php

use FluentBookingPro\App\Core\Application;
use FluentBookingPro\Database\DBMigrator;

return function ($file) {
    add_action('fluent_booking/loaded', function ($app) use ($file) {
        new Application($app, $file);
        (new \FluentBookingPro\App\Modules\ModulesInit())->register($app);

        $licenseManager = new \FluentBookingPro\App\Services\PluginManager\LicenseManager();
        $licenseManager->initUpdater();

        $licenseMessage = $licenseManager->getLicenseMessages();

        if ($licenseMessage) {
            add_action('admin_notices', function () use ($licenseMessage) {
                $class = 'notice notice-error fc_message';
                $message = $licenseMessage['message'];
                printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), wp_kses_post($message));
            });
        }

        add_action('fluent_booking/admin_app_rendering', function () {
            $currentDBVersion = get_option('fluent_booking_pro_db_version');
            if (!$currentDBVersion || version_compare($currentDBVersion, FLUENT_BOOKING_PRO_DB_VERSION, '<')) {
                update_option('fluent_booking_pro_db_version', FLUENT_BOOKING_PRO_DB_VERSION, 'no');
                DBMigrator::run();
            }
        });

        if (defined('FLUENT_BOOKING_VERSION') && version_compare(FLUENT_BOOKING_MIN_CORE_VERSION, FLUENT_BOOKING_VERSION, '>')) {
            add_filter('fluent_booking/dashboard_notices', function ($notices) {
                $updateUrl = admin_url('plugins.php?s=fluent-booking&plugin_status=all&fluent-booking_check_update=' . time());
                $notices[] = '<div class="error">' . esc_html__('FluentBooking base plugin needs to be updated to the latest version.', 'fluent-booking-pro') . ' <a href="' . esc_url($updateUrl) . '">' . esc_html__('Click here to update', 'fluent-booking-pro') . '</a></div>';
                return $notices;
            });
        }
    });

    $activationFile = defined('FLUENT_BOOKING_DIR_FILE') ? FLUENT_BOOKING_DIR_FILE : $file;

    register_activation_hook($activationFile, function ($network_wide = false) use ($file) {

        if (defined('FLUENT_BOOKING_DIR')) {
            // Temp Free version Migrator
            (new \FluentBooking\App\Hooks\Handlers\ActivationHandler(\FluentBooking\App\App::getInstance()))->handle($network_wide);
        }

        update_option('fluent_booking_pro_db_version', FLUENT_BOOKING_PRO_DB_VERSION, 'no');
        DBMigrator::run($network_wide);
    });

    if (!defined('FLUENT_BOOKING_DIR')) {
        add_action('admin_notices', function () {
            if (defined('FLUENT_BOOKING_LITE')) {
                return;
            }

            function fluentBookingGetInstallationDetails()
            {
                $activation = (object)[
                    'action' => 'install',
                    'url'    => ''
                ];

                $allPlugins = get_plugins();

                if (isset($allPlugins['fluent-booking/fluent-booking.php'])) {
                    $url = wp_nonce_url(
                        self_admin_url('plugins.php?action=activate&plugin=fluent-booking/fluent-booking.php'),
                        'activate-plugin_fluent-booking/fluent-booking.php'
                    );
                    $activation->action = 'activate';
                } else {
                    $api = (object)[
                        'slug' => 'fluent-booking'
                    ];
                    $url = wp_nonce_url(
                        self_admin_url('update.php?action=install-plugin&plugin=' . $api->slug),
                        'install-plugin_' . $api->slug
                    );
                }

                $activation->url = $url;

                return $activation;
            }

            $pluginInfo = fluentBookingGetInstallationDetails();

            $class = 'notice notice-error booking_notice';

            $install_url_text = 'Click Here to Install the Plugin';

            if ($pluginInfo->action == 'activate') {
                $install_url_text = 'Click Here to Activate the Plugin';
            }

            $message = '<b>HEADS UP:</b> FluentBooking Pro Requires FluentBooking Base Plugin, <b><a href="' . $pluginInfo->url
                . '">' . $install_url_text . '</a></b>';

            printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $message);
        });
    }

};
