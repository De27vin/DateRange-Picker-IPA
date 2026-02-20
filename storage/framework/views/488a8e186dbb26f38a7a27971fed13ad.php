<div>
    <div class="flex flex-col items-top justify-between">
        <div class="block_header w-full pb-4">
            <h3 class="title" id="message-heading">
                <?php echo app('translator')->get('Export Device-List'); ?>
            </h3>
            <p class="description pb-8 lg:pb-0">
                <?php echo app('translator')->get('Specify which data you want to export by dragging the corresponding fields into the «Export list» area. The order can also be defined by dragging the chosen fields.'); ?>
            </p>
        </div>

        <div class="w-full flex-row flex">
            <div class="flex-1 bg-white bg-opacity-20 shadow-lg py-4 px-8">
                <div class="flex justify-between items-center">
                    <h4 class="font-bold text-sm text-gray-400 mb-2"><?php echo e(__('Device list')); ?></h4>
                    <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['size' => 'sm','class' => 'px-4','wire:click' => 'moveAllDeviceFields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','class' => 'px-4','wire:click' => 'moveAllDeviceFields']); ?>
                        <?php echo app('translator')->get('Move All'); ?>
                        <span class="pl-4"><i class="f7-icons">chevron_right_2</i></span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
                </div>
                <div class="h-64 no-scrollbar overflow-y-auto border border-gray-300 shadow-md py-2 px-4">
                    <?php if (isset($component)) { $__componentOriginal45b40a4e89e20a1e412971889eb868bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45b40a4e89e20a1e412971889eb868bd = $attributes; } ?>
<?php $component = Asantibanez\LaravelBladeSortable\Components\Sortable::resolve(['group' => 'devices_for_export','name' => 'device_list'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('laravel-blade-sortable::sortable'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Asantibanez\LaravelBladeSortable\Components\Sortable::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-1 space-y-2','wire:onSortOrderChange.prevent' => 'handleOnSortOrderChanged','style' => 'min-height:20rem;']); ?>
                        <?php $__currentLoopData = $device_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $deviceItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if (isset($component)) { $__componentOriginalcd95b0b1952f6126a2da78bc617adc0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c = $attributes; } ?>
<?php $component = Asantibanez\LaravelBladeSortable\Components\SortableItem::resolve(['sortKey' => ''.e($deviceItem).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('laravel-blade-sortable::sortable-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Asantibanez\LaravelBladeSortable\Components\SortableItem::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'cursor-pointer bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 shadow border flex items-center justify-between']); ?>
                                <span class="font-bold text-sm opacity-60 group-hover:opacity-100"><?php echo e($initialList[$deviceItem]); ?></span>
                                <i class="f7-icons opacity-40 group-hover:opacity-100">circle_grid_3x3_fill</i>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c)): ?>
<?php $attributes = $__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c; ?>
<?php unset($__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcd95b0b1952f6126a2da78bc617adc0c)): ?>
<?php $component = $__componentOriginalcd95b0b1952f6126a2da78bc617adc0c; ?>
<?php unset($__componentOriginalcd95b0b1952f6126a2da78bc617adc0c); ?>
<?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45b40a4e89e20a1e412971889eb868bd)): ?>
<?php $attributes = $__attributesOriginal45b40a4e89e20a1e412971889eb868bd; ?>
<?php unset($__attributesOriginal45b40a4e89e20a1e412971889eb868bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45b40a4e89e20a1e412971889eb868bd)): ?>
<?php $component = $__componentOriginal45b40a4e89e20a1e412971889eb868bd; ?>
<?php unset($__componentOriginal45b40a4e89e20a1e412971889eb868bd); ?>
<?php endif; ?>
                </div>
            </div>

            <div class="flex h-80 justify-center items-center w-12">
                <div class="text-gray-600">
                    <i class="f7-icons text-gray-600 text-2xl">arrow_right_arrow_left</i>
                </div>
            </div>

            <div class="flex-1 bg-white bg-opacity-20 shadow-lg py-4 px-8">
                <div class="flex justify-between items-center">
                    <h4 class="font-bold text-sm text-gray-400 mb-2"><?php echo e(__('Export-List')); ?></h4>
                    <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['size' => 'sm','class' => 'px-4','wire:click' => 'resetExportList']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['size' => 'sm','class' => 'px-4','wire:click' => 'resetExportList']); ?>
                        <?php echo app('translator')->get('Reset'); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $attributes = $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a)): ?>
