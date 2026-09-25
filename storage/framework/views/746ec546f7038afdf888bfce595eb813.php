<?php if (isset($component)) { $__componentOriginal4619374cef299e94fd7263111d0abc69 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4619374cef299e94fd7263111d0abc69 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.app-layout','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Work <?php $__env->endSlot(); ?>
     <?php $__env->slot('heading', null, []); ?> Work <?php $__env->endSlot(); ?>
     <?php $__env->slot('headerAction', null, []); ?> 
        <a href="<?php echo e(route('work.create')); ?>" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            New work
        </a>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Open work</p>
            <p class="mt-2 text-3xl font-semibold"><?php echo e($events->total()); ?></p>
            <p class="mt-1 text-xs text-slate-400">Operational records</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">This workspace</p>
            <p class="mt-2 text-3xl font-semibold">0</p>
            <p class="mt-1 text-xs text-slate-400">Quotes and costs will appear here</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5">
            <p class="text-sm text-slate-500">Next layer</p>
            <p class="mt-2 text-lg font-semibold">Requirements</p>
            <p class="mt-1 text-xs text-slate-400">Coming in the next workflow batch</p>
        </div>
    </div>

    <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
            <div>
                <h2 class="font-semibold text-slate-950">Recent work</h2>
                <p class="mt-1 text-sm text-slate-500">Every job becomes an operational workspace.</p>
            </div>
            <a href="<?php echo e(route('customers.index')); ?>" class="text-sm font-medium text-sky-700 hover:text-sky-900">Customers →</a>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <a href="<?php echo e(route('work.show', $event)); ?>" class="block border-b border-slate-100 px-5 py-4 last:border-b-0 hover:bg-slate-50">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-950"><?php echo e($event->name); ?></span>
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600"><?php echo e(ucfirst($event->status)); ?></span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            <?php echo e($event->customer?->name ?? $event->customer_name ?? 'No customer'); ?>

                            · <?php echo e($event->reference); ?>

                        </p>
                    </div>
                    <div class="text-left sm:text-right">
                        <p class="text-sm font-medium text-slate-700"><?php echo e($event->event_date?->format('d M Y') ?? 'Date not set'); ?></p>
                        <p class="mt-1 text-xs text-slate-400"><?php echo e($event->event_type ?: 'Work'); ?></p>
                    </div>
                </div>
            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-slate-950">No work yet</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Create a customer first, then turn that customer into a job or event.</p>
                <div class="mt-5 flex justify-center gap-3">
                    <a href="<?php echo e(route('customers.create')); ?>" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add customer</a>
                    <a href="<?php echo e(route('work.create')); ?>" class="rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700">Create work</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if($events->hasPages()): ?>
            <div class="border-t border-slate-200 px-5 py-4"><?php echo e($events->links()); ?></div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $attributes = $__attributesOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__attributesOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4619374cef299e94fd7263111d0abc69)): ?>
<?php $component = $__componentOriginal4619374cef299e94fd7263111d0abc69; ?>
<?php unset($__componentOriginal4619374cef299e94fd7263111d0abc69); ?>
<?php endif; ?>
<?php /**PATH C:\Projects\ZazuEMP\resources\views/work/index.blade.php ENDPATH**/ ?>