<?php
declare(strict_types=1);

function process_action(PDO $pdo, ?array $user, string $action): void
{
    verify_csrf();

    if ($action === 'login') {
        $username = input_text('username', 60);
        $password = (string)($_POST['password'] ?? '');
        $statement = $pdo->prepare('SELECT id, password_hash, is_active FROM users WHERE username = ?');
        $statement->execute([$username]);
        $account = $statement->fetch();

        if (!$account || !(bool)$account['is_active'] || !password_verify($password, $account['password_hash'])) {
            flash('error', 'The username or password was not recognized.');
            redirect(app_path('login'));
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$account['id'];
        unset($_SESSION['_csrf']);
        flash('success', 'Welcome back.');
        redirect(app_path('dashboard'));
    }

    if ($action === 'setup-admin') {
        if (table_exists($pdo, 'users')) {
            $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($count > 0) {
                redirect(app_path('login'));
            }
        }

        $fullName = input_text('full_name', 120);
        $username = input_text('username', 60);
        $password = (string)($_POST['password'] ?? '');
        $confirm = (string)($_POST['confirm_password'] ?? '');
        if ($fullName === '' || !preg_match('/^[A-Za-z0-9._-]{3,60}$/', $username)) {
            throw new InvalidArgumentException('Enter your name and a username with at least 3 letters or numbers.');
        }
        if (strlen($password) < 10 || $password !== $confirm) {
            throw new InvalidArgumentException('Use a password of at least 10 characters and make sure both entries match.');
        }

        $setupLockName = 'boutiquetrack_first_admin';
        $setupLock = $pdo->prepare('SELECT GET_LOCK(?, 10)');
        $setupLock->execute([$setupLockName]);
        if ((int)$setupLock->fetchColumn() !== 1) {
            throw new RuntimeException('Account setup is already in progress. Please try again.');
        }

        try {
            install_schema($pdo);
            $pdo->beginTransaction();
            $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($count > 0) {
                throw new RuntimeException('An administrator account already exists. Sign in instead.');
            }
            $statement = $pdo->prepare(
                'INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)'
            );
            $statement->execute([$fullName, $username, password_hash($password, PASSWORD_DEFAULT), 'admin']);
            $userId = (int)$pdo->lastInsertId();
            $pdo->commit();
            $releaseLock = $pdo->prepare('SELECT RELEASE_LOCK(?)');
            $releaseLock->execute([$setupLockName]);
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            unset($_SESSION['_csrf']);
            flash('success', 'Your administrator account is ready.');
            redirect(app_path('dashboard'));
        } catch (Throwable $error) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $releaseLock = $pdo->prepare('SELECT RELEASE_LOCK(?)');
            $releaseLock->execute([$setupLockName]);
            if ($error instanceof PDOException && (string)$error->getCode() === '23000') {
                throw new InvalidArgumentException('That username is already in use.');
            }
            throw $error;
        }
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('success', 'You have signed out.');
        redirect(app_path('login'));
    }

    if (!$user) {
        redirect(app_path('login'));
    }

    switch ($action) {
        case 'save-product':
            require_role($user, ['admin']);
            save_product($pdo, $user);
            break;
        case 'toggle-product':
            require_role($user, ['admin']);
            toggle_product($pdo);
            break;
        case 'add-variant':
            require_role($user, ['admin']);
            add_variant($pdo, $user);
            break;
        case 'update-variant':
            require_role($user, ['admin']);
            update_variant($pdo);
            break;
        case 'adjust-stock':
            require_role($user, ['admin']);
            adjust_stock($pdo, $user);
            break;
        case 'add-cart':
            require_role($user, ['admin', 'cashier']);
            add_cart_item($pdo);
            break;
        case 'update-cart':
            require_role($user, ['admin', 'cashier']);
            update_cart_item($pdo);
            break;
        case 'remove-cart':
            require_role($user, ['admin', 'cashier']);
            $variantId = input_id('variant_id');
            unset($_SESSION['cart'][$variantId]);
            flash('success', 'Item removed from the sale.');
            redirect(app_path('pos'));
        case 'checkout':
            require_role($user, ['admin', 'cashier']);
            checkout($pdo, $user);
            break;
        case 'create-user':
            require_role($user, ['admin']);
            create_user($pdo);
            break;
        case 'toggle-user':
            require_role($user, ['admin']);
            toggle_user($pdo, $user);
            break;
        default:
            throw new InvalidArgumentException('That action is not available.');
    }
}

