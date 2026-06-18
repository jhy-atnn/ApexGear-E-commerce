<?php
$_apexCookieSet  = isset($_COOKIE['apex_logged_in']) && is_numeric($_COOKIE['apex_logged_in']);
$_apexCookieSetAt = $_apexCookieSet ? (int)$_COOKIE['apex_logged_in'] : 0;
require_once __DIR__ . '/auth_timeout.php';

$_apexSecondsLeft = $_apexCookieSet ? max(0, APEX_LOGIN_TIMEOUT_SECONDS - (time() - $_apexCookieSetAt)) : 0;
$_apexCookieNotifDismissed = isset($_COOKIE['apex_cookie_notif_dismissed']) && $_COOKIE['apex_cookie_notif_dismissed'] === '1';

if (!function_exists('_apexFormatCookieSeconds')) {
    function _apexFormatCookieSeconds(int $seconds): string
    {
        if ($seconds <= 60) {
            return $seconds . 's';
        }

        $days = intdiv($seconds, 86400);
        $seconds %= 86400;
        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;
        $minutes = intdiv($seconds, 60);
        $seconds %= 60;

        $parts = [];
        if ($days > 0) {
            $parts[] = $days . 'd';
        }
        if ($hours > 0) {
            $parts[] = $hours . 'h';
        }
        if ($minutes > 0) {
            $parts[] = $minutes . 'm';
        }
        if ($seconds > 0) {
            $parts[] = $seconds . 's';
        }

        return implode(' ', $parts);
    }
}
?>
<?php if ($_apexCookieSet && $_apexSecondsLeft > 0 && !$_apexCookieNotifDismissed): ?>
    <div class="apex-cookie-notif" id="apexCookieNotif">
        <div class="apex-cookie-notif__inner">
            <span class="apex-cookie-notif__icon"><i class="fas fa-cookie-bite"></i></span>
            <span class="apex-cookie-notif__text">
                Cookie was set &mdash; session active.
                Expires in <strong><span id="apexCookieCountdown"><?= htmlspecialchars(_apexFormatCookieSeconds($_apexSecondsLeft), ENT_QUOTES, 'UTF-8') ?></span></strong>.
            </span>
            <button class="apex-cookie-close" onclick="dismissApexNotif()" aria-label="Dismiss">&times;</button>
        </div>
        <div class="apex-cookie-progress" id="apexCookieBar">
            <div class="apex-cookie-progress__fill" id="apexCookieFill"
                style="width:<?= round($_apexSecondsLeft / APEX_LOGIN_TIMEOUT_SECONDS * 100) ?>%"></div>
        </div>
    </div>
    <script>
        (function() {
            var el = document.getElementById('apexCookieNotif');
            var span = document.getElementById('apexCookieCountdown');
            var fill = document.getElementById('apexCookieFill');
            if (!el) return;

            var setAt = <?= $_apexCookieSetAt ?> * 1000;
            var duration = <?= APEX_LOGIN_TIMEOUT_SECONDS ?> * 1000;

            function formatSeconds(totalSeconds) {
                if (totalSeconds <= 60) {
                    return totalSeconds + 's';
                }

                var days = Math.floor(totalSeconds / 86400);
                totalSeconds %= 86400;
                var hours = Math.floor(totalSeconds / 3600);
                totalSeconds %= 3600;
                var minutes = Math.floor(totalSeconds / 60);
                var seconds = totalSeconds % 60;
                var parts = [];

                if (days > 0) parts.push(days + 'd');
                if (hours > 0) parts.push(hours + 'h');
                if (minutes > 0) parts.push(minutes + 'm');
                if (seconds > 0) parts.push(seconds + 's');

                return parts.join(' ');
            }

            function tick() {
                var left = Math.max(0, Math.round((setAt + duration - Date.now()) / 1000));
                if (span) span.textContent = formatSeconds(left);
                if (fill) fill.style.width = (left / <?= APEX_LOGIN_TIMEOUT_SECONDS ?> * 100) + '%';

                if (left <= 0) {
                    el.classList.add('apex-cookie-notif--expired');
                    el.querySelector('.apex-cookie-notif__text').innerHTML =
                        '<i class="fas fa-clock me-1"></i> Cookie has <strong>expired</strong>.';
                    setTimeout(function() {
                        window.location.href = 'logout.php';
                    }, 1200);
                } else {
                    setTimeout(tick, 1000);
                }
            }
            tick();

            window.dismissApexNotif = function() {
                document.cookie = 'apex_cookie_notif_dismissed=1; path=/; max-age=' + Math.max(60, Math.ceil(duration / 1000)) + '; SameSite=Lax';
                el && el.remove();
            };
        })();
    </script>
<?php endif; ?>
