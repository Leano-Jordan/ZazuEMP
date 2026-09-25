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
    <?php $__env->slot('title', null, []); ?> New customer <?php $__env->endSlot(); ?>
    <?php $__env->slot('heading', null, []); ?> New customer <?php $__env->endSlot(); ?>
    <?php $__env->slot('headerAction', null, []); ?>
        <a href="<?php echo e(route('customers.index')); ?>" class="text-sm font-medium text-slate-500 hover:text-slate-900">← Customers</a>
    <?php $__env->endSlot(); ?>

    <form method="POST" action="<?php echo e(route('customers.store')); ?>" class="space-y-6">
        <?php echo csrf_field(); ?>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-slate-950">Customer</h2>
                <p class="mt-1 text-sm text-slate-500">The person or organisation you are doing business with.</p>
            </div>
            <label class="block">
                <span class="text-sm font-medium text-slate-700">Customer name</span>
                <input name="name" value="<?php echo e(old('name')); ?>" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. Thandi Mokoena">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="mt-1 block text-xs text-rose-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </label>

            <label class="mt-4 block">
                <span class="text-sm font-medium text-slate-700">Notes</span>
                <textarea name="notes" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Useful customer information"><?php echo e(old('notes')); ?></textarea>
            </label>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="font-semibold text-slate-950">Primary contact</h2>
                <p class="mt-1 text-sm text-slate-500">We will keep the contact separate so alternative contacts can be added later.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Contact name</span>
                    <input name="primary_contact_name" value="<?php echo e(old('primary_contact_name')); ?>" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    <?php $__errorArgs = ['primary_contact_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="mt-1 block text-xs text-rose-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </label>

                <label class="block">
                    <span class="text-sm font-medium text-slate-700">Phone</span>
                    <input name="primary_contact_phone" value="<?php echo e(old('primary_contact_phone')); ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. 071 234 5678">
                </label>

                <label class="block sm:col-span-2">
                    <span class="text-sm font-medium text-slate-700">Email</span>
                    <input type="email" name="primary_contact_email" value="<?php echo e(old('primary_contact_email')); ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                </label>
            </div>
        </section>

        <div class="flex items-center justify-end gap-3">
            <a href="<?php echo e(route('customers.index')); ?>" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
            <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Save customer</button>
        </div>
    </form>
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
<?php /**PATH C:\Projects\ZazuEMP\resources\views/customers/create.blade.php ENDPATH**/ ?>
