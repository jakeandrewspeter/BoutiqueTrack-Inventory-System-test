<div class="page-heading">
    <div><div class="eyebrow">CATALOGUE</div><h1>Products</h1><p>Keep product details and variations up to date.</p></div>
    <a class="button button-primary" href="<?= h(app_path('product-form')) ?>"><span>＋</span> Add product</a>
</div>
<section class="panel">
    <div class="list-toolbar">
        <form method="get" class="search-form"><input type="hidden" name="page" value="products"><span class="search-icon">⌕</span><input name="q" value="<?= h($search) ?>" placeholder="Search products, brands, or SKUs"><button class="button button-soft" type="submit">Search</button></form>
        <span class="result-count"><?= count($products) ?> product<?= count($products) === 1 ? '' : 's' ?></span>
    </div>
    <?php if ($products): ?><div class="table-wrap"><table><thead><tr><th>Product</th><th>Category</th><th>Variations</th><th>Total stock</th><th>Starting price</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($products as $product): ?><tr>
            <td><div class="table-product"><span class="product-dot"><?= h(strtoupper(substr($product['name'], 0, 1))) ?></span><span><strong><?= h($product['name']) ?></strong><small><?= h($product['brand'] ?: 'No brand') ?></small></span></div></td>
            <td><?= h($product['category'] ?: 'Uncategorized') ?></td><td><?= (int)$product['variant_count'] ?></td><td><?= (int)$product['total_stock'] ?></td><td><?= money($product['from_price']) ?></td>
            <td><span class="status-pill <?= $product['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $product['is_active'] ? 'Active' : 'Archived' ?></span></td>
            <td class="align-right"><a class="text-link" href="<?= h(app_path('product-form', ['edit' => $product['id']])) ?>">Edit</a></td>
        </tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><span class="empty-icon">▦</span><strong><?= $search ? 'No matching products' : 'Your catalogue is ready' ?></strong><p><?= $search ? 'Try another name, brand, or SKU.' : 'Add your first product and track every size and color.' ?></p><?php if (!$search): ?><a class="button button-primary" href="<?= h(app_path('product-form')) ?>">Add your first product</a><?php endif; ?></div><?php endif; ?>
</section>