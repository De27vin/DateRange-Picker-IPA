export default {
    data() {
        return {
            // Callcenter mode state
            agentAlarmState: null, // 'calling', 'answering', 'connected', null
            alarmingDeviceId: null,
            alarmSessionUuid: null, // Needed for alarm classification
            showClassificationDropdown: false,
            agentStatusListener: null,

            // Classification options (without Hang up as per requirement)
            classificationOptions: [
                { key: 'ALARM_ACTIVE', label: 'Real Alarm', fkey: 'F1', icon: 'bell', color: '#eab308' },
                { key: 'ALARM_END', label: 'Alarm End', fkey: 'F2', icon: 'bell', color: '#22c55e' },
                { key: 'NO_ALARM', label: 'Technician', fkey: 'F3', icon: 'person', color: '#ef4444' },
                { key: 'ALARM_MISUSE', label: 'Misuse', fkey: 'F4', icon: 'bell_slash', color: '#3b82f6' },
                { key: 'ALARM_MUTE', label: 'Mute', fkey: 'F5', icon: 'bell_slash', color: '#3b82f6' }
            ]
        }
    },

    computed: {
        isCallcenterModeActive() {
            return this.callcenterMode === true && this.deviceModeId;
        }
    },

    methods: {
        isAlarmingDevice(device) {
            return this.isCallcenterModeActive && device.device_id === this.alarmingDeviceId;
        },

        shouldShowCallcenterAlarmIndicator(device) {
            return this.isCallcenterModeActive && this.isAlarmingDevice(device);
        },

        shouldShowCarCallButton(device) {
            if (this.isCallcenterModeActive && this.isAlarmingDevice(device)) {
                return false; // Hide carcall, show alarm phone button instead
            }
            return true;
        },

        shouldShowAlarmPhoneButton(device) {
            return this.isCallcenterModeActive && this.isAlarmingDevice(device);
        },

        getAlarmPhoneButtonStyle(device) {
            if (!this.isAlarmingDevice(device)) {
                return null;
            }

            // Map semantic state names to colors
            if (this.agentAlarmState === 'calling') {
                return { 'background-color': '#22c55e', 'color': 'white' }; // GREEN (initial)
            }
            if (this.agentAlarmState === 'answering') {
                return { 'background-color': '#eab308', 'color': 'black' }; // BRIGHT YELLOW (waiting)
            }
            if (this.agentAlarmState === 'connected') {
                return { 'background-color': 'lightcoral', 'color': 'white' }; // RED (matches critical alerts)
            }

            return null;
        },

        isAlarmPhoneClickable(device) {
            if (!this.isAlarmingDevice(device)) {
                return false;
            }
            // Only clickable when calling or connected (for classification)
            if (this.agentAlarmState === 'calling') {
                return true;
            }
            if (this.agentAlarmState === 'connected') {
                return this.isCurrentUserAlarmController();
            }
            return false;
        },

        isCurrentUserAlarmController() {
            // Any connected agent can classify (architect hasn't implemented user tracking)
            return this.agentAlarmState === 'connected';
        },

        initCallcenterMode() {
            if (!this.isCallcenterModeActive) {
                return;
            }

            this.alarmingDeviceId = this.deviceModeId;
            this.agentAlarmState = 'calling'; // Initial state: GREEN button
            this.showClassificationDropdown = false;

            this.subscribeToAgentAlarmStatus();
            document.addEventListener('keydown', this.handleFKeyPress);
        },

        subscribeToAgentAlarmStatus() {
            if (!this.deviceModeId) {
                console.log('unable to subscribe to agent alarm status - no deviceId!');
                return;
            }

            // Listen for agent alarm status changes (WebSocket auto-initialized in realtime.js)
            this.agentStatusListener = (event) => this.handleAgentAlarmStatusChange(event);
            document.addEventListener('agent-alarm-status-changed', this.agentStatusListener);
        },

        handleAgentAlarmStatusChange(event) {
            const { deviceId, status } = event.detail;

            if (deviceId !== this.alarmingDeviceId) {
                return;
            }

            if (status === 'connecting') {
                this.agentAlarmState = 'answering';
                this.showClassificationDropdown = false;
                return;
            }

            if (status === 'connected') {
                // ANSWER event received - turn RED (busy/connected)
                this.agentAlarmState = 'connected';
                // Only show dropdown if we have alarm_session_uuid (user clicked and got response)
                if (this.alarmSessionUuid) {
                    this.showClassificationDropdown = true;
                }
            } else if (status === 'disconnected') {
                // END event or overflow - reset
                this.resetAlarmMode();
            }
        },

        resetAlarmMode() {
            this.agentAlarmState = null;
            this.alarmingDeviceId = null;
            this.alarmSessionUuid = null;
            this.showClassificationDropdown = false;
        },

        handleAlarmPhoneClick(device) {
            if (!this.isAlarmingDevice(device)) {
                return;
            }

            if (this.agentAlarmState === 'calling') {
                // GREEN - initiate connection
                this.connectToAlarmAgent(device);
            } else if (this.agentAlarmState === 'connected') {
                // RED - toggle classification dropdown
                this.toggleClassificationDropdown();
            }
        },

        connectToAlarmAgent(device) {
            if (this.agentAlarmState !== 'calling') {
                return;
            }

            // Set to YELLOW (answering/connecting)
            this.agentAlarmState = 'answering';

            window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));

            axios.post('/callcenter/connect-agent', { device_id: device.device_id })
                .then(response => {
                    if (response.data.success) {
                        window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Connecting to alarm call'), 'success']} ));
                        this.alarmSessionUuid = response.data.alarm_session_uuid;
                        // Wait for ANSWER event to turn RED (connected)
                    } else {
                        window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: response.data.error || this.trans('Failed to connect to alarm call') }} ));
                        this.agentAlarmState = 'calling'; // Reset to GREEN
                    }
                })
                .catch(error => {
                    const errorMsg = error.response?.data?.error || this.trans('Failed to connect to alarm call');
                    window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: errorMsg }} ));
                    this.agentAlarmState = 'calling'; // Reset to GREEN
                })
                .finally(() => {
                    window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
                });
        },

        toggleClassificationDropdown() {
            if (this.agentAlarmState !== 'connected') {
                return;
            }
            if (!this.isCurrentUserAlarmController()) {
                return;
            }
            this.showClassificationDropdown = !this.showClassificationDropdown;
        },

        classifyAlarmCall(classification) {
            if (!this.alarmSessionUuid) {
                window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('No active alarm session') }} ));
                return;
            }
            if (!this.isCurrentUserAlarmController()) {
                window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Only connected agent may classify the alarm') }} ));
                return;
            }

            window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: true }}));

            axios.post('/callcenter/classify-alarm', {
                device_id: this.alarmingDeviceId,
                alarm_session_uuid: this.alarmSessionUuid,
                classification: classification.key
            })
                .then(response => {
                    if (response.data.success) {
                        window.dispatchEvent(new CustomEvent('notify', {detail: [this.trans('Classification sent: ') + classification.label, 'success']} ));
                        this.showClassificationDropdown = false;
                    } else {
                        window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Classification failed') }} ));
                    }
                })
                .catch(error => {
                    window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: this.trans('Classification failed') }} ));
                })
                .finally(() => {
                    window.dispatchEvent(new CustomEvent('loading', { detail: { action: 'equipment', loading: false }} ));
                });
        },

        handleFKeyPress(event) {
            if (!this.isCallcenterModeActive || this.agentAlarmState !== 'connected' || !this.showClassificationDropdown) {
                return;
            }

            const fkeyMap = {
                'F1': 0,
                'F2': 1,
                'F3': 2,
                'F4': 3,
                'F5': 4
            };

            const index = fkeyMap[event.key];
            if (index !== undefined) {
                event.preventDefault();
                const classification = this.classificationOptions[index];
                this.classifyAlarmCall(classification);
            }
        },

        cleanupCallcenterMode() {
            if (this.agentStatusListener) {
                document.removeEventListener('agent-alarm-status-changed', this.agentStatusListener);
                this.agentStatusListener = null;
            }
            document.removeEventListener('keydown', this.handleFKeyPress);
            this.resetAlarmMode();
        }
    },

    watch: {
        callcenterMode(newValue) {
            if (newValue) {
                this.initCallcenterMode();
            } else {
                this.cleanupCallcenterMode();
            }
        },
        deviceModeId(newValue, oldValue) {
            if (!this.callcenterMode) {
                return;
            }
            if (newValue !== oldValue) {
                this.cleanupCallcenterMode();
                this.initCallcenterMode();
            }
        }
    },

    mounted() {
        if (this.callcenterMode) {
            this.initCallcenterMode();
        }
    },

    beforeDestroy() {
        if (this.callcenterMode) {
            this.cleanupCallcenterMode();
        }
    }
}
