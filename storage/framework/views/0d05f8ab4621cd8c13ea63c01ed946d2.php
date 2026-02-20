
<div>

    <?php uasort($deviceSiteSettingsProgrammable, fn ($a, $b) => strcmp(strtolower($a['translation']), strtolower($b['translation']))); ?>
    <?php uasort($deviceSiteSettingsNonProgrammable, fn ($a, $b) => strcmp(strtolower($a['translation']), strtolower($b['translation']))); ?>

    <?php if (isset($component)) { $__componentOriginalc4b02112ae79d0716caa6bfaca02830d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc4b02112ae79d0716caa6bfaca02830d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.tabs-secondary','data' => ['target' => $deviceSite,'defaultTab' => 'customFields','tabs' => [
            'customFields' => __('Custom Fields'),
            'programmableSettings' => __('Programmable settings'),
            'nonProgrammableSettings' => __('Advanced settings'),
        ],'verticalSpace' => true,'buttonsOnTop' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.tabs-secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['target' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deviceSite),'defaultTab' => 'customFields','tabs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            'customFields' => __('Custom Fields'),
            'programmableSettings' => __('Programmable settings'),
            'nonProgrammableSettings' => __('Advanced settings'),
        ]),'verticalSpace' => true,'buttonsOnTop' => true]); ?>x
         <?php $__env->slot('customFieldsButtons', null, []); ?> 
            <?php if($canWriteSettings && count($deviceSiteCustomFields)): ?>
                <div class="flex w-full justify-end gap-2">
                    <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['wire:click' => 'updateDeviceSiteCustomFields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'updateDeviceSiteCustomFields']); ?><?php echo app('translator')->get('Update'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['type' => 'button','onclick' => 'window.dispatchEvent(new CustomEvent(\'closeModal_siteSettingsCustomFields\'))']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','onclick' => 'window.dispatchEvent(new CustomEvent(\'closeModal_siteSettingsCustomFields\'))']); ?><?php echo app('translator')->get('Cancel'); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php endif; ?>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('programmableSettingsButtons', null, []); ?> 
            <?php if(count($deviceSiteSettingsProgrammable)): ?>
                <div class="flex w-full justify-end gap-2">
                    <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['wire:click' => 'updateProgrammableSettings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'updateProgrammableSettings']); ?><?php echo app('translator')->get('update'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['type' => 'button','onclick' => 'window.dispatchEvent(new CustomEvent(\'closeModal_siteSettingsCustomFields\'))']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','onclick' => 'window.dispatchEvent(new CustomEvent(\'closeModal_siteSettingsCustomFields\'))']); ?><?php echo app('translator')->get('cancel'); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php endif; ?>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('nonProgrammableSettingsButtons', null, []); ?> 
            <?php if(count($deviceSiteSettingsNonProgrammable)): ?>
                <div class="flex w-full justify-end gap-2">
                    <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['wire:click' => 'updateNonProgrammableSettings']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:click' => 'updateNonProgrammableSettings']); ?><?php echo app('translator')->get('update'); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['type' => 'button','onclick' => 'window.dispatchEvent(new CustomEvent(\'closeModal_siteSettingsCustomFields\'))']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','onclick' => 'window.dispatchEvent(new CustomEvent(\'closeModal_siteSettingsCustomFields\'))']); ?><?php echo app('translator')->get('cancel'); ?> <?php echo $__env->renderComponent(); ?>
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
            <?php endif; ?>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('customFieldsSlot', null, []); ?> 
            <form wire:submit.prevent="updateDeviceSiteCustomFields">

                <div class="md:flex flex-wrap mb-0">
                    <div class="block w-full">
                        <div class="ml-8 justify-between pb-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4">

                            <?php $__empty_1 = true; $__currentLoopData = $deviceSiteCustomFields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customKey => $customField): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php if (isset($component)) { $__componentOriginal13e63f775a4f5c3981da7e10d0c76968 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal13e63f775a4f5c3981da7e10d0c76968 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.grid-group','data' => ['class' => 'mb-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms.grid-group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4']); ?>
                                    <?php if (isset($component)) { $__componentOriginal1f715251ca27813040dd69c48bb81eec = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1f715251ca27813040dd69c48bb81eec = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.label','data' => ['for' => ''.e($customKey).'','fallback' => '']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms.label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => ''.e($customKey).'','fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('')]); ?>
                                        <?php echo e(!empty($customField['translations'][session('locale', 'default')]) ? $customField['translations'][session('locale', 'default')] : $customField['name']); ?>

                                        <?php if(!empty($customField['required'])): ?>
                                            <?php if (isset($component)) { $__componentOriginalae99970fd62e84331b2c998b40bc7fbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae99970fd62e84331b2c998b40bc7fbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.monoicon.required','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('monoicon.required'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalae99970fd62e84331b2c998b40bc7fbc)): ?>
<?php $attributes = $__attributesOriginalae99970fd62e84331b2c998b40bc7fbc; ?>
<?php unset($__attributesOriginalae99970fd62e84331b2c998b40bc7fbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalae99970fd62e84331b2c998b40bc7fbc)): ?>
<?php $component = $__componentOriginalae99970fd62e84331b2c998b40bc7fbc; ?>
<?php unset($__componentOriginalae99970fd62e84331b2c998b40bc7fbc); ?>
<?php endif; ?>
                                        <?php endif; ?>
                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1f715251ca27813040dd69c48bb81eec)): ?>
<?php $attributes = $__attributesOriginal1f715251ca27813040dd69c48bb81eec; ?>
<?php unset($__attributesOriginal1f715251ca27813040dd69c48bb81eec); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1f715251ca27813040dd69c48bb81eec)): ?>
<?php $component = $__componentOriginal1f715251ca27813040dd69c48bb81eec; ?>
<?php unset($__componentOriginal1f715251ca27813040dd69c48bb81eec); ?>
<?php endif; ?>
                                    <?php if (isset($component)) { $__componentOriginald577c7dec18d40f4620a29a9f4a40645 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald577c7dec18d40f4620a29a9f4a40645 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.text','data' => ['wire:model.defer' => 'deviceSiteCustomFields.'.e($customKey).'.value','canWriteSettings' => ''.e($canWriteSettings).'','type' => 'text','name' => ''.e($customKey).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.text'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.defer' => 'deviceSiteCustomFields.'.e($customKey).'.value','canWriteSettings' => ''.e($canWriteSettings).'','type' => 'text','name' => ''.e($customKey).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald577c7dec18d40f4620a29a9f4a40645)): ?>
<?php $attributes = $__attributesOriginald577c7dec18d40f4620a29a9f4a40645; ?>
<?php unset($__attributesOriginald577c7dec18d40f4620a29a9f4a40645); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald577c7dec18d40f4620a29a9f4a40645)): ?>
<?php $component = $__componentOriginald577c7dec18d40f4620a29a9f4a40645; ?>
<?php unset($__componentOriginald577c7dec18d40f4620a29a9f4a40645); ?>
<?php endif; ?>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal13e63f775a4f5c3981da7e10d0c76968)): ?>
<?php $attributes = $__attributesOriginal13e63f775a4f5c3981da7e10d0c76968; ?>
<?php unset($__attributesOriginal13e63f775a4f5c3981da7e10d0c76968); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal13e63f775a4f5c3981da7e10d0c76968)): ?>
<?php $component = $__componentOriginal13e63f775a4f5c3981da7e10d0c76968; ?>
<?php unset($__componentOriginal13e63f775a4f5c3981da7e10d0c76968); ?>
<?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <p class="col-span-4 text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400"><?php echo app('translator')->get('No custom fields are configured for Sites.'); ?></p>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

            </form>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('programmableSettingsSlot', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginal42aa015f34a74d2082d1ce7528ed9eaa = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal42aa015f34a74d2082d1ce7528ed9eaa = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.programmable-settings','data' => ['programmableSettings' => $deviceSiteSettingsProgrammable,'updateMethodName' => 'updateProgrammableSetting','model' => 'deviceSiteSettingsProgrammable','target' => $deviceSite]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('settings.programmable-settings'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['programmableSettings' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deviceSiteSettingsProgrammable),'updateMethodName' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('updateProgrammableSetting'),'model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('deviceSiteSettingsProgrammable'),'target' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deviceSite)]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal42aa015f34a74d2082d1ce7528ed9eaa)): ?>
