<div class="page-heading">
    <div><div class="eyebrow"><a class="text-link" href="<?= h(app_path('products')) ?>">PRODUCTS</a> / CATALOGUE</div><h1><?= empty($product) ? 'Add a product' : 'Edit product' ?></h1><p>Product details stay together; size and color options are tracked separately.</p></div>
    <a class="button button-soft" href="<?= h(app_path('products')) ?>">← Back to products</a>
</div>
<form method="post" action="<?= h(app_path('product-form')) ?>" class="panel form-panel">
    <?= csrf_field() ?><input type="hidden" name="action" value="save-product">
    <?php if ($product): ?><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>"><?php endif; ?>
    <div class="section-heading"><div><h2>Product details</h2><p>The shared details for every variation</p></div></div>
    <div class="form-grid two">
        <label>Product name<input name="name" required maxlength="140" value="<?= h($product['name'] ?? '') ?>" placeholder="e.g. Everyday linen dress"></label>
        <label>Brand <span class="optional">Optional</span><input name="brand" maxlength="100" value="<?= h($product['brand'] ?? '') ?>" placeholder="Brand or designer"></label>
        <label>Category<select name="category_id"><option value="0">Choose a category</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)($product['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>><?= h($category['name']) ?></option><?php endforeach; ?></select></label>
        <label class="span-two">Description <span class="optional">Optional</span><textarea name="description" rows="3" maxlength="2000" placeholder="A short note about this product"><?= h($product['description'] ?? '') ?></textarea></label>
    </div>
    <div class="section-heading section-spaced"><div><h2><?= $product ? 'Add a variation' : 'First variation' ?></h2><p>Each size or color combination gets its own SKU and stock count.</p></div></div>
    <div class="form-grid three">
        <label>SKU<input name="new_sku" maxlength="60" <?= $product ? '' : 'required' ?> placeholder="e.g. DRESS-001-M-NVY"></label>
        <label>Size <span class="optional">Optional</span><input name="new_size" maxlength="40" placeholder="M, 40, One size"></label>
        <label>Color <span class="optional">Optional</span><input name="new_color" maxlength="60" placeholder="Navy"></label>
        <label>Cost price<input name="new_cost_price" type="number" min="0" step="0.01" <?= $product ? '' : 'required' ?> value="<?= $product ? '' : '0' ?>"></label>
        <label>Selling price<input name="new_selling_price" type="number" min="0.01" step="0.01" <?= $product ? '' : 'required' ?> placeholder="0.00"></label>
        <label>Opening stock<input name="new_stock" type="number" min="0" step="1" <?= $product ? '' : 'required' ?> value="<?= $product ? '' : '0' ?>"></label>
        <label>Low-stock alert at<input name="new_reorder_level" type="number" min="0" step="1" value="3"></label>
    </div>
    <div class="form-actions"><span class="muted">Prices are recorded in SSP.</span><button class="button button-primary" type="submit">Save product <span>→</span></button></div>
</form>
<?php if ($product): ?>
    <section class="panel section-panel">
        <div class="panel-heading"><div><h2>Existing variations</h2><p>Stock quantities are changed from Inventory to keep an adjustment history.</p></div></div>
        <?php if ($variants): ?>
            <?php foreach ($variants as $variant): ?>
                <form id="variant-form-<?= (int)$variant['id'] ?>" method="post" action="<?= h(app_path('product-form', ['edit' => $product['id']])) ?>">
                    <?= csrf_field() ?><input type="hidden" name="action" value="update-variant"><input type="hidden" name="variant_id" value="<?= (int)$variant['id'] ?>"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                </form>
            <?php endforeach; ?>
            <div class="table-wrap"><table><thead><tr><th>SKU</th><th>Size</th><th>Color</th><th>Cost price</th><th>Selling price</th><th>Low-stock at</th></tr></thead><tbody>
            <?php foreach ($variants as $variant): $formId = 'variant-form-' . (int)$variant['id']; ?><tr>
                <td><input form="<?= h($formId) ?>" class="table-input" name="sku" value="<?= h($variant['sku']) ?>" required maxlength="60"></td>
                <td><input form="<?= h($formId) ?>" class="table-input" name="size" value="<?= h($variant['size']) ?>" maxlength="40"></td>
                <td><input form="<?= h($formId) ?>" class="table-input" name="color" value="<?= h($variant['color']) ?>" maxlength="60"></td>
                <td><input form="<?= h($formId) ?>" class="table-input narrow" name="cost_price" type="number" min="0" step="0.01" value="<?= h($variant['cost_price']) ?>"></td>
                <td><input form="<?= h($formId) ?>" class="table-input narrow" name="selling_price" type="number" min="0.01" step="0.01" value="<?= h($variant['selling_price']) ?>"></td>
                <td class="inline-save"><input form="<?= h($formId) ?>" class="table-input short" name="reorder_level" type="number" min="0" value="<?= (int)$variant['reorder_level'] ?>"><button form="<?= h($formId) ?>" class="button button-soft button-small" type="submit">Save</button></td>
            </tr><?php endforeach; ?>
        </tbody></table></div><?php else: ?><div class="empty-compact">No variations yet. Add one using the form above.</div><?php endif; ?>
    </section>
    <section class="panel section-panel archive-row">
        <div><h2>Product availability</h2><p>Archived products stay in past receipts and can be restored at any time.</p></div>
        <form method="post" action="<?= h(app_path('products')) ?>" data-confirm="<?= $product['is_active'] ? 'Archive this product? It will no longer appear in the point of sale.' : 'Restore this product to the catalogue?' ?>">
            <?= csrf_field() ?><input type="hidden" name="action" value="toggle-product"><input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
            <button class="button <?= $product['is_active'] ? 'button-danger-soft' : 'button-soft' ?>" type="submit"><?= $product['is_active'] ? 'Archive product' : 'Restore product' ?></button>
        </form>
    </section>
<?php endif; ?>