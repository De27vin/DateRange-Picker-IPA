/**
 * Realtime WebSocket Service
 *
 * Automatically subscribes to account-level realtime channel and dispatches
 * CustomEvents for different event types. Components only need to listen
 * to these events - no manual subscription needed.
 *
 * Dispatched CustomEvents:
 * - alarm-data-updated: {accountId, alarmCalls}
 * - agent-alarm-status-changed: {accountId, deviceId, status, alarmSessionId, agentSessionUuid, timestamp}
 * - carcall-status-changed: {accountId, deviceId, status}
 */

window.RealtimeService = {
    accountId: null,
    subscription: null,

    /**
     * Initialize WebSocket connection
     */
    init() {
        // Get account ID from meta tag
        this.accountId = document.querySelector('meta[name="account-id"]')?.content;

        if (!this.accountId) {
            console.log('[Realtime] No account ID found - skipping WebSocket initialization');
            return;
        }

        console.log('[Realtime] Initializing WebSocket for account:', this.accountId);
        this.waitForEcho(() => this.subscribe());
    },

    /**
     * Wait for Echo to be available
     */
    waitForEcho(callback) {
        if (typeof window.Echo !== 'undefined') {
            callback();
        } else {
            setTimeout(() => this.waitForEcho(callback), 100);
        }
    },

    /**
     * Subscribe to account channel
     */
    subscribe() {
        const channelName = `realtime.account.${this.accountId}`;

        this.subscription = window.Echo.private(channelName)
            .subscribed(() => {
                console.log(`[Realtime] Subscribed to ${channelName}`);
            })
            .listen('.realtime-event', (payload) => {
                this.handleEvent(payload);
            })
            .error((error) => {
                console.error(`[Realtime] Subscription error for ${channelName}:`, error);
            });
    },

    /**
     * Handle incoming realtime event
     */
    handleEvent({event_type, data, timestamp}) {
        console.log(`[Realtime] Event received: ${event_type}`, data);

        const handlers = {
            'alarm_notification': () => this.dispatchAlarmNotification(data),
            'agent_status': () => this.dispatchAgentStatus(data, timestamp),
            'carcall_status': () => this.dispatchCarCallStatus(data, timestamp)
        };

        const handler = handlers[event_type];
        if (handler) {
            handler();
        } else {
            console.warn(`[Realtime] Unknown event type: ${event_type}`);
        }
    },

    /**
     * Dispatch alarm notification event
     */
    dispatchAlarmNotification(data) {
        document.dispatchEvent(new CustomEvent('alarm-data-updated', {
            detail: {
                accountId: parseInt(this.accountId),
                alarmCalls: data.alarmCalls || []
            }
        }));
    },

    /**
     * Dispatch agent alarm status event
     */
    dispatchAgentStatus(data, timestamp) {
        document.dispatchEvent(new CustomEvent('agent-alarm-status-changed', {
            detail: {
                accountId: parseInt(this.accountId),
                deviceId: data.device_id,
                status: data.status,
                timestamp: timestamp
            }
        }));
    },

    /**
     * Dispatch car call status event
     */
    dispatchCarCallStatus(data, timestamp) {
        document.dispatchEvent(new CustomEvent('carcall-status-changed', {
            detail: {
                accountId: parseInt(this.accountId),
                deviceId: data.device_id,
                status: data.status
            }
        }));
    },

    /**
     * Unsubscribe from channel (for cleanup)
     */
    unsubscribe() {
        if (this.subscription) {
            window.Echo.leave(`realtime.account.${this.accountId}`);
            this.subscription = null;
            console.log('[Realtime] Unsubscribed from channel');
        }
    }
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.RealtimeService.init();
});