function save_product(PDO $pdo, array $user): never
{
    $productId = (int)($_POST['product_id'] ?? 0);
    $isNew = $productId < 1;
    $name = input_text('name', 140);
    $brand = input_text('brand', 100);
    $description = input_text('description', 2000);
    $category = (int)($_POST['category_id'] ?? 0);
    if ($name === '') {
        throw new InvalidArgumentException('Product name is required.');
    }
    if ($category > 0) {
        $check = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
        $check->execute([$category]);
        if (!$check->fetchColumn()) {
            throw new InvalidArgumentException('Choose a valid category.');
        }
    }

    $pdo->beginTransaction();
    try {
        if ($productId > 0) {
            $statement = $pdo->prepare(
                'UPDATE products SET category_id = ?, name = ?, brand = ?, description = ? WHERE id = ?'
            );
            $statement->execute([$category ?: null, $name, $brand ?: null, $description ?: null, $productId]);
            if ($statement->rowCount() === 0) {
                $check = $pdo->prepare('SELECT id FROM products WHERE id = ?');
                $check->execute([$productId]);
                if (!$check->fetchColumn()) {
                    throw new InvalidArgumentException('Product not found.');
                }
            }
            $sku = input_text('new_sku', 60);
            if ($sku !== '') {
                insert_variant($pdo, $productId, (int)$user['id']);
            }
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO products (category_id, name, brand, description) VALUES (?, ?, ?, ?)'
            );
            $statement->execute([$category ?: null, $name, $brand ?: null, $description ?: null]);
            $productId = (int)$pdo->lastInsertId();
            insert_variant($pdo, $productId, (int)$user['id']);
        }
        $pdo->commit();
        flash('success', $isNew ? 'Product created.' : 'Product saved.');
        redirect(app_path('products', ['edit' => $productId]));
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ($error instanceof PDOException && (string)$error->getCode() === '23000') {
            throw new InvalidArgumentException('That SKU is already in use.');
        }
        throw $error;
    }
}

