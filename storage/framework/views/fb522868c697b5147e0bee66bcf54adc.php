    <div class="relative justify-center" style="margin-top: 16rem;">
        <?php if($hasUpdate): ?>
            <div class="absolute shadow-lg inset-x-0 p-16" style="background-color: #a05050; z-index:1000;">
                <div class="w-full h-20 pt-6 bg-transparent flex justify-center">
                    <div wire:loading class="ml-10"><?php if (isset($component)) { $__componentOriginal51b515460e0e765151b6dec99bdf8868 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal51b515460e0e765151b6dec99bdf8868 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.monoicon.loading-indicator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('monoicon.loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal51b515460e0e765151b6dec99bdf8868)): ?>
<?php $attributes = $__attributesOriginal51b515460e0e765151b6dec99bdf8868; ?>
<?php unset($__attributesOriginal51b515460e0e765151b6dec99bdf8868); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal51b515460e0e765151b6dec99bdf8868)): ?>
<?php $component = $__componentOriginal51b515460e0e765151b6dec99bdf8868; ?>
<?php unset($__componentOriginal51b515460e0e765151b6dec99bdf8868); ?>
<?php endif; ?></div>
                </div>
                <div class="w-full my-8 text-center text-2xl text-white">
                    <?php echo e(__('There are updates pending for ucp.')); ?>

                </div>
                <div class="w-full my-8 text-center">
                    <?php if (isset($component)) { $__componentOriginal8a31ff0802d1df0c26bb607f30439b3a = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8a31ff0802d1df0c26bb607f30439b3a = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.button','data' => ['color' => 'danger','class' => 'ml-4','wire:click.prevent' => 'startUpdate']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'danger','class' => 'ml-4','wire:click.prevent' => 'startUpdate']); ?>
                        <?php echo app('translator')->get('Start Update'); ?>
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
            </div>
        <?php endif; ?>
        <div class=" <?php if($hasUpdate): ?>relative blur-lg cursor-not-allowed pointer-events-none opacity-20  <?php endif; ?>">
            <?php if (isset($component)) { $__componentOriginalb9468a5a236188da95d7472adf747435 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalb9468a5a236188da95d7472adf747435 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.card','data' => ['hasUpdate' => $hasUpdate,'class' => 'max-w-4xl mx-auto']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth.card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['hasUpdate' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($hasUpdate),'class' => 'max-w-4xl mx-auto']); ?>
                 <?php $__env->slot('title', null, []); ?> <?php echo app('translator')->get('Account Login'); ?> <?php $__env->endSlot(); ?>
                <!-- Session Status -->
                <?php if (isset($component)) { $__componentOriginal80fca99ef57b8a84e8d8dfac555784dc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal80fca99ef57b8a84e8d8dfac555784dc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.session-status','data' => ['class' => 'mb-4 text-color-new-600','status' => session('status')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth.session-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 text-color-new-600','status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(session('status'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal80fca99ef57b8a84e8d8dfac555784dc)): ?>
<?php $attributes = $__attributesOriginal80fca99ef57b8a84e8d8dfac555784dc; ?>
<?php unset($__attributesOriginal80fca99ef57b8a84e8d8dfac555784dc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal80fca99ef57b8a84e8d8dfac555784dc)): ?>
<?php $component = $__componentOriginal80fca99ef57b8a84e8d8dfac555784dc; ?>
<?php unset($__componentOriginal80fca99ef57b8a84e8d8dfac555784dc); ?>
<?php endif; ?>
                    <!-- Validation Errors -->
                    <?php if (isset($component)) { $__componentOriginal26e94262b7a35104ec5e89bdc0428d3d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal26e94262b7a35104ec5e89bdc0428d3d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.auth.validation-errors','data' => ['class' => 'mb-4 text-danger-600','errors' => $errors]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('auth.validation-errors'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 text-danger-600','errors' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($errors)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal26e94262b7a35104ec5e89bdc0428d3d)): ?>
<?php $attributes = $__attributesOriginal26e94262b7a35104ec5e89bdc0428d3d; ?>
<?php unset($__attributesOriginal26e94262b7a35104ec5e89bdc0428d3d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal26e94262b7a35104ec5e89bdc0428d3d)): ?>
<?php $component = $__componentOriginal26e94262b7a35104ec5e89bdc0428d3d; ?>
<?php unset($__componentOriginal26e94262b7a35104ec5e89bdc0428d3d); ?>
<?php endif; ?>
                    <div>
                        <div class="row">
                            <div class="col-md-12">
                                <?php if(session()->has('message')): ?>
                                    <div class="alert alert-success">
                                        <?php echo e(session('message')); ?>

                                    </div>
                                <?php endif; ?>
                                <?php if(session()->has('error')): ?>
                                    <div class="alert alert-danger">
                                        <?php echo e(session('error')); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <form wire:submit.prevent="login">
                            <div class="row">
                                <div class="col-md-12 my-4">
                                    <div class="relative w-full mx-auto mt-4">


                                        <?php if (isset($component)) { $__componentOriginald8ba2b4c22a13c55321e34443c386276 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8ba2b4c22a13c55321e34443c386276 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.label','data' => ['class' => 'default','for' => 'email','required' => true,'value' => __('Email address')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'default','for' => 'email','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Email address'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $attributes = $__attributesOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__attributesOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $component = $__componentOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__componentOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
                                        <input id="email" class="block mt-1 w-full" type="email" wire:model.defer="email"></input>
                                    </div>
                                </div>
                                <div class="col-md-12 my-4">
                                    <div class="relative w-full mx-auto mt-4">
                                        <?php if (isset($component)) { $__componentOriginald8ba2b4c22a13c55321e34443c386276 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald8ba2b4c22a13c55321e34443c386276 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.label','data' => ['class' => 'default','for' => 'password','required' => true,'value' => __('Password')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('label'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'default','for' => 'password','required' => true,'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(__('Password'))]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $attributes = $__attributesOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__attributesOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald8ba2b4c22a13c55321e34443c386276)): ?>
