<div class="page-heading">
    <div><div class="eyebrow"><?= h(date('l, j F Y')) ?></div><h1>Good day, <?= h(explode(' ', $user['full_name'])[0]) ?>.</h1><p>Here’s how your boutique is doing today.</p></div>
    <a class="button button-primary" href="<?= h(app_path('pos')) ?>"><span>＋</span> New sale</a>
</div>
<section class="metric-grid">
    <article class="metric-card metric-featured"><div class="metric-top"><span>Sales today</span><span class="metric-icon">◷</span></div><strong><?= money($today) ?></strong><small><?= (int)$salesToday ?> transaction<?= $salesToday === 1 ? '' : 's' ?> today</small></article>
    <article class="metric-card"><div class="metric-top"><span>Revenue this month</span><span class="metric-icon">↗</span></div><strong><?= money($month) ?></strong><small>After discounts</small></article>
    <article class="metric-card"><div class="metric-top"><span>Items to check</span><span class="metric-icon icon-alert">!</span></div><strong><?= $lowStockCount ?></strong><small>At or below reorder level</small></article>
    <article class="metric-card"><div class="metric-top"><span>Products sold</span><span class="metric-icon">◇</span></div><strong><?= $productsSoldCount ?></strong><small>Unique products · last 30 days</small></article>
</section>
<div class="dashboard-grid">
    <section class="panel">
        <div class="panel-heading"><div><h2>Recent sales</h2><p>The latest checkout activity</p></div><a class="text-link" href="<?= h(app_path('sales')) ?>">View all <span>→</span></a></div>
        <?php if ($recent): ?>
            <div class="table-wrap"><table><thead><tr><th>Receipt</th><th>Cashier</th><th>Date</th><th class="align-right">Total</th></tr></thead><tbody>
                <?php foreach ($recent as $sale): ?><tr><td><a class="table-link" href="<?= h(app_path('receipt', ['id' => $sale['id']])) ?>"><?= h($sale['receipt_number']) ?></a></td><td><?= h($sale['full_name']) ?></td><td><?= h(date('j M, g:i a', strtotime($sale['sold_at']))) ?></td><td class="align-right strong"><?= money($sale['total_amount']) ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><div class="empty-state"><span class="empty-icon">↗</span><strong>No sales yet</strong><p>Your completed sales will show up here.</p><a class="text-link" href="<?= h(app_path('pos')) ?>">Start your first sale →</a></div><?php endif; ?>
    </section>
    <div class="side-panels">
        <section class="panel">
            <div class="panel-heading"><div><h2>Running low</h2><p>Items at their reorder point</p></div><a class="text-link" href="<?= h(app_path('inventory')) ?>">Inventory →</a></div>
            <?php if ($lowStock): ?><div class="compact-list"><?php foreach ($lowStock as $item): ?>
                <div class="compact-row"><span class="product-dot"><?= h(strtoupper(substr($item['product_name'], 0, 1))) ?></span><span class="compact-copy"><strong><?= h($item['product_name']) ?></strong><small><?= h(trim(($item['size'] ?? '') . ' ' . ($item['color'] ?? ''))) ?: h($item['sku']) ?></small></span><span class="stock-pill stock-low"><?= (int)$item['stock_quantity'] ?> left</span></div>
            <?php endforeach; ?></div><?php else: ?><div class="empty-compact"><span class="check-mark">✓</span><span>All active items are above their reorder level.</span></div><?php endif; ?>
        </section>
        <section class="panel">
            <div class="panel-heading"><div><h2>Top movers</h2><p>Units sold · last 30 days</p></div><?php if ($user['role'] === 'admin'): ?><a class="text-link" href="<?= h(app_path('reports')) ?>">Reports →</a><?php endif; ?></div>
            <?php if ($topProducts): ?><div class="rank-list"><?php foreach ($topProducts as $index => $product): ?><div class="rank-row"><span class="rank-number"><?= str_pad((string)($index + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= h($product['product_name']) ?></strong><span><?= (int)$product['units'] ?> sold</span></div><?php endforeach; ?></div><?php else: ?><div class="empty-compact"><span class="muted">No product sales in the last 30 days.</span></div><?php endif; ?>
        </section>
    </div>
</div>