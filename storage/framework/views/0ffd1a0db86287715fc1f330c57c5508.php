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
     <?php $__env->slot('title', null, []); ?> Customers <?php $__env->endSlot(); ?>
     <?php $__env->slot('heading', null, []); ?> Customers <?php $__env->endSlot(); ?>
     <?php $__env->slot('headerAction', null, []); ?> 
        <a href="<?php echo e(route('customers.create')); ?>" class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            New customer
        </a>
     <?php $__env->endSlot(); ?>

    <div class="mb-5 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="font-semibold text-slate-950">Customer directory</p>
            <p class="mt-1 text-sm text-slate-500">Customers are the starting point for operational work.</p>
        </div>
        <p class="text-sm text-slate-400"><?php echo e($customers->total()); ?> total</p>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-4 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="font-semibold text-slate-950"><?php echo e($customer->name); ?></p>
                    <p class="mt-1 text-sm text-slate-500">
                        <?php echo e($customer->primaryContact?->phone ?? 'No phone'); ?>

                        <?php if($customer->primaryContact?->email): ?> · <?php echo e($customer->primaryContact->email); ?> <?php endif; ?>
                    </p>
                </div>
                <div class="text-sm text-slate-500">
                    <?php echo e($customer->events_count); ?> <?php echo e($customer->events_count === 1 ? 'work item' : 'work items'); ?>

                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="px-5 py-16 text-center">
                <p class="text-lg font-semibold text-slate-950">No customers yet</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Add your first customer. Their primary contact will be stored with them.</p>
                <a href="<?php echo e(route('customers.create')); ?>" class="mt-5 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add customer</a>
            </div>
        <?php endif; ?>

        <?php if($customers->hasPages()): ?>
            <div class="border-t border-slate-200 px-5 py-4"><?php echo e($customers->links()); ?></div>
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
<?php /**PATH C:\Projects\ZazuEMP\resources\views/customers/index.blade.php ENDPATH**/ ?>