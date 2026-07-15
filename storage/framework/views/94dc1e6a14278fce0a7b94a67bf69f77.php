<!DOCTYPE html>
<html>

<head>
    <title>Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Arial, sans-serif;
            margin: 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: auto;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            line-height: 24px;
        }

        .header {
            background: #f8f8f8;
            padding: 30px 30px 20px;
            border-bottom: 1px solid #eee;
        }

        .header h1 {
            font-size: 26px;
            color: #007bff;
            margin: 0;
        }

        .header p {
            margin: 0;
            font-size: 16px;
        }

        .invoice-details {
            padding: 15px 30px;
        }

        .invoice-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .invoice-details table td {
            vertical-align: top;
        }

        .invoice-details .info-left {
            text-align: left;
        }

        .invoice-details .info-right {
            text-align: right;
        }

        .bill-breakdown {
            padding: 15px 30px;
            border-top: 1px solid #eee;
        }

        .bill-breakdown table {
            width: 100%;
            border-collapse: collapse;
        }

        .bill-breakdown table th,
        .bill-breakdown table td {
            border-bottom: 1px solid #eee;
            padding: 5px 0;
        }

        .bill-breakdown table th {
            text-align: left;
            font-weight: bold;
            background: #f8f8f8;
        }

        .bill-breakdown table tr.total-row td {
            border-top: 2px solid #333;
            font-weight: bold;
            font-size: 16px;
        }

        .payment-options {
            padding: 30px;
            text-align: center;
        }

        .payment-options h2 {
            font-size: 20px;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 30px;
        }

        .payment-options .qr-container {
            display: inline-block;
            margin: 0 15px;
            text-align: center;
        }

        .payment-options .qr-container img {
            max-width: 150px;
            height: auto;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
        }

        .payment-options .qr-container p {
            font-size: 12px;
            margin-top: 10px;
            font-weight: bold;
        }

        .footer {
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #eee;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Invoice</h1>
            <p><strong>Invoice No.:</strong> <?php echo e($invoicePdf->invoice_number); ?></p>
            <p><strong>Date:</strong> <?php echo e($invoicePdf->date); ?></p>
        </div>

        <div class="invoice-details">
            <table>
                <tr>
                    <td class="info-left">
                        <strong>Billed To:</strong><br>
                        <?php if($invoicePdf->month): ?>
                            Room: <?php echo e($invoicePdf->room_number); ?> | <?php echo e($invoicePdf->tenant_name); ?><br>
                            Invoice Month: <?php echo e($invoicePdf->month); ?>

                        <?php else: ?>
                            Room: <?php echo e($invoicePdf->room_number); ?><br>
                            <?php echo e($invoicePdf->tenant_name); ?>

                        <?php endif; ?>
                    </td>
                    <td class="info-right">
                        <strong>Status:</strong> <?php echo e($invoicePdf->status); ?><br>
                        <strong>Pay Period:</strong> <?php echo e($invoicePdf->start_date); ?> - <?php echo e($invoicePdf->end_date); ?><br>
                        <strong>Readings:</strong> <?php echo e($invoicePdf->new_reading); ?> -
                        <?php echo e($invoicePdf->previous_reading); ?>

                    </td>
                </tr>
            </table>
        </div>

        <div class="bill-breakdown">
            <table>
                <thead>
                    <tr>
                        <th style="text-align: left;">Item</th>
                        <th style="text-align: right;">Rate</th>
                        <th style="text-align: right;">Quantity</th>
                        <th style="text-align: right;">Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $invoicePdf->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($item['title']); ?></td>
                            <td style="text-align: right;"><?php echo e($item['rate']); ?></td>
                            <td style="text-align: right;"><?php echo e($item['quantity']); ?></td>
                            <td style="text-align: right;"><?php echo e(number_format($item['amount'], 2)); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if($invoicePdf->due_amount > 0): ?>
                        <tr class="dues-row">
                            <td colspan=3 style="text-align: left;"><strong>Previous Dues</strong></td>
                            <td style="text-align: right;">
                                <strong><?php echo e(number_format($invoicePdf->due_amount, 2)); ?></strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if($invoicePdf->advance_amount > 0): ?>
                        <tr class="advance-row">
                            <td colspan=3 style="text-align: left;"><strong>Advance Amount</strong></td>
                            <td style="text-align: right;">
                                <strong>- <?php echo e(number_format($invoicePdf->advance_amount, 2)); ?></strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr class="total-row">
                        <td colspan=3 style="text-align: left;"><strong>Total Amount</strong></td>
                        <td style="text-align: right;">
                            <strong><?php echo e(number_format($invoicePdf->grand_total, 2)); ?></strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if(!empty($invoicePdf->payment_options)): ?>
            <div class="payment-options">
                <h2>Payment Options</h2>
                <?php $__currentLoopData = $invoicePdf->payment_options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="qr-container">
                        <img style="max-width:100px; height:100px;" src="<?php echo e($option['path']); ?>"
                            alt="<?php echo e($option['file_name']); ?> QR Code">
                        <p><?php echo e($option['payment_name']); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
        <?php if($invoicePdf->remarks): ?>
            <div class="footer">
                <strong>Remarks:</strong> <?php echo e($invoicePdf->remarks); ?>

            </div>
        <?php else: ?>
            <div class="footer">
                Kindly ensure timely payment of rent and outstanding dues. Thank you!
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
<?php /**PATH /var/www/html/resources/views/invoices/pdf.blade.php ENDPATH**/ ?>