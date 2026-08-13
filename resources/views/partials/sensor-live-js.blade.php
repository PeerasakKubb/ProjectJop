<script>
(function () {
    const sensorAgo = (iso) => {
        if (!iso) return '';
        const sec = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 1000));
        if (sec < 8) return 'อัปเดต เมื่อกี้';
        if (sec < 60) return 'อัปเดต ' + sec + ' วินาทีที่แล้ว';
        return 'อัปเดต ' + Math.floor(sec / 60) + ' นาทีที่แล้ว';
    };
    const tick = () => {
        fetch('/api/sensors/now?t=' + Date.now(), { headers: { Accept: 'application/json' }, cache: 'no-store' })
            .then((r) => r.json())
            .then((rows) => {
                (rows || []).forEach((row) => {
                    const box = document.querySelector('[data-sensor-id="' + row.id + '"]');
                    if (!box) return;
                    const valueEl = box.querySelector('.js-sensor-value');
                    const agoEl = box.querySelector('.js-sensor-ago');
                    if (valueEl && row.value !== null) {
                        const unit = valueEl.querySelector('span');
                        valueEl.innerHTML = Number(row.value).toFixed(1) + ' ' + (unit ? unit.outerHTML : '');
                    }
                    if (agoEl) agoEl.textContent = sensorAgo(row.recorded_at);
                });
            })
            .catch(() => {});
    };
    tick();
    setInterval(tick, 1000);
})();
</script>