<?php $component = $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a; ?>
<?php unset($__componentOriginal8a31ff0802d1df0c26bb607f30439b3a); ?>
<?php endif; ?>
                </div>
                <div class="h-64 no-scrollbar overflow-y-auto border border-gray-300 shadow-md py-2 px-4">
                    <?php $__currentLoopData = $lockedFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lockedItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bg-white opacity-40 px-4 py-1 shadow border flex items-center justify-between">
                            <span class="font-bold text-sm"><?php echo e(__($lockedItem)); ?></span>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if (isset($component)) { $__componentOriginal45b40a4e89e20a1e412971889eb868bd = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal45b40a4e89e20a1e412971889eb868bd = $attributes; } ?>
<?php $component = Asantibanez\LaravelBladeSortable\Components\Sortable::resolve(['group' => 'devices_for_export','name' => 'export_list'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('laravel-blade-sortable::sortable'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Asantibanez\LaravelBladeSortable\Components\Sortable::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'flex-1 space-y-2','wire:onSortOrderChange.prevent' => 'handleOnSortOrderChanged','style' => 'min-height:20rem;']); ?>
                        <?php $__currentLoopData = $export_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exportItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(!in_array($exportItem, $lockedFields)): ?>
                                <?php if (isset($component)) { $__componentOriginalcd95b0b1952f6126a2da78bc617adc0c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c = $attributes; } ?>
<?php $component = Asantibanez\LaravelBladeSortable\Components\SortableItem::resolve(['sortKey' => ''.e($exportItem).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('laravel-blade-sortable::sortable-item'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Asantibanez\LaravelBladeSortable\Components\SortableItem::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'cursor-pointer bg-white group group-hover:bg-opacity-100 bg-opacity-60 px-4 py-1 shadow border flex items-center justify-between']); ?>
                                    <span class="font-bold text-sm opacity-60 group-hover:opacity-100"><?php echo e($initialList[$exportItem]); ?></span>
                                    <i class="f7-icons opacity-40 group-hover:opacity-100">circle_grid_3x3_fill</i>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c)): ?>