function insert_variant(PDO $pdo, int $productId, int $userId): void
{
    $sku = input_text('new_sku', 60);
    if ($sku === '') {
        throw new InvalidArgumentException('SKU is required for a new variation.');
    }
    $size = input_text('new_size', 40);
    $color = input_text('new_color', 60);
    $cost = input_money('new_cost_price');
    $price = input_money('new_selling_price', false);
    $stock = input_quantity('new_stock');
    $reorder = input_quantity('new_reorder_level');
    $statement = $pdo->prepare(
        'INSERT INTO product_variants
            (product_id, sku, size, color, cost_price, selling_price, stock_quantity, reorder_level)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $statement->execute([$productId, $sku, $size ?: null, $color ?: null, $cost, $price, $stock, $reorder]);
    if ($stock > 0) {
        $movement = $pdo->prepare(
            'INSERT INTO stock_movements (variant_id, user_id, movement_type, quantity_change, note)
             VALUES (?, ?, ?, ?, ?)'
        );
        $movement->execute([(int)$pdo->lastInsertId(), $userId, 'restock', $stock, 'Opening stock']);
    }
}

function toggle_product(PDO $pdo): never
{
    $id = input_id('product_id');
    $statement = $pdo->prepare('UPDATE products SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?');
    $statement->execute([$id]);
    flash('success', 'Product availability updated.');
    redirect(app_path('products'));
}

function add_variant(PDO $pdo, array $user): never
{
    $productId = input_id('product_id');
    $exists = $pdo->prepare('SELECT id FROM products WHERE id = ? AND is_active = 1');
    $exists->execute([$productId]);
    if (!$exists->fetchColumn()) {
        throw new InvalidArgumentException('Choose an active product.');
    }
    try {
        $pdo->beginTransaction();
        insert_variant($pdo, $productId, (int)$user['id']);
        $pdo->commit();
    } catch (PDOException $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        if ((string)$error->getCode() === '23000') {
            throw new InvalidArgumentException('That SKU is already in use.');
        }
        throw $error;
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
    flash('success', 'Variation added.');
    redirect(app_path('products', ['edit' => $productId]));
}

function update_variant(PDO $pdo): never
{
    $variantId = input_id('variant_id');
    $productId = input_id('product_id');
    $sku = input_text('sku', 60);
    $size = input_text('size', 40);
    $color = input_text('color', 60);
    $cost = input_money('cost_price');
    $price = input_money('selling_price', false);
    $reorder = input_quantity('reorder_level');
    try {
        $statement = $pdo->prepare(
            'UPDATE product_variants
             SET sku = ?, size = ?, color = ?, cost_price = ?, selling_price = ?, reorder_level = ?
             WHERE id = ? AND product_id = ?'
        );
        $statement->execute([$sku, $size ?: null, $color ?: null, $cost, $price, $reorder, $variantId, $productId]);
    } catch (PDOException $error) {
        if ((string)$error->getCode() === '23000') {
            throw new InvalidArgumentException('That SKU is already in use.');
        }
        throw $error;
    }
    flash('success', 'Variation details updated. Use Inventory to change its stock count.');
    redirect(app_path('products', ['edit' => $productId]));
}

function adjust_stock(PDO $pdo, array $user): never
{
    $variantId = input_id('variant_id');
    $delta = filter_var($_POST['quantity_change'] ?? null, FILTER_VALIDATE_INT);
    $note = input_text('note', 240);
    if ($delta === false || $delta === 0 || $note === '') {
        throw new InvalidArgumentException('Enter a non-zero stock change and a reason.');
    }

    $pdo->beginTransaction();
    try {
        $find = $pdo->prepare(
            'SELECT stock_quantity FROM product_variants WHERE id = ? AND is_active = 1 FOR UPDATE'
        );
        $find->execute([$variantId]);
        $current = $find->fetchColumn();
        if ($current === false || (int)$current + $delta < 0) {
            throw new InvalidArgumentException('That change would leave the item with negative stock.');
        }
        $update = $pdo->prepare('UPDATE product_variants SET stock_quantity = stock_quantity + ? WHERE id = ?');
        $update->execute([$delta, $variantId]);
        $insert = $pdo->prepare(
            'INSERT INTO stock_movements (variant_id, user_id, movement_type, quantity_change, note)
             VALUES (?, ?, ?, ?, ?)'
        );
        $insert->execute([$variantId, $user['id'], $delta > 0 ? 'restock' : 'adjustment', $delta, $note]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
    flash('success', 'Stock updated and recorded in the movement history.');
    redirect(app_path('inventory'));
}

function add_cart_item(PDO $pdo): never
{
    $variantId = input_id('variant_id');
    $quantity = input_quantity('quantity', false);
    $statement = $pdo->prepare(
        'SELECT v.stock_quantity FROM product_variants v
         JOIN products p ON p.id = v.product_id
         WHERE v.id = ? AND v.is_active = 1 AND p.is_active = 1'
    );
    $statement->execute([$variantId]);
    $stock = $statement->fetchColumn();
    $current = (int)($_SESSION['cart'][$variantId] ?? 0);
    if ($stock === false || $current + $quantity > (int)$stock) {
        throw new InvalidArgumentException('There is not enough available stock for that quantity.');
    }
    $_SESSION['cart'][$variantId] = $current + $quantity;
    flash('success', 'Item added to the sale.');
    redirect(app_path('pos'));
}

function update_cart_item(PDO $pdo): never
{
    $variantId = input_id('variant_id');
    $quantity = input_quantity('quantity');
    if ($quantity === 0) {
        unset($_SESSION['cart'][$variantId]);
    } else {
        $statement = $pdo->prepare(
            'SELECT v.stock_quantity FROM product_variants v
             JOIN products p ON p.id = v.product_id
             WHERE v.id = ? AND v.is_active = 1 AND p.is_active = 1'
        );
        $statement->execute([$variantId]);
        $stock = $statement->fetchColumn();
        if ($stock === false || $quantity > (int)$stock) {
            throw new InvalidArgumentException('There is not enough available stock for that quantity.');
        }
        $_SESSION['cart'][$variantId] = $quantity;
    }
    flash('success', 'Sale cart updated.');
    redirect(app_path('pos'));
}

function checkout(PDO $pdo, array $user): never
{
    $cart = $_SESSION['cart'] ?? [];
    if (!$cart) {
        throw new InvalidArgumentException('Add at least one item before completing the sale.');
    }
    $discount = input_money('discount_amount');
    $payment = (string)($_POST['payment_method'] ?? 'cash');
    if (!in_array($payment, ['cash', 'other'], true)) {
        throw new InvalidArgumentException('Choose a valid payment method.');
    }

    $pdo->beginTransaction();
    try {
        $lines = [];
        $subtotal = 0.0;
        $select = $pdo->prepare(
            'SELECT v.id, v.sku, v.size, v.color, v.cost_price, v.selling_price, v.stock_quantity,
                    p.name AS product_name
             FROM product_variants v JOIN products p ON p.id = v.product_id
             WHERE v.id = ? AND v.is_active = 1 AND p.is_active = 1 FOR UPDATE'
        );
        foreach ($cart as $variantId => $quantity) {
            $quantity = (int)$quantity;
            if ($quantity < 1) {
                throw new InvalidArgumentException('Sale quantities must be at least one.');
            }
            $select->execute([(int)$variantId]);
            $variant = $select->fetch();
            if (!$variant || (int)$variant['stock_quantity'] < $quantity) {
                throw new InvalidArgumentException('Stock changed while this sale was open. Review the cart and try again.');
            }
            $variant['quantity'] = $quantity;
            $variant['line_total'] = round((float)$variant['selling_price'] * $quantity, 2);
            $subtotal += $variant['line_total'];
            $lines[] = $variant;
        }
        if ($discount > round($subtotal, 2)) {
            throw new InvalidArgumentException('Discount cannot be greater than the sale subtotal.');
        }

        $receipt = 'BT-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $insertSale = $pdo->prepare(
            'INSERT INTO sales (receipt_number, cashier_id, subtotal, discount_amount, total_amount, payment_method)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $total = round($subtotal - $discount, 2);
        $insertSale->execute([$receipt, $user['id'], round($subtotal, 2), $discount, $total, $payment]);
        $saleId = (int)$pdo->lastInsertId();

        $insertLine = $pdo->prepare(
            'INSERT INTO sale_items
                (sale_id, variant_id, product_name, sku, size, color, quantity, unit_price, unit_cost, line_total)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $updateStock = $pdo->prepare(
            'UPDATE product_variants SET stock_quantity = stock_quantity - ? WHERE id = ?'
        );
        $movement = $pdo->prepare(
            'INSERT INTO stock_movements
                (variant_id, user_id, movement_type, quantity_change, note, reference_id)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ($lines as $line) {
            $insertLine->execute([
                $saleId,
                $line['id'],
                $line['product_name'],
                $line['sku'],
                $line['size'],
                $line['color'],
                $line['quantity'],
                $line['selling_price'],
                $line['cost_price'],
                $line['line_total'],
            ]);
            $updateStock->execute([$line['quantity'], $line['id']]);
            $movement->execute([
                $line['id'],
                $user['id'],
                'sale',
                -$line['quantity'],
                'Sale ' . $receipt,
                $saleId,
            ]);
        }
        $pdo->commit();
        unset($_SESSION['cart']);
        flash('success', 'Sale completed and stock updated.');
        redirect(app_path('receipt', ['id' => $saleId]));
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

function create_user(PDO $pdo): never
{
    $name = input_text('full_name', 120);
    $username = input_text('username', 60);
    $password = (string)($_POST['password'] ?? '');
    $role = (string)($_POST['role'] ?? 'cashier');
    if ($name === '' || !preg_match('/^[A-Za-z0-9._-]{3,60}$/', $username)) {
        throw new InvalidArgumentException('Enter a name and a username with at least 3 letters or numbers.');
    }
    if (strlen($password) < 10 || !in_array($role, ['admin', 'cashier'], true)) {
        throw new InvalidArgumentException('Use a password of at least 10 characters and choose a valid role.');
    }
    try {
        $statement = $pdo->prepare(
            'INSERT INTO users (full_name, username, password_hash, role) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$name, $username, password_hash($password, PASSWORD_DEFAULT), $role]);
    } catch (PDOException $error) {
        if ((string)$error->getCode() === '23000') {
            throw new InvalidArgumentException('That username is already in use.');
        }
        throw $error;
    }
    flash('success', 'Team member added.');
    redirect(app_path('users'));
}

function toggle_user(PDO $pdo, array $user): never
{
    $id = input_id('user_id');
    if ($id === (int)$user['id']) {
        throw new InvalidArgumentException('You cannot disable your own account.');
    }
    $pdo->beginTransaction();
    try {
        $activeAdmins = $pdo->query(
            "SELECT id FROM users WHERE role = 'admin' AND is_active = 1 ORDER BY id FOR UPDATE"
        )->fetchAll(PDO::FETCH_COLUMN);
        $find = $pdo->prepare('SELECT role, is_active FROM users WHERE id = ? FOR UPDATE');
        $find->execute([$id]);
        $target = $find->fetch();
        if (!$target) {
            throw new InvalidArgumentException('Team member not found.');
        }
        if ($target['role'] === 'admin' && (bool)$target['is_active'] && count($activeAdmins) <= 1) {
            throw new InvalidArgumentException('You cannot disable the last active administrator.');
        }
        $statement = $pdo->prepare('UPDATE users SET is_active = IF(is_active = 1, 0, 1) WHERE id = ?');
        $statement->execute([$id]);
        $pdo->commit();
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
    flash('success', 'Account availability updated.');
    redirect(app_path('users'));
}