<!doctype html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php if (isset($component)) { $__componentOriginal774c089a9a8f84c2d0749a595e46c89d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal774c089a9a8f84c2d0749a595e46c89d = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.meta','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.meta'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal774c089a9a8f84c2d0749a595e46c89d)): ?>
<?php $attributes = $__attributesOriginal774c089a9a8f84c2d0749a595e46c89d; ?>
<?php unset($__attributesOriginal774c089a9a8f84c2d0749a595e46c89d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal774c089a9a8f84c2d0749a595e46c89d)): ?>
<?php $component = $__componentOriginal774c089a9a8f84c2d0749a595e46c89d; ?>
<?php unset($__componentOriginal774c089a9a8f84c2d0749a595e46c89d); ?>
<?php endif; ?>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Styles -->
        <link rel="stylesheet" type="text/css" href="/assets/themes/system/css/style.css">
        <link href="<?php echo e(asset('assets/css/app.css')); ?>" rel="stylesheet">
        <?php echo $__env->yieldPushContent('style'); ?>
    </head>
    <body>
        <div id="app" class="bg-gray-200">
            <main class="flex lg:items-center  min-h-screen lg:justify-center ">
                <?php echo e($slot); ?>

            </main>
        </div>

        <?php echo \Livewire\Livewire::scripts(); ?>

        
        <?php if (isset($component)) { $__componentOriginalc19e31d817540d5affe5f7c74827c6a1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalc19e31d817540d5affe5f7c74827c6a1 = $attributes; } ?>
<?php $component = Asantibanez\LaravelBladeSortable\Components\Scripts::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('laravel-blade-sortable::scripts'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Asantibanez\LaravelBladeSortable\Components\Scripts::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalc19e31d817540d5affe5f7c74827c6a1)): ?>
<?php $attributes = $__attributesOriginalc19e31d817540d5affe5f7c74827c6a1; ?>
<?php unset($__attributesOriginalc19e31d817540d5affe5f7c74827c6a1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalc19e31d817540d5affe5f7c74827c6a1)): ?>
<?php $component = $__componentOriginalc19e31d817540d5affe5f7c74827c6a1; ?>
<?php unset($__componentOriginalc19e31d817540d5affe5f7c74827c6a1); ?>
<?php endif; ?>

        <script src="<?php echo e(asset('assets/js/app.js')); ?>" ></script>
        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>

    <style>
        /* Error message styling */
        .errormessage {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .errormessage li {
            margin-bottom: 0.75rem;
            line-height: 1.5;
        }

        .errormessage .title {
            font-weight: 600;
            display: inline-block;
            min-width: 80px;
        }
    </style>
</html>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/layouts/error.blade.php ENDPATH**/ ?>