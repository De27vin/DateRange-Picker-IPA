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
    <meta name="account-id" content="<?php echo e(session('account.id', '')); ?>">
    <meta name="active-labels" content="<?php echo e(config('ucp.active_labels')); ?>">
    <meta name="has-phone" content="<?php echo e(!empty(Auth::user()?->user_ext)); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?></title>

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="/assets/themes/<?php echo e(session()->get('account.slug', 'system')); ?>/css/style.css">
    <link rel="preload" href="<?php echo e(asset('assets/fonts/f7icons/Framework7Icons-Regular.woff2')); ?>" as="font" type="font/woff2" crossorigin>
    <link href="<?php echo e(asset('assets/plugins/fontawesome/css/iconfonts.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/app.css')); ?>" rel="stylesheet">
    <link href="<?php echo e(asset('assets/css/app-new.css')); ?>" rel="stylesheet">
    <link href="/assets/css/app2.css?id=12" rel="stylesheet">
    <?php echo $__env->yieldPushContent('style'); ?>
    <?php echo \Livewire\Livewire::styles(); ?>


    <script src="<?php echo e(mix('assets/js/head.js')); ?>"></script>
</head>
<body class="bg-white text-normal" style="zoom: 0.75; overflow-x: hidden;">
<div>

    <div id="vue-loading-indicator">
        <vue-loading-indicator></vue-loading-indicator>
    </div>
    <script src="/vue/vue-loading-indicator.js"></script>

    <?php if (isset($component)) { $__componentOriginal6e60bac47b432ccf3dd91fddf4dea380 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6e60bac47b432ccf3dd91fddf4dea380 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.loading-indicator','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.loading-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6e60bac47b432ccf3dd91fddf4dea380)): ?>
<?php $attributes = $__attributesOriginal6e60bac47b432ccf3dd91fddf4dea380; ?>
<?php unset($__attributesOriginal6e60bac47b432ccf3dd91fddf4dea380); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6e60bac47b432ccf3dd91fddf4dea380)): ?>
<?php $component = $__componentOriginal6e60bac47b432ccf3dd91fddf4dea380; ?>
<?php unset($__componentOriginal6e60bac47b432ccf3dd91fddf4dea380); ?>
<?php endif; ?>

    <div id="app" class="min-h-screen flex flex-col justify-start">

        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('admin.navigation-new', [])->html();
} elseif ($_instance->childHasBeenRendered('EjwuM6K')) {
    $componentId = $_instance->getRenderedChildComponentId('EjwuM6K');
    $componentTag = $_instance->getRenderedChildComponentTagName('EjwuM6K');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('EjwuM6K');
} else {
    $response = \Livewire\Livewire::mount('admin.navigation-new', []);
    $html = $response->html();
    $_instance->logRenderedChild('EjwuM6K', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>

        <!--  BEGIN :: content -->
        <div class="pb-48">
            <?php if(!empty($slot)): ?>
                <?php echo e($slot); ?>

            <?php else: ?>
                <?php echo $__env->yieldContent('content'); ?>
            <?php endif; ?>
        </div>
        <!--  END :: content -->

    </div>
</div>

<i class="absolute w-0 h-0 hidden fas fa-caret-right fa-caret-down md:w-1/2 lg:w-1/3 lg:w-1/4 devicebox"></i>
<div class="absolute w-0 h-0 hidden text-gray-400 fas fa-caret-down fa-caret-right text-purple-400 text-missing-400 text-warning-400 text-error-400 w-64 w-48 w-40 w-32 -ml-8 ml-0  bg-warnings-600 bg-missings-600 bg-errors-600 bg-blue-600 bg-green-600 bg-success-400 bg-success-600 bg-warnings-400 bg-warnings-600 text-color-new-800 text-green-800 bg-gray-600 text-gray-600 text-gray-800" ></div>
<div class="absolute w-0 h-0 hidden bg-red-200 bg-blue-200 bg-green-200 bg-orange-200 hover:border-blue-600 border-infos-200 border-infos-400 hover:border-infos-600 border-warnings-200 border-warnings-400 hover:border-warnings-600 border-errors-200 border-errors-400 hover:border-errors-600 text-infos-200 text-infos-400 text-infos-600 text-warnings-200 text-warnings-400 text-warnings-600 text-errors-200 text-errors-400 text-errors-600  bg-infos-200 bg-infos-400 bg-infos-600 bg-warnings-200 bg-warnings-400 bg-warnings-600 bg-errors-200 bg-errors-400 bg-errors-600"></div>

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

<script src="<?php echo e(mix('assets/js/app.js')); ?>" ></script>
<?php echo $__env->yieldPushContent('scripts'); ?>

<?php if (isset($component)) { $__componentOriginal0d8d3c14ebd2b92d484be47e6c018839 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.notification','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('notification'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839)): ?>
<?php $attributes = $__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839; ?>
<?php unset($__attributesOriginal0d8d3c14ebd2b92d484be47e6c018839); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0d8d3c14ebd2b92d484be47e6c018839)): ?>
<?php $component = $__componentOriginal0d8d3c14ebd2b92d484be47e6c018839; ?>
<?php unset($__componentOriginal0d8d3c14ebd2b92d484be47e6c018839); ?>
<?php endif; ?>

<script src="<?php echo e(mix('assets/js/footer.js')); ?>"></script>

</body>
</html>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/layouts/app.blade.php ENDPATH**/ ?>