<?php $component = $__componentOriginald8ba2b4c22a13c55321e34443c386276; ?>
<?php unset($__componentOriginald8ba2b4c22a13c55321e34443c386276); ?>
<?php endif; ?>
                                        <input class="block mt-1 w-full" type="password" wire:model.defer="password"></input>
                                    </div>
                                </div>

                                <div class="flex w-full mt-4 items-center justify-end">
                                    <input wire:model.defer="remember_me" class="uiswitch uiswitch-new" name="remember_me" id="remember_me" type="checkbox" checked="checked"/>
                                    <label class="text-sm ml-2" for="remember_me">
                                        <?php echo e(__('Remember me')); ?>

                                    </label>
                                </div>
                                <div class="flex w-full mt-4">
                                    <div class="flex w-1/2  items-center justify-start">
                                        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit','class' => 'bg-color-new-400 text-white hover:bg-color-new-600 text-sm']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','class' => 'bg-color-new-400 text-white hover:bg-color-new-600 text-sm']); ?>
                                            <?php echo e(__('Log in')); ?>

                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                                    </div>
                                    <div class="flex w-1/2 items-center justify-end">
                                        <?php if(Route::has('password.request')): ?>
                                            <a type="button" class="text-sm bg-gray-400 text-white hover:bg-gray-600" href="<?php echo e(route('password.request')); ?>">
                                                <?php echo e(__('Forgot password')); ?> ...
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalb9468a5a236188da95d7472adf747435)): ?>
<?php $attributes = $__attributesOriginalb9468a5a236188da95d7472adf747435; ?>
<?php unset($__attributesOriginalb9468a5a236188da95d7472adf747435); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalb9468a5a236188da95d7472adf747435)): ?>
<?php $component = $__componentOriginalb9468a5a236188da95d7472adf747435; ?>
<?php unset($__componentOriginalb9468a5a236188da95d7472adf747435); ?>
<?php endif; ?>
        </div>
    </div>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/auth/login.blade.php ENDPATH**/ ?>