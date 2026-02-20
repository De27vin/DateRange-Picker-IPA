

<div id="popup-<?php echo e($popupId); ?>" class="popup" style="display: none;">
    <div class="popup-content">
        <div class="bottom-underline h-12">
            <span id="popup-title-1-<?php echo e($popupId); ?>" class="popup-title-1 text-lg"></span>
            <span id="popup-title-2-<?php echo e($popupId); ?>" class="popup-title-2 text-lg ml-2"></span>
            <span id="popup-close-<?php echo e($popupId); ?>" class="popup-close">&times;</span>
        </div>
        <?php echo e($slot); ?>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        let modal = document.getElementById("popup-<?php echo e($popupId); ?>");
        let closeBtn = modal.querySelector("#popup-close-<?php echo e($popupId); ?>");
        let titleElement1 = modal.querySelector("#popup-title-1-<?php echo e($popupId); ?>");
        let titleElement2 = modal.querySelector("#popup-title-2-<?php echo e($popupId); ?>");
        let modalOpen = false
        let isDragging = false
        let popupContent = modal.querySelector(".popup-content")

        if ('<?php echo e($popupId); ?>' === 'deviceSettingsCustomFields'){
            window.addEventListener("openModal_deviceSettingsCustomFields", function() {
                modalOpen = true;
                const deviceId = event.detail.deviceId;
                const deviceEquipment = event.detail.deviceEquipment;

               titleElement1.innerText = "<?php echo e(__('Device options')); ?>";
               titleElement2.innerText = "<?php echo e(__('Equipment')); ?>" + ": " + event.detail.deviceEquipment;

                Livewire.find(window.deviceSettingsLivewireId).call('setDeviceId', deviceId);

                Livewire.hook('message.processed', (message, component) => {
                    if (component.id === window.deviceSettingsLivewireId && modalOpen) {
                        modal.style.display = "block";
                        document.body.classList.add('modal-open');
                    }
                });
            });
        }

        if ('<?php echo e($popupId); ?>' === 'siteSettingsCustomFields'){
            window.addEventListener("openModal_siteSettingsCustomFields", function() {
                modalOpen = true;
                const siteId = event.detail.siteId;
                const siteName = event.detail.siteName;

               titleElement1.innerText = "<?php echo e(__('Site options')); ?>";
               titleElement2.innerText = "<?php echo e(__('Site')); ?>" + ": " + event.detail.siteName;

                Livewire.find(window.siteSettingsLivewireId).call('setDeviceSiteId', siteId);

                Livewire.hook('message.processed', (message, component) => {
                    if (component.id === window.siteSettingsLivewireId && modalOpen) {
                        modal.style.display = "block";
                        document.body.classList.add('modal-open');
                    }
                });
            });
        }

        if ('<?php echo e($popupId); ?>' === 'cliConfirmationModal') {
            window.addEventListener("openModal_cliConfirmationModal", function(event) {
                modalOpen = true;
                const newNumber = event.detail.newNumber;
                const cliNumber = event.detail.cliNumber;
                const cliNumberLevel = event.detail.cliNumberLevel;

                titleElement1.innerText = "<?php echo e(__('Update CLI Setting')); ?>";
                titleElement2.innerText = "";

                modal.querySelector('#old-number-display').innerText = cliNumber + (cliNumberLevel ? ' (Setting level: ' + cliNumberLevel + ')' : '');
                modal.querySelector('#new-number-display').innerText = newNumber;

                modal.style.display = "block";
                document.body.classList.add('modal-open');

                Livewire.hook('message.processed', (message, component) => {
                    if (modalOpen) {
                        modal.style.display = "block";
                        document.body.classList.add('modal-open');
                    }
                });
            });
        }

        function closeModal() {
            modal.style.display = "none";
            document.body.classList.remove('modal-open');
            modalOpen = false;
        }

        closeBtn.onclick = function() {
            console.log('button closing modal with id - <?php echo e($popupId); ?>');
            closeModal();
        };

        // Track drag selection to prevent modal closing during text selection
        popupContent.addEventListener('mousedown', function(event) {
            isDragging = false;
        });

        popupContent.addEventListener('mousemove', function(event) {
            if (event.buttons === 1) { // Left mouse button is pressed
                isDragging = true;
            }
        });

        document.addEventListener('mouseup', function(event) {
            // Reset dragging state after a short delay to allow the click event to use the current state
            setTimeout(() => {
                isDragging = false;
            }, 50);
        });

        modal.onclick = function(event) {
            if (event.target === modal && !isDragging) {
                console.log('click closing modal with id - <?php echo e($popupId); ?>');
                closeModal();
            }
        };

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                console.log('escape key closing modal with id - <?php echo e($popupId); ?>');
                closeModal();
            }
        });

        window.addEventListener("closeModal_<?php echo e($popupId); ?>", function() {
            console.log('Programmatically closing modal with id - <?php echo e($popupId); ?>');
            closeModal();
        });
    });

</script>


<style>
.popup {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5); /* Dimmed background */
}

.popup-content {
  background-color: white;
  margin: 20vh auto; /* Centering and allowing modal to start from 20% from top */
  padding: 20px;
  border: 1px solid #888;
  width: 80%;
  max-height: 100vh; /* Modal height limited to 80% of viewport height */
  overflow-y: auto; /* Add scrollbar if content exceeds modal height */
  position: relative; /* Ensure content stays within the modal */
  z-index: 10000;
}

.popup-title-1 {
  float: left;
  text-transform: uppercase;
}

.popup-title-2 {
  float: left;
}

.popup-close {
  color: #aaa;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.popup-close:hover,
.popup-close:focus {
  color: black;
  cursor: pointer;
}


</style><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/modal/new-popup.blade.php ENDPATH**/ ?>