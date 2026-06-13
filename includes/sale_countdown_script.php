<script>
    (function () {
        if (window.ApeXSaleCountdownsLoaded) return;
        window.ApeXSaleCountdownsLoaded = true;

        const serverNowMs = <?php echo time() * 1000; ?>;
        const browserLoadedAtMs = Date.now();

        function getSyncedNowMs() {
            return serverNowMs + (Date.now() - browserLoadedAtMs);
        }

        function formatRemaining(diff) {
            const d = Math.floor(diff / 86400000);
            const h = Math.floor((diff % 86400000) / 3600000);
            const m = Math.floor((diff % 3600000) / 60000);
            const s = Math.floor((diff % 60000) / 1000);

            if (d > 0) return `${d}d ${h}h ${m}m ${s}s`;
            if (h > 0) return `${h}h ${m}m ${s}s`;
            return `${m}m ${s}s`;
        }

        function formatExpiry(expMs) {
            return new Date(expMs).toLocaleString('en-PH', {
                timeZone: 'Asia/Manila',
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        function updateSaleCountdowns() {
            document.querySelectorAll('.sale-countdown[data-expiry]').forEach(el => {
                const exp = parseInt(el.dataset.expiry, 10) * 1000;
                const diff = exp - getSyncedNowMs();
                const txt = el.querySelector('.cdown-text');
                if (!txt) return;

                if (!Number.isFinite(exp) || diff <= 0) {
                    el.style.display = 'none';
                    return;
                }

                txt.textContent = `Sale ends in ${formatRemaining(diff)}`;
                el.title = `Ends ${formatExpiry(exp)}`;
                el.setAttribute('aria-label', `Sale ends ${formatExpiry(exp)}`);
            });
        }

        updateSaleCountdowns();
        setInterval(updateSaleCountdowns, 1000);
    })();
</script>
