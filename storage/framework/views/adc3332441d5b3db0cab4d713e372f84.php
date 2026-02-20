<script>

    document.addEventListener('DOMContentLoaded', () => {
        this.livewire.hook('message.sent', () => {
            window.dispatchEvent(
                new CustomEvent('loading', { detail: { action: 'livewire', loading: true }})
            );
        } )
        this.livewire.hook('message.processed', (message, component) => {
            window.dispatchEvent(
                new CustomEvent('loading', { detail: { action: 'livewire', loading: false }})
            );
        })
        this.livewire.hook('message.failed', (message, component) => {
            window.dispatchEvent(
                new CustomEvent('loading', { detail: { action: 'livewire', loading: false }})
            );
            window.dispatchEvent(
                new CustomEvent('notifyerror', { detail: { message: "<?php echo e(__('Error occurred on update')); ?>" }})
            );
        })
    });

</script>

<div x-data="{ loading: false }" x-show="loading" @loading.window="loading = $event.detail.loading;" style="display: none;">

    <div class="fixed top-0 w-96 right-0 flex flex-col items-end justify-end pr-4 pt-4 pointer-events-none" style="z-index:10000;">
        <div class="w-full max-w-sm bg-white pointer-events-auto overflow-hidden shadow-lg rounded-md border-l-4 border-color-new">

            <div class="p-4 bg-white border-l-2 border-color-new flex justify-between items-center">
                <div class="pt-1">
                    <p class="text-base leading-5 text-medium"><?php echo e(__('Loading')); ?>...</p>
                </div>

                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-surface motion-reduce:animate-[spin_1s_linear_infinite] text-color-new" role="status">
                    <div class="loader ease-linear rounded-full border-8 border-t-8 border-gray-200 h-8 w-8 mb-4"></div>
                </div>

            </div>
        </div>
    </div>

</div>

<style>
    .loader {
        border-top-color: rgb(59, 130, 246);
        -webkit-animation: spinner 2s linear infinite;
        animation: spinner 2s linear infinite;
    }

    @-webkit-keyframes spinner {
        0% {
            -webkit-transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spinner {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/page/loading-indicator.blade.php ENDPATH**/ ?>