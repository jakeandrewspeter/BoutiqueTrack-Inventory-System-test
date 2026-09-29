<div class="page-heading">
    <div><div class="eyebrow">STOCK CONTROL</div><h1>Inventory</h1><p>Review available stock and record every adjustment.</p></div>
    <span class="subtle-chip"><span class="status-dot"></span> Stock updates with every sale</span>
</div>
<section class="panel">
    <div class="panel-heading"><div><h2>Current stock</h2><p>Items at or below their alert level are highlighted.</p></div><span class="result-count"><?= count($variants) ?> variations</span></div>
    <?php if ($variants): ?><div class="table-wrap"><table><thead><tr><th>Product / SKU</th><th>Variation</th><th>On hand</th><th>Alert at</th><th>Sell price</th><th>Adjust stock</th></tr></thead><tbody>
        <?php foreach ($variants as $variant): ?><tr class="<?= (int)$variant['stock_quantity'] <= (int)$variant['reorder_level'] ? 'row-low-stock' : '' ?>">
            <td><div class="table-product"><span class="product-dot"><?= h(strtoupper(substr($variant['product_name'], 0, 1))) ?></span><span><strong><?= h($variant['product_name']) ?></strong><small><?= h($variant['sku']) ?></small></span></div></td>
            <td><?= h(trim(($variant['size'] ?? '') . ' · ' . ($variant['color'] ?? ''), ' ·')) ?: 'Standard' ?></td>
            <td><strong><?= (int)$variant['stock_quantity'] ?></strong></td><td><?= (int)$variant['reorder_level'] ?></td><td><?= money($variant['selling_price']) ?></td>
            <td><form class="stock-adjust-form" method="post" action="<?= h(app_path('inventory')) ?>">
                <?= csrf_field() ?><input type="hidden" name="action" value="adjust-stock"><input type="hidden" name="variant_id" value="<?= (int)$variant['id'] ?>">
                <input name="quantity_change" type="number" step="1" required placeholder="+ / −" aria-label="Quantity change for <?= h($variant['sku']) ?>">
                <input name="note" maxlength="240" required placeholder="Reason" aria-label="Reason for stock change">
                <button class="button button-soft button-small" type="submit">Apply</button>
            </form></td>
        </tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><span class="empty-icon">◫</span><strong>No stock to track yet</strong><p>Add a product and variation to start managing inventory.</p><a class="button button-primary" href="<?= h(app_path('product-form')) ?>">Add a product</a></div><?php endif; ?>
</section>
<section class="panel section-panel">
    <div class="panel-heading"><div><h2>Recent stock movements</h2><p>Sales, restocks, and manual corrections</p></div></div>
    <?php if ($movements): ?><div class="table-wrap"><table><thead><tr><th>Product</th><th>Type</th><th>Change</th><th>Reason / reference</th><th>Updated by</th><th>Date</th></tr></thead><tbody>
        <?php foreach ($movements as $movement): ?><tr><td><strong><?= h($movement['product_name']) ?></strong><small class="block-muted"><?= h($movement['sku']) ?></small></td><td><span class="status-pill"><?= h(ucfirst($movement['movement_type'])) ?></span></td><td class="<?= (int)$movement['quantity_change'] > 0 ? 'positive' : 'negative' ?>"><?= (int)$movement['quantity_change'] > 0 ? '+' : '' ?><?= (int)$movement['quantity_change'] ?></td><td><?= h($movement['note'] ?: '—') ?></td><td><?= h($movement['full_name']) ?></td><td><?= h(date('j M, g:i a', strtotime($movement['created_at']))) ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-compact">Stock changes will appear here as sales and adjustments are recorded.</div><?php endif; ?>
</section>