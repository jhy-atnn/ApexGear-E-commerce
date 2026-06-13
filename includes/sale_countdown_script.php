<script>
    (function () {
        if (window.ApeXSaleCountdownsLoaded) return;
        window.ApeXSaleCountdownsLoaded = true;

        function updateSaleCountdowns() {
            document.querySelectorAll('.sale-countdown[data-expiry]').forEach(el => {
                const exp = parseInt(el.dataset.expiry, 10) * 1000;
                const diff = exp - Date.now();
                const txt = el.querySelector('.cdown-text');
                if (!txt) return;

                if (!Number.isFinite(exp) || diff <= 0) {
                    el.style.display = 'none';
                    return;
                }

                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);

                if (d > 0) txt.textContent = `Sale ends in ${d}d ${h}h ${m}m`;
                else if (h > 0) txt.textContent = `Sale ends in ${h}h ${m}m ${s}s`;
                else txt.textContent = `Sale ends in ${m}m ${s}s`;
            });
        }

        updateSaleCountdowns();
        setInterval(updateSaleCountdowns, 1000);
    })();
</script>
