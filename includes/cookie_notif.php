<?php
$_apexCookieSet  = isset($_COOKIE['apex_logged_in']) && is_numeric($_COOKIE['apex_logged_in']);
$_apexCookieSetAt = $_apexCookieSet ? (int)$_COOKIE['apex_logged_in'] : 0;
$_apexSecondsLeft = $_apexCookieSet ? max(0, 60 - (time() - $_apexCookieSetAt)) : 0;
?>
<?php if ($_apexCookieSet && $_apexSecondsLeft > 0): ?>
    <div class="apex-cookie-notif" id="apexCookieNotif">
        <div class="apex-cookie-notif__inner">
            <span class="apex-cookie-notif__icon"><i class="fas fa-cookie-bite"></i></span>
            <span class="apex-cookie-notif__text">
                Cookie was set &mdash; session active.
                Expires in <strong><span id="apexCookieCountdown"><?= $_apexSecondsLeft ?></span>s</strong>.
            </span>
            <button class="apex-cookie-close" onclick="dismissApexNotif()" aria-label="Dismiss">&times;</button>
        </div>
        <div class="apex-cookie-progress" id="apexCookieBar">
            <div class="apex-cookie-progress__fill" id="apexCookieFill"
                style="width:<?= round($_apexSecondsLeft / 60 * 100) ?>%"></div>
        </div>
    </div>
    <script>
        (function() {
            var el = document.getElementById('apexCookieNotif');
            var span = document.getElementById('apexCookieCountdown');
            var fill = document.getElementById('apexCookieFill');
            if (!el) return;

            var setAt = <?= $_apexCookieSetAt ?> * 1000;
            var duration = 60 * 1000;

            function tick() {
                var left = Math.max(0, Math.round((setAt + duration - Date.now()) / 1000));
                if (span) span.textContent = left;
                if (fill) fill.style.width = (left / 60 * 100) + '%';

                if (left <= 0) {
                    el.classList.add('apex-cookie-notif--expired');
                    el.querySelector('.apex-cookie-notif__text').innerHTML =
                        '<i class="fas fa-clock me-1"></i> Cookie has <strong>expired</strong>.';
                    setTimeout(function() {
                        el && el.remove();
                    }, 2500);
                } else {
                    setTimeout(tick, 1000);
                }
            }
            tick();

            window.dismissApexNotif = function() {
                el && el.remove();
            };
        })();
    </script>
<?php endif; ?>