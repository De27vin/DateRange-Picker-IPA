const getCsrfToken = () => document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';

// Alpine v2: register as a global window function (Alpine.data / alpine:init are v3 only).
window.exportHandler = (config = {}) => ({
    type: config.type || null,
    componentId: config.componentId || null,
    storeUrl: config.storeUrl || '/exports',
    progressLabel: config.progressLabel || 'Preparing export…',
    showFormat: false,
    polling: false,
    progress: 0,
    emailSent: false,
    timer: null,
    init() {
        this.startListener = (event) => {
            const detail = event.detail || {};
            if (!this.shouldHandle(detail)) {
                return;
            }
            this.beginExport(detail.request || {});
        };

        this.startedListener = (event) => {
            const detail = event.detail || {};
            if (!this.shouldHandle(detail)) {
                return;
            }
            this.handleExportStarted(detail);
        };

        window.addEventListener('start-export', this.startListener);
        window.addEventListener('export-started', this.startedListener);
    },
    shouldHandle(detail) {
        if (!detail || detail.type !== this.type) {
            return false;
        }

        if (detail.component_id && this.componentId) {
            return detail.component_id === this.componentId;
        }

        if (detail.component_id && !this.componentId) {
            return false;
        }

        if (!detail.component_id && this.componentId) {
            return false;
        }

        return true;
    },
    beginExport(requestBody) {
        if (!this.storeUrl) {
            return;
        }

        fetch(this.storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(requestBody),
            credentials: 'same-origin',
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Export request failed');
                }
                return response.json();
            })
            .then((data) => {
                window.dispatchEvent(new CustomEvent('export-started', { detail: data }));
            })
            .catch(() => {
                this.polling = false;
                this.progress = 0;
            });
    },
    handleExportStarted(detail) {
        const isEmail = detail.delivery === 'email';
        // Show the progress bar for both browser and email deliveries.
        // For email, the toast appears after the job finishes (not immediately).
        this.startPolling(detail.progress_url, detail.download_url, isEmail);
    },
    startPolling(progressUrl, downloadUrl, isEmail = false) {
        if (!progressUrl) {
            return;
        }

        this.clearTimer();
        this.polling = true;
        this.progress = 0;

        this.timer = setInterval(() => {
            fetch(progressUrl, { credentials: 'same-origin' })
                .then((response) => response.json())
                .then((data) => {
                    if (data.ready) {
                        this.progress = 100;
                        this.polling = false;
                        this.clearTimer();

                        if (isEmail) {
                            // Show "sent to email" confirmation after job completes.
                            this.emailSent = true;
                            setTimeout(() => {
                                this.emailSent = false;
                            }, 3000);
                        } else {
                            const iframe = document.createElement('iframe');
                            iframe.style.display = 'none';
                            iframe.src = downloadUrl;
                            document.body.appendChild(iframe);
                            setTimeout(() => iframe.remove(), 20000);
                        }
                    } else if (typeof data.progress === 'number') {
                        this.progress = data.progress;
                    }
                })
                .catch(() => {
                    this.polling = false;
                    this.clearTimer();
                });
        }, 1000);
    },
    clearTimer() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    },
    cleanup() {
        this.clearTimer();
        window.removeEventListener('start-export', this.startListener);
        window.removeEventListener('export-started', this.startedListener);
    },
});
