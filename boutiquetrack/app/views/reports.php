<div class="page-heading">
    <div><div class="eyebrow">BUSINESS INSIGHTS</div><h1>Reports</h1><p>Sales performance and product movement for your boutique.</p></div>
    <a class="button button-soft" href="<?= h(app_path('export', ['period' => $period])) ?>">Download CSV <span>↓</span></a>
</div>
<div class="period-tabs">
    <?php foreach (['today' => 'Today', 'week' => 'Last 7 days', 'month' => 'This month'] as $value => $label): ?><a class="<?= $period === $value ? 'selected' : '' ?>" href="<?= h(app_path('reports', ['period' => $value])) ?>"><?= h($label) ?></a><?php endforeach; ?>
</div>
<section class="metric-grid report-metrics">
    <article class="metric-card metric-featured"><div class="metric-top"><span>Net sales</span><span class="metric-icon">↗</span></div><strong><?= money($summary['revenue']) ?></strong><small>After discounts</small></article>
    <article class="metric-card"><div class="metric-top"><span>Transactions</span><span class="metric-icon">◷</span></div><strong><?= (int)$summary['transaction_count'] ?></strong><small>Completed sales</small></article>
    <article class="metric-card"><div class="metric-top"><span>Discounts given</span><span class="metric-icon">％</span></div><strong><?= money($summary['discounts']) ?></strong><small>Across selected period</small></article>
    <article class="metric-card"><div class="metric-top"><span>Gross profit estimate</span><span class="metric-icon">◇</span></div><strong><?= money(array_sum(array_map(static fn($row) => (float)$row['gross_sales'] - (float)$row['cost_of_goods'], $products))) ?></strong><small>Before discounts · based on recorded costs</small></article>
</section>
<div class="dashboard-grid report-grid">
    <section class="panel">
        <div class="panel-heading"><div><h2>Product performance</h2><p>Items sold and gross sales in this period</p></div></div>
        <?php if ($products): ?><div class="table-wrap"><table><thead><tr><th>Product</th><th>Units sold</th><th>Sales</th><th>Cost</th><th>Gross profit</th></tr></thead><tbody>
            <?php foreach ($products as $product): ?><tr><td><strong><?= h($product['product_name']) ?></strong></td><td><?= (int)$product['units'] ?></td><td><?= money($product['gross_sales']) ?></td><td><?= money($product['cost_of_goods']) ?></td><td class="positive"><?= money((float)$product['gross_sales'] - (float)$product['cost_of_goods']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div><?php else: ?><div class="empty-state"><span class="empty-icon">▤</span><strong>No sales in this period</strong><p>Product performance will appear after the next completed sale.</p></div><?php endif; ?>
    </section>
    <section class="panel">
        <div class="panel-heading"><div><h2>Daily sales</h2><p>Last 14 days</p></div></div>
        <?php if ($daily): ?><div class="rank-list daily-list"><?php foreach ($daily as $day): ?><div class="rank-row"><span class="rank-number"><?= h(date('d M', strtotime($day['sale_date']))) ?></span><span><?= (int)$day['transactions'] ?> sale<?= (int)$day['transactions'] === 1 ? '' : 's' ?></span><strong><?= money($day['revenue']) ?></strong></div><?php endforeach; ?></div><?php else: ?><div class="empty-compact">No recent sales recorded yet.</div><?php endif; ?>
    </section>
</div>