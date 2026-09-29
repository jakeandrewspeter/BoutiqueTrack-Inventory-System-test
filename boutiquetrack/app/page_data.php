<?php
declare(strict_types=1);

function page_data(PDO $pdo, string $page, array $user): array
{
    switch ($page) {
        case 'dashboard':
            $cashierOnly = $user['role'] === 'admin' ? '' : ' AND cashier_id = ?';
            $todayQuery = $pdo->prepare(
                'SELECT COALESCE(SUM(total_amount), 0) FROM sales
                 WHERE DATE(sold_at) = CURRENT_DATE()' . $cashierOnly
            );
            $monthQuery = $pdo->prepare(
                "SELECT COALESCE(SUM(total_amount), 0) FROM sales
                 WHERE sold_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')" . $cashierOnly
            );
            $countQuery = $pdo->prepare(
                'SELECT COUNT(*) FROM sales WHERE DATE(sold_at) = CURRENT_DATE()' . $cashierOnly
            );
            foreach ([$todayQuery, $monthQuery, $countQuery] as $query) {
                if ($cashierOnly !== '') {
                    $query->execute([$user['id']]);
                } else {
                    $query->execute();
                }
            }
            $today = (float)$todayQuery->fetchColumn();
            $month = (float)$monthQuery->fetchColumn();
            $salesToday = (int)$countQuery->fetchColumn();
            $lowStock = $pdo->query(
                'SELECT v.id, v.sku, v.size, v.color, v.stock_quantity, v.reorder_level,
                        p.name AS product_name
                 FROM product_variants v JOIN products p ON p.id = v.product_id
                 WHERE p.is_active = 1 AND v.is_active = 1 AND v.stock_quantity <= v.reorder_level
                 ORDER BY v.stock_quantity ASC, p.name LIMIT 6'
            )->fetchAll();
            $lowStockCount = (int)$pdo->query(
                'SELECT COUNT(*) FROM product_variants v JOIN products p ON p.id = v.product_id
                 WHERE p.is_active = 1 AND v.is_active = 1 AND v.stock_quantity <= v.reorder_level'
            )->fetchColumn();
            $productsSoldCount = (int)$pdo->query(
                'SELECT COUNT(DISTINCT si.product_name)
                 FROM sale_items si JOIN sales s ON s.id = si.sale_id
                 WHERE s.sold_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)' .
                 ($user['role'] === 'admin' ? '' : ' AND s.cashier_id = ' . (int)$user['id'])
            )->fetchColumn();
            $topQuery = $pdo->prepare(
                'SELECT si.product_name, SUM(si.quantity) AS units, SUM(si.line_total) AS revenue
                 FROM sale_items si JOIN sales s ON s.id = si.sale_id
                 WHERE s.sold_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)' .
                 ($user['role'] === 'admin' ? '' : ' AND s.cashier_id = ?') .
                ' GROUP BY si.product_name ORDER BY units DESC, revenue DESC LIMIT 5'
            );
            $recentQuery = $pdo->prepare(
                'SELECT s.id, s.receipt_number, s.total_amount, s.sold_at, u.full_name
                 FROM sales s JOIN users u ON u.id = s.cashier_id' .
                 ($user['role'] === 'admin' ? '' : ' WHERE s.cashier_id = ?') .
                ' ORDER BY s.sold_at DESC LIMIT 6'
            );
            foreach ([$topQuery, $recentQuery] as $query) {
                if ($user['role'] === 'admin') {
                    $query->execute();
                } else {
                    $query->execute([$user['id']]);
                }
            }
            $topProducts = $topQuery->fetchAll();
            $recent = $recentQuery->fetchAll();
            return compact('today', 'month', 'salesToday', 'lowStock', 'lowStockCount', 'productsSoldCount', 'topProducts', 'recent');

        case 'products':
            $search = trim((string)($_GET['q'] ?? ''));
            $statement = $pdo->prepare(
                'SELECT p.id, p.name, p.brand, p.is_active, c.name AS category,
                        COUNT(v.id) AS variant_count,
                        COALESCE(SUM(v.stock_quantity), 0) AS total_stock,
                        COALESCE(MIN(v.selling_price), 0) AS from_price
                 FROM products p
                 LEFT JOIN categories c ON c.id = p.category_id
                 LEFT JOIN product_variants v ON v.product_id = p.id AND v.is_active = 1
                 WHERE (? = ? OR p.name LIKE ? OR p.brand LIKE ? OR v.sku LIKE ?)
                 GROUP BY p.id, p.name, p.brand, p.is_active, c.name
                 ORDER BY p.is_active DESC, p.name'
            );
            $like = '%' . $search . '%';
            $statement->execute([$search, $search, $like, $like, $like]);
            return ['products' => $statement->fetchAll(), 'search' => $search];

        case 'product-form':
            $productId = (int)($_GET['edit'] ?? 0);
            $product = null;
            $variants = [];
            if ($productId > 0) {
                $statement = $pdo->prepare('SELECT * FROM products WHERE id = ?');
                $statement->execute([$productId]);
                $product = $statement->fetch();
                if (!$product) {
                    throw new InvalidArgumentException('Product not found.');
                }
                $statement = $pdo->prepare(
                    'SELECT * FROM product_variants WHERE product_id = ? ORDER BY sku'
                );
                $statement->execute([$productId]);
                $variants = $statement->fetchAll();
            }
            return [
                'product' => $product,
                'variants' => $variants,
                'categories' => $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll(),
            ];

        case 'inventory':
            $variants = $pdo->query(
                'SELECT v.id, v.sku, v.size, v.color, v.stock_quantity, v.reorder_level,
                        v.selling_price, p.name AS product_name, p.is_active AS product_active
                 FROM product_variants v JOIN products p ON p.id = v.product_id
                 WHERE v.is_active = 1 AND p.is_active = 1
                 ORDER BY (v.stock_quantity <= v.reorder_level) DESC, p.name, v.sku'
            )->fetchAll();
            $movements = $pdo->query(
                'SELECT m.created_at, m.movement_type, m.quantity_change, m.note, m.id,
                        v.sku, p.name AS product_name, u.full_name
                 FROM stock_movements m
                 JOIN product_variants v ON v.id = m.variant_id
                 JOIN products p ON p.id = v.product_id
                 JOIN users u ON u.id = m.user_id
                 ORDER BY m.created_at DESC LIMIT 12'
            )->fetchAll();
            return compact('variants', 'movements');

        case 'pos':
            $search = trim((string)($_GET['q'] ?? ''));
            $statement = $pdo->prepare(
                'SELECT v.id, v.sku, v.size, v.color, v.selling_price, v.stock_quantity,
                        p.name AS product_name
                 FROM product_variants v JOIN products p ON p.id = v.product_id
                 WHERE v.is_active = 1 AND p.is_active = 1 AND v.stock_quantity > 0
                   AND (? = ? OR p.name LIKE ? OR v.sku LIKE ? OR v.color LIKE ?)
                 ORDER BY p.name, v.size LIMIT 60'
            );
            $like = '%' . $search . '%';
            $statement->execute([$search, $search, $like, $like, $like]);
            $items = $statement->fetchAll();
            $cart = [];
            $cartSubtotal = 0.0;
            foreach ($_SESSION['cart'] ?? [] as $variantId => $quantity) {
                $statement = $pdo->prepare(
                    'SELECT v.id, v.sku, v.size, v.color, v.selling_price, v.stock_quantity,
                            p.name AS product_name
                     FROM product_variants v JOIN products p ON p.id = v.product_id
                     WHERE v.id = ? AND v.is_active = 1 AND p.is_active = 1'
                );
                $statement->execute([(int)$variantId]);
                $line = $statement->fetch();
                if (!$line) {
                    unset($_SESSION['cart'][$variantId]);
                    continue;
                }
                $line['quantity'] = (int)$quantity;
                $line['line_total'] = (float)$line['selling_price'] * (int)$quantity;
                $cartSubtotal += $line['line_total'];
                $cart[] = $line;
            }
            return compact('items', 'search', 'cart', 'cartSubtotal');

        case 'sales':
            $isAdmin = $user['role'] === 'admin';
            $sql = 'SELECT s.id, s.receipt_number, s.subtotal, s.discount_amount, s.total_amount,
                           s.payment_method, s.sold_at, u.full_name
                    FROM sales s JOIN users u ON u.id = s.cashier_id';
            if (!$isAdmin) {
                $statement = $pdo->prepare($sql . ' WHERE s.cashier_id = ? ORDER BY s.sold_at DESC LIMIT 100');
                $statement->execute([$user['id']]);
            } else {
                $statement = $pdo->query($sql . ' ORDER BY s.sold_at DESC LIMIT 100');
            }
            return ['sales' => $statement->fetchAll()];

        case 'receipt':
            $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id === null || $id < 1) {
                throw new InvalidArgumentException('Choose a valid receipt.');
            }
            $sql = 'SELECT s.*, u.full_name AS cashier_name, u.id AS cashier_id
                    FROM sales s JOIN users u ON u.id = s.cashier_id WHERE s.id = ?';
            $statement = $pdo->prepare($sql);
            $statement->execute([$id]);
            $sale = $statement->fetch();
            if (!$sale || ($user['role'] !== 'admin' && (int)$sale['cashier_id'] !== (int)$user['id'])) {
                throw new InvalidArgumentException('That receipt could not be found.');
            }
            $statement = $pdo->prepare('SELECT * FROM sale_items WHERE sale_id = ? ORDER BY id');
            $statement->execute([$id]);
            return ['sale' => $sale, 'lines' => $statement->fetchAll()];

        case 'reports':
            require_role($user, ['admin']);
            $period = (string)($_GET['period'] ?? 'month');
            $period = in_array($period, ['today', 'week', 'month'], true) ? $period : 'month';
            $where = match ($period) {
                'today' => 'DATE(sold_at) = CURRENT_DATE()',
                'week' => 'sold_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY)',
                default => "sold_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')",
            };
            $statement = $pdo->query(
                "SELECT COUNT(*) AS transaction_count, COALESCE(SUM(subtotal), 0) AS subtotal,
                        COALESCE(SUM(discount_amount), 0) AS discounts,
                        COALESCE(SUM(total_amount), 0) AS revenue
                 FROM sales WHERE $where"
            );
            $summary = $statement->fetch();
            $statement = $pdo->query(
                "SELECT si.product_name, SUM(si.quantity) AS units,
                        SUM(si.line_total) AS gross_sales,
                        SUM(si.quantity * si.unit_cost) AS cost_of_goods
                 FROM sale_items si JOIN sales s ON s.id = si.sale_id
                 WHERE $where
                 GROUP BY si.product_name
                 ORDER BY units DESC, gross_sales DESC LIMIT 20"
            );
            $products = $statement->fetchAll();
            $daily = $pdo->query(
                "SELECT DATE(sold_at) AS sale_date, COUNT(*) AS transactions,
                        SUM(total_amount) AS revenue
                 FROM sales WHERE sold_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 13 DAY)
                 GROUP BY DATE(sold_at) ORDER BY sale_date DESC"
            )->fetchAll();
            return compact('period', 'summary', 'products', 'daily');

        case 'users':
            require_role($user, ['admin']);
            return ['users' => $pdo->query(
                'SELECT id, full_name, username, role, is_active, created_at FROM users ORDER BY full_name'
            )->fetchAll()];

        default:
            throw new InvalidArgumentException('Page not found.');
    }
}

function export_report(PDO $pdo, string $period): never
{
    $period = in_array($period, ['today', 'week', 'month'], true) ? $period : 'month';
    $where = match ($period) {
        'today' => 'DATE(s.sold_at) = CURRENT_DATE()',
        'week' => 's.sold_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY)',
        default => "s.sold_at >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01')",
    };
    $statement = $pdo->query(
        "SELECT s.receipt_number, s.sold_at, u.full_name AS cashier, s.subtotal,
                s.discount_amount, s.total_amount, s.payment_method
         FROM sales s JOIN users u ON u.id = s.cashier_id
         WHERE $where ORDER BY s.sold_at DESC"
    );
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="boutiquetrack-' . $period . '-sales.csv"');
    $output = fopen('php://output', 'wb');
    fputcsv($output, ['Receipt', 'Date', 'Cashier', 'Subtotal (SSP)', 'Discount (SSP)', 'Total (SSP)', 'Payment']);
    while ($row = $statement->fetch(PDO::FETCH_NUM)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}