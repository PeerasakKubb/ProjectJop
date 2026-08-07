@once
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('deviceToggle', (initialOn) => ({
                    isOn: !!initialOn,
                    loading: false,
                    async submit(url) {
                        this.loading = true;
                        try {
                            const token = document.querySelector('meta[name="csrf-token"]')?.content;
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });

                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                throw new Error(data.message || ('สั่งงานไม่สำเร็จ (' + response.status + ')'));
                            }

                            this.isOn = !!data.device?.is_on;
                            const card = this.$el.closest('.app-card, .rounded-xl');
                            const stateEl = card?.querySelector('[data-device-state]');
                            if (stateEl) {
                                stateEl.textContent = this.isOn ? 'ON' : 'OFF';
                                stateEl.classList.toggle('text-emerald-600', this.isOn);
                                stateEl.classList.toggle('text-slate-300', !this.isOn);
                            }
                            card?.classList.toggle('ring-2', this.isOn);
                            card?.classList.toggle('ring-emerald-400/50', this.isOn);
                        } catch (error) {
                            alert(error.message || 'สั่งงานไม่สำเร็จ — ลองเข้าสู่ระบบใหม่');
                        } finally {
                            this.loading = false;
                        }
                    },
                }));

                Alpine.data('deviceBulkControl', () => ({
                    loading: false,
                    async submit(url) {
                        this.loading = true;
                        try {
                            const token = document.querySelector('meta[name="csrf-token"]')?.content;
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            });
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                throw new Error(data.message || ('สั่งงานไม่สำเร็จ (' + response.status + ')'));
                            }
                            window.location.reload();
                        } catch (error) {
                            alert(error.message || 'สั่งงานไม่สำเร็จ — ลองเข้าสู่ระบบใหม่');
                            this.loading = false;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