<?php $attributes = $__attributesOriginal42aa015f34a74d2082d1ce7528ed9eaa; ?>
<?php unset($__attributesOriginal42aa015f34a74d2082d1ce7528ed9eaa); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal42aa015f34a74d2082d1ce7528ed9eaa)): ?>
<?php $component = $__componentOriginal42aa015f34a74d2082d1ce7528ed9eaa; ?>
<?php unset($__componentOriginal42aa015f34a74d2082d1ce7528ed9eaa); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

         <?php $__env->slot('nonProgrammableSettingsSlot', null, []); ?> 
            <?php if (isset($component)) { $__componentOriginalc11e57ad37781cc70c61bbed79e9d59a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc11e57ad37781cc70c61bbed79e9d59a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.advanced-settings','data' => ['advancedSettings' => $deviceSiteSettingsNonProgrammable,'updateMethodName' => 'updateNonProgrammableSettings','model' => 'deviceSiteSettingsNonProgrammable']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('settings.advanced-settings'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['advancedSettings' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($deviceSiteSettingsNonProgrammable),'updateMethodName' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('updateNonProgrammableSettings'),'model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('deviceSiteSettingsNonProgrammable')]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc11e57ad37781cc70c61bbed79e9d59a)): ?>
<?php $attributes = $__attributesOriginalc11e57ad37781cc70c61bbed79e9d59a; ?>
<?php unset($__attributesOriginalc11e57ad37781cc70c61bbed79e9d59a); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc11e57ad37781cc70c61bbed79e9d59a)): ?>
<?php $component = $__componentOriginalc11e57ad37781cc70c61bbed79e9d59a; ?>
<?php unset($__componentOriginalc11e57ad37781cc70c61bbed79e9d59a); ?>
<?php endif; ?>
         <?php $__env->endSlot(); ?>

     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc4b02112ae79d0716caa6bfaca02830d)): ?>
<?php $attributes = $__attributesOriginalc4b02112ae79d0716caa6bfaca02830d; ?>
<?php unset($__attributesOriginalc4b02112ae79d0716caa6bfaca02830d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc4b02112ae79d0716caa6bfaca02830d)): ?>
<?php $component = $__componentOriginalc4b02112ae79d0716caa6bfaca02830d; ?>
<?php unset($__componentOriginalc4b02112ae79d0716caa6bfaca02830d); ?>
<?php endif; ?>

    <script>
        window.siteSettingsLivewireId = '<?php echo e($this->id); ?>';
    </script>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/ucp/site-settings-custom-fields.blade.php ENDPATH**/ ?>