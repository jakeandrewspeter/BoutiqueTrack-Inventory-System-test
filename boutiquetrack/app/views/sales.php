<div class="page-heading">
    <div><div class="eyebrow">TRANSACTIONS</div><h1>Sales history</h1><p><?= $user['role'] === 'admin' ? 'Receipts from your boutique.' : 'Your completed transactions.' ?></p></div>
    <a class="button button-primary" href="<?= h(app_path('pos')) ?>"><span>＋</span> New sale</a>
</div>
<section class="panel">
    <?php if ($sales): ?><div class="table-wrap"><table><thead><tr><th>Receipt</th><th>Date</th><?php if ($user['role'] === 'admin'): ?><th>Cashier</th><?php endif; ?><th>Payment</th><th>Discount</th><th class="align-right">Total</th><th></th></tr></thead><tbody>
        <?php foreach ($sales as $sale): ?><tr><td><a class="table-link" href="<?= h(app_path('receipt', ['id' => $sale['id']])) ?>"><?= h($sale['receipt_number']) ?></a></td><td><?= h(date('j M Y, g:i a', strtotime($sale['sold_at']))) ?></td><?php if ($user['role'] === 'admin'): ?><td><?= h($sale['full_name']) ?></td><?php endif; ?><td><?= h(ucfirst($sale['payment_method'])) ?></td><td><?= money($sale['discount_amount']) ?></td><td class="align-right strong"><?= money($sale['total_amount']) ?></td><td class="align-right"><a class="text-link" href="<?= h(app_path('receipt', ['id' => $sale['id']])) ?>">Receipt →</a></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><span class="empty-icon">↗</span><strong>No completed sales</strong><p>When you complete a checkout, it will appear here.</p><a class="button button-primary" href="<?= h(app_path('pos')) ?>">Start a sale</a></div><?php endif; ?>
</section>