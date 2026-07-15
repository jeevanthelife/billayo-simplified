<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="space-y-6">

        
        <div class="text-center">
            <h2 class="text-xl font-semibold">Bill Details</h2>
            <span class="text-gray-600"><strong>Invoice Number: </strong><?php echo e($record->invoice_number); ?></span>
            <p class="text-gray-600">
                <strong>Billed To: </strong><?php echo e($record->billing_type); ?> - Room <?php echo e($record->room->room_number); ?>

                (<?php echo e($record->tenant->name); ?>)
            </p>
            <!--[if BLOCK]><![endif]--><?php if($record->month): ?>
                <p class="text-gray-600">
                    <strong>Billed Month: </strong><?php echo e($record->month); ?>

                </p>
            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            <span class="text-gray-600"><strong>Meter Readings:</strong> <?php echo e($record->new_reading); ?> -
                <?php echo e($record->previous_reading); ?></span>
        </div>

        
        <div class="flex items-center justify-between border-b pb-4">
            <span><?php echo e($record->invoice_date); ?></span>
            <span class="px-3 py-1 text-sm rounded-md border">
                <?php echo e(ucfirst($record->status)); ?>

            </span>
        </div>

        
        <div>
            <h3 class="font-semibold mb-2">Bill Breakdown</h3>
            <div class="space-y-2">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $record->invoiceItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-between text-sm">
                        <span>
                            <?php echo e($item->title); ?>

                            <!--[if BLOCK]><![endif]--><?php if($item->description): ?>
                                (<?php echo e($item->description); ?>)
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </span>
                        <span>Rs. <?php echo e(number_format($item->amount, 2)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($record->due_amount > 0): ?>
                    <div class="flex justify-between text-sm">
                        <strong>
                            Previous Dues
                        </strong>
                        <strong>Rs. <?php echo e(number_format($record->due_amount, 2)); ?></strong>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                <!--[if BLOCK]><![endif]--><?php if($record->advance_amount > 0): ?>
                    <div class="flex justify-between text-sm">
                        <strong>
                            Advance Amount
                        </strong>
                        <strong>Rs. <?php echo e(number_format($record->advance_amount, 2)); ?></strong>
                    </div>
                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        </div>

        
        <div class="border-t pt-3">
            <div class="flex justify-between font-semibold text-lg">
                <span>Total Amount</span>
                <span>Rs. <?php echo e(number_format($record->grand_total, 2)); ?></span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mt-1">
                <span>Start-End Date</span>
                <span><?php echo e(\Carbon\Carbon::parse($record->start_date)->format('m/d/Y')); ?> -
                    <?php echo e(\Carbon\Carbon::parse($record->end_date)->format('m/d/Y')); ?>

                </span>
            </div>
        </div>
        
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('approve-invoice', ['invoice' => $this->record]);

$__html = app('livewire')->mount($__name, $__params, 'lw-358489031-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <!--[if BLOCK]><![endif]--><?php if(!empty($record->paymentOptions)): ?>
            <h2 class="text-center font-semibold">Payment Options</h2>
            <div class="flex payment-options" style="justify-content: space-evenly;">
                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $record->paymentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="qr-container">
                        <figure>
                            <img style="max-width:120px; height:120px; width:120px;" src="<?php echo e($option['full_url']); ?>"
                                alt="<?php echo e($option['file_name']); ?> QR Code">
                            <figcaption><?php echo e($option['payment_name']); ?></figcaption>
                        </figure>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/filament/resources/invoice-resource/pages/view-invoice.blade.php ENDPATH**/ ?>