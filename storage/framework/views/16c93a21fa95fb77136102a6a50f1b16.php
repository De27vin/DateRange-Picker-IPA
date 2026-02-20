<div class="mt-4 mx-auto w-full pt-4 px-12 font-medium">
    <div class="w-full">

        <?php if (isset($component)) { $__componentOriginal4a29ad41492c717286123b97d5ba8cca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a29ad41492c717286123b97d5ba8cca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.header','data' => ['class' => 'h-16']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-16']); ?>
             <?php $__env->slot('title', null, []); ?> <?php echo app('translator')->get('User Profile'); ?> <?php $__env->endSlot(); ?>
             <?php $__env->slot('description', null, []); ?> <?php echo app('translator')->get('Manage your user settings'); ?> <?php $__env->endSlot(); ?>
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

        <?php if (isset($component)) { $__componentOriginal8fc6ff88e43a88c6d33724efff2a3eb8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8fc6ff88e43a88c6d33724efff2a3eb8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.tabs-primary','data' => ['defaultTab' => ''.e(session('tab', 'changePassword')).'','tabs' => [
            'changePassword' => __('Password'),
            'changeLanguage' => __('Language'),
            'changeFilters' => __('Filters'),
        ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.tabs-primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['defaultTab' => ''.e(session('tab', 'changePassword')).'','tabs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
            'changePassword' => __('Password'),
            'changeLanguage' => __('Language'),
            'changeFilters' => __('Filters'),
        ])]); ?>
             <?php $__env->slot('changePasswordSlot', null, []); ?> <div style="margin-inline: 1.5rem;"><?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('user.change-password', [])->html();
} elseif ($_instance->childHasBeenRendered('l2704073988-0')) {
    $componentId = $_instance->getRenderedChildComponentId('l2704073988-0');
    $componentTag = $_instance->getRenderedChildComponentTagName('l2704073988-0');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l2704073988-0');
} else {
    $response = \Livewire\Livewire::mount('user.change-password', []);
    $html = $response->html();
    $_instance->logRenderedChild('l2704073988-0', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></div> <?php $__env->endSlot(); ?>
             <?php $__env->slot('changeLanguageSlot', null, []); ?> <div style="margin-inline: 1.5rem;"><?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('user.change-language', [])->html();
} elseif ($_instance->childHasBeenRendered('l2704073988-1')) {
    $componentId = $_instance->getRenderedChildComponentId('l2704073988-1');
    $componentTag = $_instance->getRenderedChildComponentTagName('l2704073988-1');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l2704073988-1');
} else {
    $response = \Livewire\Livewire::mount('user.change-language', []);
    $html = $response->html();
    $_instance->logRenderedChild('l2704073988-1', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></div> <?php $__env->endSlot(); ?>
             <?php $__env->slot('changeFiltersSlot', null, []); ?> <div style="margin-inline: 1.5rem;"><?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('user.change-filters', [])->html();
} elseif ($_instance->childHasBeenRendered('l2704073988-2')) {
    $componentId = $_instance->getRenderedChildComponentId('l2704073988-2');
    $componentTag = $_instance->getRenderedChildComponentTagName('l2704073988-2');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('l2704073988-2');
} else {
    $response = \Livewire\Livewire::mount('user.change-filters', []);
    $html = $response->html();
    $_instance->logRenderedChild('l2704073988-2', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></div> <?php $__env->endSlot(); ?>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8fc6ff88e43a88c6d33724efff2a3eb8)): ?>
<?php $attributes = $__attributesOriginal8fc6ff88e43a88c6d33724efff2a3eb8; ?>
<?php unset($__attributesOriginal8fc6ff88e43a88c6d33724efff2a3eb8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8fc6ff88e43a88c6d33724efff2a3eb8)): ?>
<?php $component = $__componentOriginal8fc6ff88e43a88c6d33724efff2a3eb8; ?>
<?php unset($__componentOriginal8fc6ff88e43a88c6d33724efff2a3eb8); ?>
<?php endif; ?>

    </div>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/user/user-profile.blade.php ENDPATH**/ ?>