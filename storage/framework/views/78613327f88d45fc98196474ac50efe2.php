<div class="mx-auto pb-12 px-4 mt-8">

    <?php if (isset($component)) { $__componentOriginal4a29ad41492c717286123b97d5ba8cca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a29ad41492c717286123b97d5ba8cca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.header','data' => ['class' => 'h-24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-24']); ?>
         <?php $__env->slot('title', null, []); ?> <?php echo app('translator')->get('Change Password'); ?> <?php $__env->endSlot(); ?>
         <?php $__env->slot('description', null, []); ?> <?php echo app('translator')->get('Change your access password to the service'); ?> <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a29ad41492c717286123b97d5ba8cca)): ?>
<?php $attributes = $__attributesOriginal4a29ad41492c717286123b97d5ba8cca; ?>
<?php unset($__attributesOriginal4a29ad41492c717286123b97d5ba8cca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a29ad41492c717286123b97d5ba8cca)): ?>
<?php $component = $__componentOriginal4a29ad41492c717286123b97d5ba8cca; ?>
<?php unset($__componentOriginal4a29ad41492c717286123b97d5ba8cca); ?>
<?php endif; ?>

    <div class="w-full pt-2">
        <fieldset class="-mx-1 mb-1">

            <div class="flex w-full">
                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                    <?php if (isset($component)) { $__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.group','data' => ['for' => 'oldPassword','label' => ''.e(__('Current Password')).'','required' => 'required','error' => $errors->first('oldPassword')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'oldPassword','label' => ''.e(__('Current Password')).'','required' => 'required','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('oldPassword'))]); ?>
                        <?php if (isset($component)) { $__componentOriginalfed4f127db5cbb2597dfb22360abb47d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfed4f127db5cbb2597dfb22360abb47d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.password','data' => ['wire:model.defer' => 'oldPassword','class' => 'w-full','required' => 'required']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.password'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.defer' => 'oldPassword','class' => 'w-full','required' => 'required']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfed4f127db5cbb2597dfb22360abb47d)): ?>
<?php $attributes = $__attributesOriginalfed4f127db5cbb2597dfb22360abb47d; ?>
<?php unset($__attributesOriginalfed4f127db5cbb2597dfb22360abb47d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfed4f127db5cbb2597dfb22360abb47d)): ?>
<?php $component = $__componentOriginalfed4f127db5cbb2597dfb22360abb47d; ?>
<?php unset($__componentOriginalfed4f127db5cbb2597dfb22360abb47d); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d)): ?>
<?php $attributes = $__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d; ?>
<?php unset($__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d)): ?>
<?php $component = $__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d; ?>
<?php unset($__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d); ?>
<?php endif; ?>
                </div>
                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                    <?php if (isset($component)) { $__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.group','data' => ['for' => 'newPassword','label' => ''.e(__('New Password')).'','required' => 'required','error' => $errors->first('newPassword')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'newPassword','label' => ''.e(__('New Password')).'','required' => 'required','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('newPassword'))]); ?>
                        <?php if (isset($component)) { $__componentOriginalfed4f127db5cbb2597dfb22360abb47d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfed4f127db5cbb2597dfb22360abb47d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.password','data' => ['wire:model.defer' => 'newPassword','class' => 'w-full','required' => 'required']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.password'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.defer' => 'newPassword','class' => 'w-full','required' => 'required']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfed4f127db5cbb2597dfb22360abb47d)): ?>
<?php $attributes = $__attributesOriginalfed4f127db5cbb2597dfb22360abb47d; ?>
<?php unset($__attributesOriginalfed4f127db5cbb2597dfb22360abb47d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfed4f127db5cbb2597dfb22360abb47d)): ?>
<?php $component = $__componentOriginalfed4f127db5cbb2597dfb22360abb47d; ?>
<?php unset($__componentOriginalfed4f127db5cbb2597dfb22360abb47d); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d)): ?>
<?php $attributes = $__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d; ?>
<?php unset($__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d)): ?>
<?php $component = $__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d; ?>
<?php unset($__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d); ?>
<?php endif; ?>
                </div>
                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0">
                    <?php if (isset($component)) { $__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.group','data' => ['for' => 'newPasswordConfirm','label' => ''.e(__('Confirm New Password')).'','required' => 'required','error' => $errors->first('newPasswordConfirm')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['for' => 'newPasswordConfirm','label' => ''.e(__('Confirm New Password')).'','required' => 'required','error' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors->first('newPasswordConfirm'))]); ?>
                        <?php if (isset($component)) { $__componentOriginalfed4f127db5cbb2597dfb22360abb47d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfed4f127db5cbb2597dfb22360abb47d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.input.password','data' => ['wire:model.defer' => 'newPasswordConfirm','class' => 'w-full','required' => 'required']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('input.password'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:model.defer' => 'newPasswordConfirm','class' => 'w-full','required' => 'required']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfed4f127db5cbb2597dfb22360abb47d)): ?>
<?php $attributes = $__attributesOriginalfed4f127db5cbb2597dfb22360abb47d; ?>
<?php unset($__attributesOriginalfed4f127db5cbb2597dfb22360abb47d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfed4f127db5cbb2597dfb22360abb47d)): ?>
<?php $component = $__componentOriginalfed4f127db5cbb2597dfb22360abb47d; ?>
<?php unset($__componentOriginalfed4f127db5cbb2597dfb22360abb47d); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d)): ?>
<?php $attributes = $__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d; ?>
<?php unset($__attributesOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d)): ?>
<?php $component = $__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d; ?>
<?php unset($__componentOriginal2f3d4c0d4af7bf8e6f9db4822e3fcd7d); ?>
<?php endif; ?>
                </div>

                <div class="relative w-full md:w-1/2 lg:w-1/4 px-1 mb-1 md:mb-0 flex items-center justify-end">
                    <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['color' => 'primary','type' => 'button','wire:click' => 'changePassword']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('primary'),'type' => 'button','wire:click' => 'changePassword']); ?><?php echo e(__('Change Password')); ?> <?php echo $__env->renderComponent(); ?>
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
            </div>

        </fieldset>

    </div>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/user/change-password.blade.php ENDPATH**/ ?>