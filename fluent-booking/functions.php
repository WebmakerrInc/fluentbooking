<?php
/**
 * Theme/Plugin helper functions.
 *
 * Custom snippet to block access to FluentBooking's Global Feature Modules screen.
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_footer', function () {
    if (!is_admin()) {
        return;
    }

    $currentPage = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';

    if ($currentPage !== 'fluent-booking') {
        return;
    }
    ?>
    <script>
        (function () {
            var restrictedHash = '#/settings/configure-integrations/global-modules';
            var fallbackHash = '#/settings';

            function shouldRedirect(hashValue) {
                if (!hashValue) {
                    return false;
                }
                return hashValue === restrictedHash || hashValue.indexOf(restrictedHash + '/') === 0;
            }

            function redirectIfNeeded() {
                if (shouldRedirect(window.location.hash)) {
                    window.location.hash = fallbackHash;
                }
            }

            document.addEventListener('DOMContentLoaded', redirectIfNeeded);
            window.addEventListener('hashchange', redirectIfNeeded);
            redirectIfNeeded();
        })();
    </script>
    <?php
});
