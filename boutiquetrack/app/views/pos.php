<div class="page-heading">
    <div><div class="eyebrow">CHECKOUT</div><h1>New sale</h1><p>Add items to the basket, apply a discount if needed, and complete the sale.</p></div>
    <a class="button button-soft" href="<?= h(app_path('sales')) ?>">Sales history</a>
</div>
<div class="pos-layout">
    <section class="panel pos-catalogue">
        <div class="panel-heading"><div><h2>Choose items</h2><p>Prices are read from the current catalogue.</p></div></div>
        <form method="get" class="search-form pos-search"><input type="hidden" name="page" value="pos"><span class="search-icon">⌕</span><input name="q" value="<?= h($search) ?>" placeholder="Search product, color, or SKU"><button class="button button-soft" type="submit">Search</button></form>
        <?php if ($items): ?><div class="pos-item-list"><?php foreach ($items as $item): ?>
            <article class="pos-item">
                <span class="product-dot"><?= h(strtoupper(substr($item['product_name'], 0, 1))) ?></span>
                <div class="pos-item-copy"><strong><?= h($item['product_name']) ?></strong><small><?= h($item['sku']) ?> · <?= h(trim(($item['size'] ?? '') . ' · ' . ($item['color'] ?? ''), ' ·') ?: 'Standard') ?></small><small class="stock-hint"><?= (int)$item['stock_quantity'] ?> in stock</small></div>
                <div class="pos-item-end"><strong><?= money($item['selling_price']) ?></strong><form method="post" action="<?= h(app_path('pos')) ?>" class="add-to-sale">
                    <?= csrf_field() ?><input type="hidden" name="action" value="add-cart"><input type="hidden" name="variant_id" value="<?= (int)$item['id'] ?>">
                    <input type="number" name="quantity" min="1" max="<?= (int)$item['stock_quantity'] ?>" value="1" aria-label="Quantity"><button class="button button-primary button-small" type="submit">Add</button>
                </form></div>
            </article>
        <?php endforeach; ?></div><?php else: ?><div class="empty-state compact-empty"><span class="empty-icon">⌕</span><strong>No available items found</strong><p>Try another search or restock an item before selling it.</p></div><?php endif; ?>
    </section>
    <aside class="panel sale-panel">
        <div class="panel-heading"><div><h2>Current sale</h2><p><?= count($cart) ?> line<?= count($cart) === 1 ? '' : 's' ?> · <?= date('j M Y') ?></p></div><span class="cart-icon">▣</span></div>
        <?php if ($cart): ?>
            <div class="sale-lines"><?php foreach ($cart as $line): ?><div class="sale-line">
                <div><strong><?= h($line['product_name']) ?></strong><small><?= h($line['sku']) ?> · <?= h(trim(($line['size'] ?? '') . ' / ' . ($line['color'] ?? ''), ' /') ?: 'Standard') ?></small><small><?= money($line['selling_price']) ?> each</small></div>
                <div class="sale-line-end"><strong><?= money($line['line_total']) ?></strong><form method="post" action="<?= h(app_path('pos')) ?>" class="cart-quantity">
                    <?= csrf_field() ?><input type="hidden" name="action" value="update-cart"><input type="hidden" name="variant_id" value="<?= (int)$line['id'] ?>">
                    <input type="number" name="quantity" min="0" max="<?= (int)$line['stock_quantity'] ?>" value="<?= (int)$line['quantity'] ?>" aria-label="Update quantity"><button class="mini-link" type="submit">Update</button>
                </form><form method="post" action="<?= h(app_path('pos')) ?>"><?= csrf_field() ?><input type="hidden" name="action" value="remove-cart"><input type="hidden" name="variant_id" value="<?= (int)$line['id'] ?>"><button class="mini-link danger-link" type="submit">Remove</button></form></div>
            </div><?php endforeach; ?></div>
            <form method="post" action="<?= h(app_path('pos')) ?>" class="checkout-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="checkout">
                <div class="checkout-field"><label for="discount">Discount <span class="optional">SSP</span></label><input id="discount" name="discount_amount" type="number" min="0" step="0.01" value="0"></div>
                <div class="checkout-field"><label for="payment">Payment method</label><select id="payment" name="payment_method"><option value="cash">Cash</option><option value="other">Other</option></select></div>
                <div class="total-row"><span>Subtotal</span><strong><?= money($cartSubtotal) ?></strong></div>
                <div class="total-row grand-total"><span>Amount due</span><strong><?= money($cartSubtotal) ?></strong></div>
                <button class="button button-primary button-wide checkout-button" type="submit">Complete sale <span>→</span></button>
                <small class="checkout-note">Stock is deducted when the sale is completed.</small>
            </form>
        <?php else: ?><div class="empty-state cart-empty"><span class="empty-icon">▣</span><strong>Your sale is empty</strong><p>Add a product variation to begin checkout.</p></div><?php endif; ?>
    </aside>
</div>