import {isEmpty} from "../../assets/js/globalUtils";

export default {
    methods: {
        notifySuccess(message) {
          window.dispatchEvent(new CustomEvent('notify', {detail: [message, 'success'] } ));
        },

        notifyError(message) {
          window.dispatchEvent(new CustomEvent('notifyerror', {detail: {message: message}} ));
        },

        displayResponseErrors(response) {
            console.log('displayResponseErrors received:', response?.data);
            const errors = response?.data?.errors ?? [];
            if (Array.isArray(errors) && errors.length > 0) {
                console.log('Displaying errors:', errors);
                const type = response?.data?.success === true ? 'notifywarning' : 'notifyerror';
                for (const error of errors) {
                    window.dispatchEvent(new CustomEvent(type, {detail: {message: error}}));
                }
            }
        },

        displayResponseNotifications(response) {
            console.log('displayResponseNotifications received:', response?.data);
            const notifications = response?.data?.notifications ?? {};
            if (typeof notifications === 'object' && !Array.isArray(notifications)) {
                for (const [type, messages] of Object.entries(notifications)) {
                    if (Array.isArray(messages)) {
                        for (const message of messages) {
                            console.log(`Dispatching ${type} notification:`, message);
                            const eventType = `notify${type}`;
                            window.dispatchEvent(new CustomEvent(eventType, { detail: { message } }));
                        }
                    }
                }
            }
},

    }
};