<div x-data="initNotification()"
     @notify.window="displayMessage($event.detail)"
     @notifysuccess.window="displaySuccess($event.detail.message)"
     @notifyerror.window="displayError($event.detail.message)"
     @notifywarning.window="displayWarning($event.detail.message)"
     @notifyinfo.window="displayInfo($event.detail.message)"
     class="notify fixed inset-0 flex flex-col items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:justify-start space-y-4"
     style="z-index:10000;">
  <template x-for="(message, messageIndex) in messages" :key="messageIndex" hidden>
    <div x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="w-full max-w-sm bg-white pointer-events-auto overflow-hidden shadow-lg rounded-md relative flex items-center"
         :class="message[1] == 'success' ? 'border-l-4 border-green-500' :
                 message[1] == 'info' ? 'border-l-4 border-blue-500' :
                 message[1] == 'error' ? 'border-l-4 border-red-500' :
                 message[1] == 'danger' ? 'border-l-4 border-red-500' :
                 message[1] == 'warning' ? 'border-l-4 border-orange-500' : 'border-l-4 border-gray-500'">
      <div class="flex-1 p-4">
        <!-- Message Type -->
        <div x-text="severityTrans?.[message[1]] ?? 'info'"
             class="text-sm font-medium uppercase mb-1"
             :class="message[1] == 'success' ? 'text-green-500' :
                     message[1] == 'info' ? 'text-blue-500' :
                     message[1] == 'error' ? 'text-red-500' :
                     message[1] == 'danger' ? 'text-red-500' :
                     message[1] == 'warning' ? 'text-orange-500' : 'text-gray-500'"></div>

        <!-- Message Content -->
        <div>
          <p x-text="message[0] ?? '{{ __('Error occurred on update') }}'" class="text-custom-smaller text-gray-900"></p>
        </div>
      </div>

      <!-- Close button -->
      <button @click="remove(message)"
              class="h-full px-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none"
              aria-label="Close notification">
        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
        </svg>
      </button>
    </div>
  </template>
</div>

<script>
function initNotification() {
    return {
        messages: [],
        severityTrans: {
          success: '{{ __('Success') }}',
          info: '{{ __('Info') }}',
          error: '{{ __('Error') }}',
          danger: '{{ __('Danger') }}',
          warning: '{{ __('Warning') }}',
        },
        remove(message) {
            this.messages.splice(this.messages.indexOf(message), 1)
        },
        displayMessage(message) {
            this.messages.push(message);
            setTimeout(() => { this.remove(message) }, 5000);
        },
        displaySuccess(successMessage) {
            let message = [successMessage, 'success'];
            this.messages.push(message);
            setTimeout(() => { this.remove(message) }, 5000);
        },
        displayError(errorMessage) {
            let message = [errorMessage, 'error'];
            this.messages.push(message);
            setTimeout(() => { this.remove(message) }, 10000);
        },
        displayWarning(warningMessage) {
            let message = [warningMessage, 'warning'];
            this.messages.push(message);
            setTimeout(() => { this.remove(message) }, 10000);
        },
        displayInfo(infoMessage) {
            let message = [infoMessage, 'info'];
            this.messages.push(message);
            setTimeout(() => { this.remove(message) }, 5000);
        }
    };
}

@if(session('global-handler-error'))
    setTimeout(() => {
        window.dispatchEvent(
            new CustomEvent('notifyerror', {
                detail: { message: "{{ __('Error occurred on update') }}" }
            })
        );
    }, 500);
@endif
</script>