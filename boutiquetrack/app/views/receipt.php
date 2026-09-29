<div class="page-heading no-print">
    <div><div class="eyebrow">SALE COMPLETE</div><h1>Receipt</h1><p><?= h($sale['receipt_number']) ?> · <?= h(date('j F Y, g:i a', strtotime($sale['sold_at']))) ?></p></div>
    <div class="heading-actions"><a class="button button-soft" href="<?= h(app_path('pos')) ?>">New sale</a><button class="button button-primary" type="button" onclick="window.print()">Print receipt <span>↗</span></button></div>
</div>
<section class="receipt-card">
    <div class="receipt-brand"><span class="brand-mark">B</span><div><strong>BoutiqueTrack</strong><small>MY BOUTIQUE · JUBA</small></div></div>
    <div class="receipt-title"><span>SALE RECEIPT</span><strong><?= h($sale['receipt_number']) ?></strong></div>
    <div class="receipt-meta"><span>DATE <strong><?= h(date('j M Y, g:i a', strtotime($sale['sold_at']))) ?></strong></span><span>SERVED BY <strong><?= h($sale['cashier_name']) ?></strong></span></div>
    <div class="receipt-items"><div class="receipt-items-heading"><span>ITEM</span><span>QTY</span><span>PRICE</span></div>
        <?php foreach ($lines as $line): ?><div class="receipt-item"><span><strong><?= h($line['product_name']) ?></strong><small><?= h($line['sku']) ?><?= $line['size'] || $line['color'] ? ' · ' . h(trim(($line['size'] ?? '') . ' / ' . ($line['color'] ?? ''), ' /')) : '' ?></small></span><span><?= (int)$line['quantity'] ?></span><span><?= money($line['line_total']) ?></span></div><?php endforeach; ?>
    </div>
    <div class="receipt-totals"><div><span>Subtotal</span><strong><?= money($sale['subtotal']) ?></strong></div><?php if ((float)$sale['discount_amount'] > 0): ?><div><span>Discount</span><strong>− <?= money($sale['discount_amount']) ?></strong></div><?php endif; ?><div class="receipt-grand"><span>TOTAL PAID</span><strong><?= money($sale['total_amount']) ?></strong></div><div><span>Payment</span><strong><?= h(ucfirst($sale['payment_method'])) ?></strong></div></div>
    <p class="receipt-thanks">Thank you for shopping with us.</p>
</section>