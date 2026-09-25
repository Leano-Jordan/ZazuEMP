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
     <?php $__env->slot('title', null, []); ?> New work <?php $__env->endSlot(); ?>
     <?php $__env->slot('heading', null, []); ?> New work <?php $__env->endSlot(); ?>
     <?php $__env->slot('headerAction', null, []); ?> 
        <a href="<?php echo e(route('work.index')); ?>" class="text-sm font-medium text-slate-500 hover:text-slate-900">← Work</a>
     <?php $__env->endSlot(); ?>

    <?php if($customers->isEmpty()): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h2 class="font-semibold text-amber-950">Add a customer first</h2>
            <p class="mt-1 text-sm text-amber-800">A work record starts from a customer. Once you have one, it can become an event/job workspace.</p>
            <a href="<?php echo e(route('customers.create')); ?>" class="mt-4 inline-flex rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white">Add customer</a>
        </div>
    <?php else: ?>
        <form method="POST" action="<?php echo e(route('work.store')); ?>" class="space-y-6">
            <?php echo csrf_field(); ?>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                <div class="mb-5">
                    <h2 class="font-semibold text-slate-950">Work details</h2>
                    <p class="mt-1 text-sm text-slate-500">Start the operational record. Requirements, quotes, travel and costs attach later.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Customer</span>
                        <select id="customer_id" name="customer_id" required class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Select customer</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($customer->id); ?>" <?php if(old('customer_id') == $customer->id): echo 'selected'; endif; ?>><?php echo e($customer->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['customer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="mt-1 block text-xs text-rose-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Work / event name</span>
                        <input name="name" value="<?php echo e(old('name')); ?>" required class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="e.g. Mokoena Wedding">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Type</span>
                        <input name="event_type" value="<?php echo e(old('event_type')); ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Wedding, funeral, hire, catering...">
                    </label>

                    <label class="block">
                        <span class="text-sm font-medium text-slate-700">Date</span>
                        <input type="date" name="event_date" value="<?php echo e(old('event_date')); ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Event-day contact</span>
                        <select id="event_day_contact_id" name="event_day_contact_id" class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100">
                            <option value="">Select a customer first</option>
                        </select>
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Event location</span>
                        <input name="event_address" value="<?php echo e(old('event_address')); ?>" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100" placeholder="Address or venue">
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="text-sm font-medium text-slate-700">Notes</span>
                        <textarea name="notes" rows="4" class="mt-2 w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-100"><?php echo e(old('notes')); ?></textarea>
                    </label>
                </div>
            </section>

            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('work.index')); ?>" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-100">Cancel</a>
                <button class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Create work</button>
            </div>
        </form>

        <script>
            const customers = <?php echo json_encode($customers->map(fn ($customer) => [
                'id' => $customer->id, 'contacts' => $customer->contacts->map(fn ($contact) => [
                    'id' => $contact->id, 'name' => $contact->name) ?>;

            const customerSelect = document.getElementById('customer_id');
            const contactSelect = document.getElementById('event_day_contact_id');

            function refreshContacts() {
                const customer = customers.find(item => String(item.id) === customerSelect.value);
                contactSelect.innerHTML = '<option value="">Select event-day contact</option>';

                if (!customer) return;

                customer.contacts.forEach(contact => {
                    const option = document.createElement('option');
                    option.value = contact.id;
                    option.textContent = contact.name + (contact.label ? ' · ' + contact.label : '') + (contact.phone ? ' · ' + contact.phone : '');
                    contactSelect.appendChild(option);
                });
            }

            customerSelect.addEventListener('change', refreshContacts);
            refreshContacts();
        </script>
    <?php endif; ?>
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
<?php /**PATH C:\Projects\ZazuEMP\resources\views/work/create.blade.php ENDPATH**/ ?>