<?php $attributes = $__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c; ?>
<?php unset($__attributesOriginalcd95b0b1952f6126a2da78bc617adc0c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcd95b0b1952f6126a2da78bc617adc0c)): ?>
<?php $component = $__componentOriginalcd95b0b1952f6126a2da78bc617adc0c; ?>
<?php unset($__componentOriginalcd95b0b1952f6126a2da78bc617adc0c); ?>
<?php endif; ?>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal45b40a4e89e20a1e412971889eb868bd)): ?>
<?php $attributes = $__attributesOriginal45b40a4e89e20a1e412971889eb868bd; ?>
<?php unset($__attributesOriginal45b40a4e89e20a1e412971889eb868bd); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal45b40a4e89e20a1e412971889eb868bd)): ?>
<?php $component = $__componentOriginal45b40a4e89e20a1e412971889eb868bd; ?>
<?php unset($__componentOriginal45b40a4e89e20a1e412971889eb868bd); ?>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Field Presets Section -->
    <div class="bg-white bg-opacity-10 rounded-lg p-4">
        <div class="flex justify-between mb-4">
            <h4 class="font-bold text-sm text-gray-400"><?php echo app('translator')->get('Field Presets'); ?></h4>
        </div>

        <!-- Preset Controls - 4 States with fixed widths -->
        <div class="flex items-start align-start space-x-4 mb-4">
            <!-- Preset Dropdown (1/3 width, consistent height) -->
            <div class="w-1/3">
                <select 
                    class="w-full bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500 h-10"
                    wire:model="selectedPreset" 
                    wire:change="loadPreset($event.target.value)"
                >
                    <option value=""><?php echo app('translator')->get('Choose list item'); ?>...</option>
                    <?php $__currentLoopData = $presets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $presetId => $preset): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($presetId); ?>"><?php echo e($preset['name'] ?? 'Unnamed Preset'); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            <!-- State 1: Selected preset - show trash icon -->
            <?php if($this->canDeletePreset): ?>
                <div
                    wire:click="deletePreset('<?php echo e($selectedPreset); ?>')"
                    wire:confirm="<?php echo app('translator')->get('Are you sure you want to delete this preset?'); ?>"
                    class="cursor-pointer bg-white border border-gray-300 rounded text-gray-600 hover:text-red-600 hover:border-red-300 transition-colors h-10 w-10 flex items-center justify-center flex-shrink-0"
                    title="<?php echo app('translator')->get('Delete Preset'); ?>"
                    wire:key="delete-<?php echo e($selectedPreset); ?>"
                >
                    <i class="f7-icons text-lg">trash</i>
                </div>
            
            <!-- State 2: Fields selected but no matching preset exists - show save preset button -->
            <?php elseif($this->canSavePreset): ?>
                <div
                    wire:click="showSavePresetForm"
                    class="cursor-pointer bg-white border border-gray-300 rounded px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors h-10 flex-shrink-0"
                    wire:key="save-preset-btn"
                >
                    <?php echo app('translator')->get('Save preset'); ?>
                </div>
            <?php endif; ?>

            <!-- State 3: Saving new preset - show input and save/cancel buttons -->
            <?php if($showSavePreset): ?>
                <div class="w-1/3">
                    <input 
                        type="text"
                        wire:model="newPresetName" 
                        placeholder="<?php echo app('translator')->get('Preset name'); ?>"
                        maxlength="100"
                        style="background-color: white !important; padding-block: 0;"
                        class="p-0 w-full !bg-white border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-blue-500 h-10"
                    />
                </div>
            <div class="w-1/3 flex gap-2">
                <div
                        wire:click="savePreset"
                        class="cursor-pointer bg-white border border-gray-300 rounded px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors h-10 flex-shrink-0"
                >

                    Save
                </div>
                <div
                        wire:click="cancelSavePreset"
                        class="cursor-pointer bg-white border border-gray-300 rounded px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors h-10 flex-shrink-0"
                >
                    <?php echo app('translator')->get('Cancel'); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Error Display -->
        <?php if($presetError): ?>
            <div class="mb-4">
                <p class="text-sm text-red-800"><?php echo e($presetError); ?></p>
            </div>
        <?php endif; ?>
        
        <!-- Validation Error for Preset Name -->
        <?php if($showSavePreset): ?>
            <?php $__errorArgs = ['newPresetName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="mb-4">
                    <p class="text-sm text-red-800"><?php echo e($message); ?></p>
                </div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        <?php endif; ?>
    </div>

    <div class="flex justify-end mt-4">
        <div x-data="{ showFormat: false, polling: false, progress: 0, iframeRef: null }" 
             style="z-index: 10;" 
             class="relative"
             @start-download-csv.window="
                 const data = $event.detail;
                 const downloadId = data.download_id;
                 // Build and submit a hidden form that targets a hidden iframe so the page does not navigate
                 const iframe = document.createElement('iframe');
                 iframe.style.display = 'none';
                 iframe.name = `download_iframe_${downloadId}`;

                 // link iframe to Alpine state for later cleanup
                 this.iframeRef && this.iframeRef.remove(); // remove previous if any
                 this.iframeRef = iframe;
                 document.body.appendChild(iframe);

                 const form = document.createElement('form');
                 form.method = 'POST';
                 form.action = data.url;
                 form.style.display = 'none';
                 form.target = iframe.name;

                 // CSRF token (Laravel embeds this meta tag by default)
                 const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                 if (token) {
                     const csrfInput = document.createElement('input');
                     csrfInput.type = 'hidden';
                     csrfInput.name = '_token';
                     csrfInput.value = token;
                     form.appendChild(csrfInput);
                 }

                 // Append all payload fields
                 for (const [key, value] of Object.entries(data.params || {})) {
                     const input = document.createElement('input');
                     input.type = 'hidden';
                     input.name = key;
                     input.value = value;
                     form.appendChild(input);
                 }

                 document.body.appendChild(form);
                 form.submit();
                 document.body.removeChild(form);
                   
                   // Start polling for progress
                   polling = true;
                   progress = 0;
                   if (window.__exportProgressTimer) clearInterval(window.__exportProgressTimer);
                   window.__exportProgressTimer = setInterval(() => {
                       fetch(`${'<?php echo e(route('exportDevicesProgress')); ?>'}?id=${downloadId}`)
                           .then(r => r.json())
                           .then(d => {
                               if (d.progress === null || d.progress >= 100) {
                                   progress = 100;
                                   clearInterval(window.__exportProgressTimer);
                                   polling = false;

                                   // Clean up iframe once the backend finished writing the file
                                   if (this.iframeRef) {
                                       // give browser a short moment to start download
                                       setTimeout(() => {
                                           this.iframeRef.remove();
                                           this.iframeRef = null;
                                       }, 2000);
                                   }
                               } else {
                                   progress = d.progress;
                               }
                           })
                           .catch(() => {
                               clearInterval(window.__exportProgressTimer);
                               polling = false;
                           });
                   }, 500);
                "
              @start-export-excel.window="
                  const data = $event.detail;
                  const downloadId = data.download_id;

                  // Start async job via fetch POST
                  fetch(data.url, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/json',
                          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || ''
                      },
                      body: JSON.stringify(data.params)
                      ,credentials: 'same-origin'
                  }).then(r => r.json()).then(() => {
                      // begin polling
                      polling = true;
                      progress = 0;

                      if (window.__exportProgressTimer) clearInterval(window.__exportProgressTimer);
                      window.__exportProgressTimer = setInterval(() => {
                          fetch(`${'<?php echo e(route('exportDevicesProgress')); ?>'}?id=${downloadId}`)
                              .then(r => r.json())
                              .then(d => {
                                  if (d.ready) {
                                      progress = 100;
                                      clearInterval(window.__exportProgressTimer);
                                      polling = false;

                                      const iframe = document.createElement('iframe');
                                      iframe.style.display = 'none';
                                      iframe.src = `${'<?php echo e(url('/download/devices')); ?>'}/${downloadId}`;
                                      document.body.appendChild(iframe);

                                      setTimeout(() => iframe.remove(), 20000);
                                  } else {
                                      progress = d.progress ?? progress;
                                  }
                              })
                              .catch(() => {
                                  clearInterval(window.__exportProgressTimer);
                                  polling = false;
                              });
                      }, 1000);
                  });
              "
        >
            <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['xOn:click' => 'showFormat = true']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => 'showFormat = true']); ?>
                <?php echo app('translator')->get('export'); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>

            <!-- Format Selection Dropdown -->
            <div
                x-show="showFormat"
                x-on:click.away="showFormat = false"
                class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
            >
                <div class="py-1">
                    <button
                        wire:click.prevent="doExportDevices('csv')"
                        x-on:click="showFormat=false"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700"
                    >
                        CSV
                    </button>
                    <button
                        wire:click.prevent="doExportDevices('xlsx')"
                        x-on:click="showFormat=false"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700"
                    >
                        Excel (XLSX)
                    </button>
                </div>
            </div>

            <!-- Progress Bar -->
            <div x-show="polling" x-cloak>
                <div class="absolute bottom-4 right-4 w-48 bg-white rounded-lg shadow-lg p-4 border z-50">
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium"><?php echo app('translator')->get('Exporting Devices'); ?>...</span>
                            <template x-if="progress >= 100">
                                <span class="text-green-500">✓</span>
                            </template>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                                 :style="`width: ${progress}%`">
                            </div>
                        </div>
                        <span class="text-xs text-gray-500" x-text="`${progress}% complete`"></span>
                    </div>
                </div>
            </div>
        </div>

        <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['class' => 'ml-4','xOn:click' => '$dispatch(\'dropdown-select\', { element: \'\' })']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'ml-4','x-on:click' => '$dispatch(\'dropdown-select\', { element: \'\' })']); ?>
            <?php echo app('translator')->get('cancel'); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $attributes = $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $component = $__componentOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
    </div>

</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/ucp/export-devices-new.blade.php ENDPATH**/ ?>