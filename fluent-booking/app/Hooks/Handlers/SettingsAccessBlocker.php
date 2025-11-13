<?php

namespace FluentBooking\App\Hooks\Handlers;

class SettingsAccessBlocker
{
    /**
     * Hash fragments that should be blocked within the SPA settings screen.
     *
     * @var array<int, string>
     */
    protected $blockedHashes = [
        '#/settings/global-modules',
        '#/settings/globalmodules',
        '#/settings/license',
        '#/global-modules',
        '#/globalmodules',
        '#/license',
    ];

    /**
     * The hash fragment for the safe destination inside the settings screen.
     *
     * @var string
     */
    protected $fallbackHash = '#/settings/general-settings';

    public function register()
    {
        add_filter('fluent_booking/settings_menu_items', [$this, 'filterSettingsMenu'], 999);
        add_action('fluent_booking/admin_app_rendering', [$this, 'scheduleRedirectScript']);
    }

    /**
     * Remove the blocked menu items from the settings navigation.
     *
     * @param array $items
     * @return array
     */
    public function filterSettingsMenu($items)
    {
        unset($items['global_modules'], $items['license']);

        return $items;
    }

    public function scheduleRedirectScript()
    {
        if (!$this->isFluentBookingScreen()) {
            return;
        }

        add_action('admin_print_footer_scripts', [$this, 'printRedirectScript'], 20);
    }

    /**
     * Determine whether the current admin screen belongs to Fluent Booking.
     *
     * @return bool
     */
    protected function isFluentBookingScreen()
    {
        if (!is_admin()) {
            return false;
        }

        if (!isset($_GET['page'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return false;
        }

        $page = sanitize_key(wp_unslash($_GET['page'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        return $page === 'fluent-booking';
    }

    public function printRedirectScript()
    {
        $blocked = array_map('strtolower', $this->blockedHashes);
        $fallback = strtolower($this->fallbackHash);
        ?>
        <script type="text/javascript">
            (function() {
                var blockedTargets = <?php echo wp_json_encode($blocked); ?>;
                var fallbackHash = <?php echo wp_json_encode($fallback); ?>;

                function normalise(hash) {
                    if (!hash) {
                        return '';
                    }

                    var cleaned = hash.toLowerCase();

                    if (cleaned.slice(-1) === '/') {
                        cleaned = cleaned.replace(/\/+$/, '');
                    }

                    return cleaned;
                }

                function needsRedirect(hash) {
                    if (!hash) {
                        return false;
                    }

                    for (var i = 0; i < blockedTargets.length; i++) {
                        var target = blockedTargets[i];
                        if (hash === target || hash.indexOf(target + '/') === 0) {
                            return true;
                        }
                    }

                    return false;
                }

                function maybeRedirect() {
                    var currentHash = normalise(window.location.hash);

                    if (!needsRedirect(currentHash)) {
                        return;
                    }

                    var baseUrl = window.location.href.split('#')[0];
                    window.location.replace(baseUrl + fallbackHash);
                }

                document.addEventListener('DOMContentLoaded', maybeRedirect);
                window.addEventListener('hashchange', maybeRedirect);
            })();
        </script>
        <?php
    }
}
