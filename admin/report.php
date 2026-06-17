<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../admingear.php");
    exit;
}

require_once __DIR__ . '/../database/db_connect.php';

$db = new Database();
$conn = $db->getConnection();

function scalarValue(mysqli $conn, string $sql, $fallback = 0)
{
    $result = $conn->query($sql);
    if (!$result) {
        return $fallback;
    }
    $row = $result->fetch_assoc();
    return $row ? (array_values($row)[0] ?? $fallback) : $fallback;
}

function rowsValue(mysqli $conn, string $sql): array
{
    $result = $conn->query($sql);
    if (!$result) {
        return [];
    }
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function pctChange(float $current, float $previous): string
{
    if ($previous <= 0) {
        return $current > 0 ? '+100%' : '0%';
    }
    $change = (($current - $previous) / $previous) * 100;
    return ($change >= 0 ? '+' : '') . number_format($change, 1) . '%';
}

function buildLinePoints(array $values, int $width = 640, int $height = 170, int $padding = 18): string
{
    $count = count($values);
    if ($count === 0) {
        return '';
    }

    $max = max($values);
    $max = $max > 0 ? $max : 1;
    $usableWidth = $width - ($padding * 2);
    $usableHeight = $height - ($padding * 2);
    $points = [];

    foreach ($values as $index => $value) {
        $x = $count > 1 ? $padding + (($usableWidth / ($count - 1)) * $index) : $width / 2;
        $y = $height - $padding - (($value / $max) * $usableHeight);
        $points[] = number_format($x, 1, '.', '') . ',' . number_format($y, 1, '.', '');
    }

    return implode(' ', $points);
}

function clampPercent(float $value): float
{
    return max(0, min(100, $value));
}

function metricPercent(float $part, float $whole): float
{
    return $whole > 0 ? clampPercent(($part / $whole) * 100) : 0;
}

function buildConicGradient(array $rows, string $valueKey, array $colors): string
{
    $total = 0;
    foreach ($rows as $row) {
        $total += (float)($row[$valueKey] ?? 0);
    }
    if ($total <= 0) {
        return '#edf2f9 0 360deg';
    }

    $segments = [];
    $start = 0;
    $colorCount = max(1, count($colors));
    foreach (array_values($rows) as $index => $row) {
        $value = (float)($row[$valueKey] ?? 0);
        if ($value <= 0) {
            continue;
        }
        $end = $start + (($value / $total) * 360);
        $segments[] = $colors[$index % $colorCount] . ' ' . number_format($start, 2, '.', '') . 'deg ' . number_format($end, 2, '.', '') . 'deg';
        $start = $end;
    }

    return implode(', ', $segments);
}

function getPhilippineMapPoint(string $city): array
{
    $key = strtolower(trim($city));
    $key = preg_replace('/\s+city$/', '', $key);
    $known = [
        'calamba' => [266, 346],
        'laguna' => [266, 346],
        'manila' => [244, 318],
        'quezon' => [252, 308],
        'makati' => [248, 323],
        'taguig' => [254, 326],
        'pasig' => [254, 320],
        'mandaluyong' => [249, 318],
        'pasay' => [244, 326],
        'paranaque' => [246, 331],
        'muntinlupa' => [255, 338],
        'cavite' => [235, 348],
        'batangas' => [258, 374],
        'lucena' => [288, 370],
        'naga' => [349, 420],
        'legazpi' => [388, 455],
        'baguio' => [213, 228],
        'la union' => [198, 239],
        'dagupan' => [207, 258],
        'tarlac' => [224, 281],
        'angeles' => [218, 292],
        'san fernando' => [224, 300],
        'olongapo' => [194, 303],
        'subic' => [190, 303],
        'tuguegarao' => [260, 150],
        'laoag' => [182, 119],
        'vigan' => [180, 172],
        'cebu' => [399, 545],
        'mandaue' => [397, 539],
        'lapu-lapu' => [405, 541],
        'iloilo' => [317, 560],
        'bacolod' => [352, 545],
        'dumaguete' => [384, 595],
        'tacloban' => [462, 520],
        'ormoc' => [440, 525],
        'cagayan de oro' => [486, 649],
        'davao' => [559, 738],
        'tagum' => [553, 706],
        'general santos' => [538, 788],
        'gensan' => [538, 788],
        'zamboanga' => [350, 735],
        'butuan' => [532, 635],
        'surigao' => [520, 585],
        'cotabato' => [466, 733],
        'kidapawan' => [515, 747],
    ];

    if (isset($known[$key])) {
        return $known[$key];
    }

    $hash = abs(crc32($key ?: 'unknown'));
    return [230 + ($hash % 300), 270 + (($hash >> 8) % 470)];
}

$totalOrders = (int) scalarValue($conn, "SELECT COUNT(*) FROM orders_tbl");
$completedOrders = (int) scalarValue($conn, "SELECT COUNT(*) FROM orders_tbl WHERE LOWER(order_status) = 'completed'");
$pendingOrders = (int) scalarValue($conn, "SELECT COUNT(*) FROM orders_tbl WHERE LOWER(order_status) IN ('pending', 'on process')");
$canceledOrders = (int) scalarValue($conn, "SELECT COUNT(*) FROM orders_tbl WHERE LOWER(order_status) = 'canceled'");
$totalUsers = (int) scalarValue($conn, "SELECT COUNT(*) FROM users_tbl");
$totalProducts = (int) scalarValue($conn, "SELECT COUNT(*) FROM products_tbl WHERE archived_at IS NULL");
$archivedProducts = (int) scalarValue($conn, "SELECT COUNT(*) FROM products_tbl WHERE archived_at IS NOT NULL");
$lowStock = (int) scalarValue($conn, "SELECT COUNT(*) FROM products_tbl WHERE archived_at IS NULL AND stock_qty < 5");
$totalStock = (int) scalarValue($conn, "SELECT COALESCE(SUM(stock_qty), 0) FROM products_tbl WHERE archived_at IS NULL");
$activeDeals = (int) scalarValue($conn, "SELECT COUNT(*) FROM coupon_code WHERE is_active = 1 AND valid_until >= NOW()");

$grossRevenue = (float) scalarValue($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders_tbl WHERE LOWER(order_status) <> 'canceled'");
$completedRevenue = (float) scalarValue($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders_tbl WHERE LOWER(order_status) = 'completed'");
$todayRevenue = (float) scalarValue($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders_tbl WHERE DATE(created_at) = CURDATE() AND LOWER(order_status) <> 'canceled'");
$monthRevenue = (float) scalarValue($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders_tbl WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) AND LOWER(order_status) <> 'canceled'");
$lastMonthRevenue = (float) scalarValue($conn, "SELECT COALESCE(SUM(total_amount), 0) FROM orders_tbl WHERE created_at >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01') AND created_at < DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LOWER(order_status) <> 'canceled'");
$averageOrderValue = $totalOrders > 0 ? $grossRevenue / $totalOrders : 0;
$completionRate = $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
$paidPayments = (int) scalarValue($conn, "SELECT COUNT(*) FROM payments_tbl WHERE LOWER(status) = 'paid'");
$totalPayments = (int) scalarValue($conn, "SELECT COUNT(*) FROM payments_tbl");
$paidRate = metricPercent($paidPayments, $totalPayments);
$cancelRate = metricPercent($canceledOrders, $totalOrders);
$activeFulfillment = (int) scalarValue($conn, "SELECT COUNT(*) FROM orders_tbl WHERE LOWER(order_status) IN ('on process', 'shipped', 'delivered')");
$fulfillmentRate = metricPercent($activeFulfillment + $completedOrders, $totalOrders);
$stockHealthRate = $totalProducts > 0 ? clampPercent((($totalProducts - $lowStock) / $totalProducts) * 100) : 0;
$repeatCustomers = (int) scalarValue($conn, "
    SELECT COUNT(*) FROM (
        SELECT user_id
        FROM orders_tbl
        WHERE user_id IS NOT NULL AND LOWER(order_status) <> 'canceled'
        GROUP BY user_id
        HAVING COUNT(*) > 1
    ) repeat_buyers
");
$orderingCustomers = (int) scalarValue($conn, "SELECT COUNT(DISTINCT user_id) FROM orders_tbl WHERE user_id IS NOT NULL AND LOWER(order_status) <> 'canceled'");
$repeatCustomerRate = metricPercent($repeatCustomers, $orderingCustomers);
$totalUnitsSold = (int) scalarValue($conn, "
    SELECT COALESCE(SUM(oi.quantity), 0)
    FROM order_items_tbl oi
    JOIN orders_tbl o ON oi.order_id = o.order_id
    WHERE LOWER(o.order_status) <> 'canceled'
");
$totalProductBuyers = (int) scalarValue($conn, "
    SELECT COUNT(DISTINCT COALESCE(CAST(o.user_id AS CHAR), CONCAT('guest-', o.order_id)))
    FROM order_items_tbl oi
    JOIN orders_tbl o ON oi.order_id = o.order_id
    WHERE LOWER(o.order_status) <> 'canceled'
");

$statusRows = rowsValue($conn, "
    SELECT order_status, COUNT(*) AS total, COALESCE(SUM(total_amount), 0) AS amount
    FROM orders_tbl
    GROUP BY order_status
    ORDER BY total DESC
");

$categoryRows = rowsValue($conn, "
    SELECT COALESCE(c.category_name, 'Uncategorized') AS category_name,
           COUNT(p.product_id) AS products,
           COALESCE(SUM(p.stock_qty), 0) AS stock,
           COALESCE(AVG(p.price), 0) AS avg_price
    FROM products_tbl p
    LEFT JOIN category_tbl c ON p.category_id = c.category_id
    WHERE p.archived_at IS NULL
    GROUP BY c.category_name
    ORDER BY products DESC, category_name ASC
");

$topProductRows = rowsValue($conn, "
    SELECT p.name, COALESCE(c.category_name, 'Uncategorized') AS category_name,
           COALESCE(SUM(oi.quantity), 0) AS units,
           COUNT(DISTINCT COALESCE(CAST(o.user_id AS CHAR), CONCAT('guest-', o.order_id))) AS buyers,
           COALESCE(SUM(oi.quantity * oi.price_at_checkout), 0) AS revenue
    FROM order_items_tbl oi
    JOIN products_tbl p ON oi.product_id = p.product_id
    LEFT JOIN category_tbl c ON p.category_id = c.category_id
    JOIN orders_tbl o ON oi.order_id = o.order_id
    WHERE LOWER(o.order_status) <> 'canceled'
    GROUP BY p.product_id, p.name, c.category_name
    ORDER BY units DESC, revenue DESC
    LIMIT 5
");

$trendRows = rowsValue($conn, "
    SELECT DATE(created_at) AS order_day,
           COUNT(*) AS orders,
           COALESCE(SUM(CASE WHEN LOWER(order_status) <> 'canceled' THEN total_amount ELSE 0 END), 0) AS revenue
    FROM orders_tbl
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
    GROUP BY DATE(created_at)
    ORDER BY order_day ASC
");

$recentOrders = rowsValue($conn, "
    SELECT o.order_id, o.order_ref_code, o.total_amount, o.order_status, o.created_at,
           COALESCE(u.username, 'Guest') AS buyer
    FROM orders_tbl o
    LEFT JOIN users_tbl u ON o.user_id = u.user_id
    ORDER BY o.created_at DESC
    LIMIT 6
");

$paymentRows = rowsValue($conn, "
    SELECT status, COUNT(*) AS total
    FROM payments_tbl
    GROUP BY status
    ORDER BY total DESC
");

$channelRows = rowsValue($conn, "
    SELECT COALESCE(NULLIF(p.method, ''), 'Unknown') AS channel,
           COUNT(*) AS orders,
           COALESCE(SUM(o.total_amount), 0) AS revenue
    FROM orders_tbl o
    LEFT JOIN payments_tbl p ON o.order_id = p.order_id
    WHERE LOWER(o.order_status) <> 'canceled'
    GROUP BY COALESCE(NULLIF(p.method, ''), 'Unknown')
    ORDER BY revenue DESC, orders DESC
");

$monthlyRevenueRows = rowsValue($conn, "
    SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
           COALESCE(SUM(CASE WHEN LOWER(order_status) <> 'canceled' THEN total_amount ELSE 0 END), 0) AS revenue,
           COUNT(*) AS orders
    FROM orders_tbl
    WHERE created_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
    GROUP BY YEAR(created_at), MONTH(created_at), DATE_FORMAT(created_at, '%b %Y')
    ORDER BY YEAR(created_at), MONTH(created_at)
");

$locationRows = rowsValue($conn, "
    SELECT COALESCE(NULLIF(TRIM(sa.city), ''), 'Unknown') AS city,
           COUNT(DISTINCT o.order_id) AS orders,
           COALESCE(SUM(o.total_amount), 0) AS revenue
    FROM orders_tbl o
    LEFT JOIN shipping_address_tbl sa ON sa.order_ref_code = o.order_ref_code
    WHERE LOWER(o.order_status) <> 'canceled'
    GROUP BY COALESCE(NULLIF(TRIM(sa.city), ''), 'Unknown')
    ORDER BY orders DESC, revenue DESC
");

$activityRows = rowsValue($conn, "
    SELECT activity_type, message, created_at
    FROM admin_activity_tbl
    ORDER BY created_at DESC
    LIMIT 6
");

$maxCategoryProducts = 1;
foreach ($categoryRows as $row) {
    $maxCategoryProducts = max($maxCategoryProducts, (int) $row['products']);
}

$trendLookup = [];
foreach ($trendRows as $row) {
    $trendLookup[$row['order_day']] = $row;
}

$trendLabels = [];
$orderTrendValues = [];
$revenueTrendValues = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $trendLabels[] = date('M j', strtotime($day));
    $orderTrendValues[] = isset($trendLookup[$day]) ? (int) $trendLookup[$day]['orders'] : 0;
    $revenueTrendValues[] = isset($trendLookup[$day]) ? (float) $trendLookup[$day]['revenue'] : 0;
}

$orderLinePoints = buildLinePoints($orderTrendValues);
$revenueLinePoints = buildLinePoints($revenueTrendValues);
$maxTrendOrders = max($orderTrendValues) ?: 0;
$maxTrendRevenue = max($revenueTrendValues) ?: 0;
$channelColors = ['#0b7d75', '#35bdb2', '#7fd9d1', '#f5c518', '#f59f00', '#0b2fa8'];
$channelGradient = buildConicGradient($channelRows, 'revenue', $channelColors);
$maxMonthlyRevenue = 1;
foreach ($monthlyRevenueRows as $row) {
    $maxMonthlyRevenue = max($maxMonthlyRevenue, (float)$row['revenue']);
}

$maxLocationOrders = 1;
$mapLocations = [];
foreach ($locationRows as $row) {
    $city = trim((string)($row['city'] ?? 'Unknown'));
    [$x, $y] = getPhilippineMapPoint($city);
    $orders = (int)($row['orders'] ?? 0);
    $maxLocationOrders = max($maxLocationOrders, $orders);
    $mapLocations[] = [
        'city' => $city,
        'orders' => $orders,
        'revenue' => (float)($row['revenue'] ?? 0),
        'x' => $x,
        'y' => $y,
    ];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports &amp; Analytics | ApeX Gear</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800;900&family=Barlow:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-style.css" rel="stylesheet">
    <style>
        .report-hero {
            background: linear-gradient(135deg, #080f1e 0%, #0b2fa8 78%, #00c2ff 100%);
            color: #fff;
            border-radius: 14px;
            padding: 28px 32px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
        }

        .report-hero h1 {
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
            font-size: 2.1rem;
            margin: 0 0 4px;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .report-hero p {
            margin: 0;
            color: rgba(255, 255, 255, .72);
            font-size: .9rem;
        }

        .report-stamp {
            padding: 8px 13px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 8px;
            background: rgba(255, 255, 255, .08);
            font-size: .78rem;
            white-space: nowrap;
        }

        .metric-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            min-height: 138px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .metric-label {
            color: var(--text-muted);
            font-size: .74rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .metric-value {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
            margin-top: 10px;
        }

        .metric-note {
            margin-top: 8px;
            color: var(--text-muted);
            font-size: .8rem;
        }

        .metric-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            border-radius: 8px;
            background: rgba(0, 194, 255, .11);
            color: var(--accent);
        }

        .report-panel {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }

        .report-panel-header {
            padding: 16px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .report-panel-header h2 {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.2rem;
            font-weight: 900;
            text-transform: uppercase;
            margin: 0;
        }

        .report-panel-body {
            padding: 18px;
        }

        .trend-chart {
            position: relative;
            min-height: 250px;
        }

        .trend-chart svg {
            display: block;
            width: 100%;
            height: 210px;
            overflow: visible;
        }

        .trend-grid {
            stroke: #e8eef7;
            stroke-width: 1;
        }

        .trend-line {
            fill: none;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-linejoin: round;
            filter: drop-shadow(0 7px 14px rgba(0, 194, 255, .18));
        }

        .trend-line.revenue {
            stroke: var(--accent);
        }

        .trend-line.orders {
            stroke: var(--blue);
        }

        .trend-dot {
            fill: #fff;
            stroke-width: 3;
        }

        .trend-dot.revenue {
            stroke: var(--accent);
        }

        .trend-dot.orders {
            stroke: var(--blue);
        }

        .trend-meta {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
            color: var(--text-muted);
            font-size: .78rem;
        }

        .trend-legend {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            font-size: .78rem;
            color: var(--text-muted);
        }

        .legend-swatch {
            width: 22px;
            height: 4px;
            border-radius: 999px;
            background: var(--accent);
        }

        .legend-swatch.orders {
            background: var(--blue);
        }

        .chart-labels {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            color: var(--text-muted);
            font-size: .68rem;
            margin-top: -8px;
        }

        .product-share {
            min-width: 120px;
        }

        .share-track {
            height: 8px;
            border-radius: 999px;
            background: #edf2f9;
            overflow: hidden;
            margin-top: 5px;
        }

        .share-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), var(--blue));
        }

        .circle-metric {
            min-height: 190px;
            align-items: center;
            text-align: center;
        }

        .circle-gauge {
            width: 116px;
            height: 116px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            margin: 8px auto 12px;
            background:
                radial-gradient(circle at center, #fff 58%, transparent 60%),
                conic-gradient(var(--gauge-color, var(--accent)) calc(var(--pct, 0) * 1%), #edf2f9 0);
            box-shadow: inset 0 0 0 1px rgba(13, 27, 46, .04);
        }

        .circle-gauge strong {
            font-family: 'Barlow Condensed', sans-serif;
            font-size: 1.6rem;
            font-weight: 900;
        }

        .circle-caption {
            color: var(--text-muted);
            font-size: .78rem;
            margin: 0;
        }

        .donut-wrap {
            display: grid;
            grid-template-columns: minmax(180px, 240px) 1fr;
            gap: 22px;
            align-items: center;
        }

        .channel-donut {
            width: 220px;
            aspect-ratio: 1;
            border-radius: 50%;
            margin: 0 auto;
            background: conic-gradient(<?php echo $channelGradient; ?>);
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(13, 27, 46, .05);
        }

        .channel-donut::after {
            content: "";
            position: absolute;
            inset: 58px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 0 0 1px rgba(13, 27, 46, .05);
        }

        .channel-donut-label {
            position: absolute;
            inset: 0;
            display: grid;
            place-items: center;
            z-index: 1;
            text-align: center;
            font-family: 'Barlow Condensed', sans-serif;
            font-weight: 900;
        }

        .channel-list {
            display: grid;
            gap: 10px;
        }

        .channel-row {
            display: grid;
            grid-template-columns: 14px 1fr auto;
            gap: 9px;
            align-items: center;
            font-size: .84rem;
        }

        .channel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--dot);
        }

        .mrr-bars {
            height: 240px;
            display: flex;
            align-items: end;
            gap: 14px;
            padding-top: 10px;
            border-bottom: 1px solid var(--border);
        }

        .mrr-bar {
            flex: 1;
            min-width: 42px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: end;
            gap: 8px;
            color: var(--text-muted);
            font-size: .7rem;
        }

        .mrr-bar-fill {
            width: 100%;
            max-width: 58px;
            min-height: 8px;
            border-radius: 7px 7px 0 0;
            background: linear-gradient(180deg, #35bdb2, var(--accent));
            box-shadow: 0 10px 18px rgba(0, 194, 255, .18);
        }

        .mrr-value {
            color: var(--text-main);
            font-weight: 800;
            white-space: nowrap;
        }

        .ph-map-layout {
            display: grid;
            grid-template-columns: minmax(320px, 1.5fr) minmax(220px, .8fr);
            gap: 18px;
            align-items: stretch;
        }

        .ph-map-shell {
            min-height: 520px;
            border: 1px solid var(--border);
            border-radius: 12px;
            background:
                linear-gradient(180deg, rgba(0, 194, 255, .08), rgba(11, 47, 168, .03)),
                #f7fbff;
            position: relative;
            overflow: hidden;
        }

        .ph-map-tools {
            position: absolute;
            top: 14px;
            left: 14px;
            z-index: 3;
            display: flex;
            gap: 8px;
        }

        .map-tool-btn {
            width: 34px;
            height: 34px;
            border: 1px solid rgba(0, 194, 255, .28);
            background: rgba(255, 255, 255, .92);
            color: var(--blue);
            border-radius: 8px;
            display: grid;
            place-items: center;
            font-weight: 900;
            cursor: pointer;
        }

        .ph-map-svg {
            width: 100%;
            height: 100%;
            min-height: 520px;
            display: block;
            cursor: grab;
        }

        .ph-map-svg:active {
            cursor: grabbing;
        }

        .island-shape {
            fill: #d9f4f8;
            stroke: rgba(11, 47, 168, .22);
            stroke-width: 2;
        }

        .map-marker {
            stroke: #fff;
            stroke-width: 3;
            filter: drop-shadow(0 8px 12px rgba(11, 47, 168, .24));
            cursor: pointer;
        }

        .map-label {
            pointer-events: none;
            font-family: 'Barlow', sans-serif;
            font-size: 13px;
            font-weight: 800;
            fill: #0d1b2e;
            paint-order: stroke;
            stroke: rgba(255, 255, 255, .9);
            stroke-width: 4px;
        }

        .map-side-list {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .map-location-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 11px 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #fff;
            font-size: .84rem;
        }

        .map-scale {
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--text-muted);
            font-size: .75rem;
        }

        .map-scale-bar {
            width: 110px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(90deg, #cceeff, #00c2ff, #0b2fa8);
        }

        .bar-row {
            display: grid;
            grid-template-columns: minmax(100px, 160px) 1fr auto;
            gap: 12px;
            align-items: center;
            margin-bottom: 13px;
            font-size: .84rem;
        }

        .bar-track {
            height: 10px;
            border-radius: 999px;
            background: #edf2f9;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--accent), var(--blue));
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border-radius: 999px;
            font-size: .72rem;
            font-weight: 800;
            background: rgba(0, 194, 255, .1);
            color: #087da5;
        }

        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .84rem;
        }

        .analytics-table th {
            color: var(--text-muted);
            font-size: .7rem;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 10px 0;
            border-bottom: 1px solid var(--border);
        }

        .analytics-table td {
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .analytics-table tr:last-child td {
            border-bottom: none;
        }

        .activity-item {
            display: flex;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
            font-size: .84rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: var(--accent);
            margin-top: 6px;
            flex: 0 0 auto;
        }

        @media (max-width: 767.98px) {
            .report-hero {
                align-items: flex-start;
                flex-direction: column;
                padding: 24px;
            }

            .bar-row {
                grid-template-columns: 1fr;
                gap: 7px;
            }

            .donut-wrap,
            .ph-map-layout {
                grid-template-columns: 1fr;
            }

            .ph-map-shell,
            .ph-map-svg {
                min-height: 430px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <a href="../index.php" class="sidebar-brand">
            <img src="../assets/images/ApeX Logo.png" alt="ApeX Gear">
            <div class="sidebar-brand-text"><span class="t1">ApeX </span><span class="t2">Gear</span></div>
            <span class="sidebar-badge">Admin</span>
        </a>
        <nav class="sidebar-nav">
            <div class="sidebar-section-label">Main</div>
            <a href="apex26admin.php"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="manage_orders.php">
                <i class="fas fa-shopping-cart"></i> Orders
                <?php if ($pendingOrders > 0): ?><span class="nav-badge"><?php echo $pendingOrders; ?></span><?php endif; ?>
            </a>
            <a href="manage_products.php"><i class="fas fa-boxes"></i> Manage Products</a>
            <a href="manage_archives.php"><i class="fas fa-archive"></i> Archives</a>
            <a href="manage_users.php"><i class="fas fa-users"></i> Users</a>
            <a href="report.php" class="active"><i class="fas fa-chart-pie"></i> Reports &amp; Analytics</a>
            <a href="manage_deals.php"><i class="fas fa-percentage"></i> Deals &amp; Promos</a>
            <div class="sidebar-section-label">Store</div>
            <a href="../index.php" target="_blank"><i class="fas fa-store"></i> View Live Store</a>
            <a href="../index.php?page=products" target="_blank"><i class="fas fa-tags"></i> Product Catalog</a>
        </nav>
        <div class="sidebar-footer" style="display: flex; flex-direction: column; gap: 14px;">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Site</a>
            <a href="admin_logout.php" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrap">
        <header class="topbar">
            <div class="topbar-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
                <span class="topbar-title">Reports</span>
                <div class="topbar-divider"></div>
                <span class="topbar-crumb">Orders, Users &amp; Store Analytics</span>
            </div>
            <div class="topbar-right">
                <a href="manage_orders.php" class="btn-topbar"><i class="fas fa-shopping-cart"></i> Orders</a>
            </div>
        </header>

        <main class="page-body">
            <section class="report-hero">
                <div>
                    <h1>Reports &amp; Analytics</h1>
                    <p>Crystal-style operational view for ApeX Gear sales, users, inventory, and admin activity.</p>
                </div>
                <div class="report-stamp"><i class="fas fa-calendar-day me-2"></i><?php echo date('M d, Y h:i A'); ?></div>
            </section>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-label">Gross Revenue</div>
                                <div class="metric-value">&#8369;<?php echo number_format($grossRevenue, 2); ?></div>
                            </div>
                            <div class="metric-icon"><i class="fas fa-peso-sign"></i></div>
                        </div>
                        <div class="metric-note"><?php echo pctChange($monthRevenue, $lastMonthRevenue); ?> vs last month</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-label">Total Orders</div>
                                <div class="metric-value"><?php echo number_format($totalOrders); ?></div>
                            </div>
                            <div class="metric-icon"><i class="fas fa-receipt"></i></div>
                        </div>
                        <div class="metric-note"><?php echo number_format($completionRate, 1); ?>% completed order rate</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-label">Customers</div>
                                <div class="metric-value"><?php echo number_format($totalUsers); ?></div>
                            </div>
                            <div class="metric-icon"><i class="fas fa-users"></i></div>
                        </div>
                        <div class="metric-note"><?php echo number_format($averageOrderValue, 2); ?> average order value</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <div class="metric-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="metric-label">Inventory Health</div>
                                <div class="metric-value"><?php echo number_format($totalStock); ?></div>
                            </div>
                            <div class="metric-icon"><i class="fas fa-boxes-stacked"></i></div>
                        </div>
                        <div class="metric-note"><?php echo $lowStock; ?> low stock, <?php echo $archivedProducts; ?> archived</div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card circle-metric">
                        <div class="metric-label">Paid Payment Rate</div>
                        <div class="circle-gauge" style="--pct: <?php echo number_format($paidRate, 1, '.', ''); ?>; --gauge-color: var(--accent);">
                            <strong><?php echo number_format($paidRate, 1); ?>%</strong>
                        </div>
                        <p class="circle-caption"><?php echo $paidPayments; ?> of <?php echo $totalPayments; ?> payment records are paid</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card circle-metric">
                        <div class="metric-label">Completion Rate</div>
                        <div class="circle-gauge" style="--pct: <?php echo number_format($completionRate, 1, '.', ''); ?>; --gauge-color: #00d68f;">
                            <strong><?php echo number_format($completionRate, 1); ?>%</strong>
                        </div>
                        <p class="circle-caption"><?php echo $completedOrders; ?> completed from <?php echo $totalOrders; ?> orders</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card circle-metric">
                        <div class="metric-label">Repeat Customer Rate</div>
                        <div class="circle-gauge" style="--pct: <?php echo number_format($repeatCustomerRate, 1, '.', ''); ?>; --gauge-color: #0b2fa8;">
                            <strong><?php echo number_format($repeatCustomerRate, 1); ?>%</strong>
                        </div>
                        <p class="circle-caption"><?php echo $repeatCustomers; ?> repeat buyers from <?php echo $orderingCustomers; ?> customers</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="metric-card circle-metric">
                        <div class="metric-label">Stock Health</div>
                        <div class="circle-gauge" style="--pct: <?php echo number_format($stockHealthRate, 1, '.', ''); ?>; --gauge-color: #f5c518;">
                            <strong><?php echo number_format($stockHealthRate, 1); ?>%</strong>
                        </div>
                        <p class="circle-caption"><?php echo $totalProducts - $lowStock; ?> healthy products, <?php echo $lowStock; ?> low stock</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Total Volume by Channel</h2>
                            <span class="status-pill"><i class="fas fa-wallet"></i>payment mix</span>
                        </div>
                        <div class="report-panel-body">
                            <?php if (empty($channelRows)): ?>
                                <div class="text-muted small">No channel data yet.</div>
                            <?php else: ?>
                                <div class="donut-wrap">
                                    <div class="channel-donut">
                                        <div class="channel-donut-label">
                                            <div>
                                                <div style="font-size:1.65rem;">&#8369;<?php echo number_format($grossRevenue, 0); ?></div>
                                                <div style="font-size:.75rem;color:var(--text-muted);">volume</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="channel-list">
                                        <?php foreach ($channelRows as $index => $row): ?>
                                            <?php
                                                $channelRevenue = (float)($row['revenue'] ?? 0);
                                                $channelPct = $grossRevenue > 0 ? ($channelRevenue / $grossRevenue) * 100 : 0;
                                                $dotColor = $channelColors[$index % count($channelColors)];
                                            ?>
                                            <div class="channel-row" style="--dot: <?php echo $dotColor; ?>;">
                                                <span class="channel-dot"></span>
                                                <span>
                                                    <strong><?php echo htmlspecialchars($row['channel']); ?></strong><br>
                                                    <span class="text-muted"><?php echo (int)$row['orders']; ?> orders</span>
                                                </span>
                                                <span class="text-end">
                                                    <strong><?php echo number_format($channelPct, 1); ?>%</strong><br>
                                                    <span class="text-muted">&#8369;<?php echo number_format($channelRevenue, 0); ?></span>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-6">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Monthly Revenue</h2>
                            <span class="status-pill"><i class="fas fa-chart-column"></i>last 6 months</span>
                        </div>
                        <div class="report-panel-body">
                            <?php if (empty($monthlyRevenueRows)): ?>
                                <div class="text-muted small">No monthly revenue data yet.</div>
                            <?php else: ?>
                                <div class="mrr-bars">
                                    <?php foreach ($monthlyRevenueRows as $row): ?>
                                        <?php
                                            $barRevenue = (float)($row['revenue'] ?? 0);
                                            $barHeight = $maxMonthlyRevenue > 0 ? max(8, ($barRevenue / $maxMonthlyRevenue) * 190) : 8;
                                        ?>
                                        <div class="mrr-bar">
                                            <span class="mrr-value">&#8369;<?php echo number_format($barRevenue, 0); ?></span>
                                            <div class="mrr-bar-fill" style="height: <?php echo number_format($barHeight, 1, '.', ''); ?>px;"></div>
                                            <span><?php echo htmlspecialchars($row['month_label']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-7">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Revenue Line Graph</h2>
                            <span class="status-pill"><i class="fas fa-arrow-trend-up"></i>14-day trend</span>
                        </div>
                        <div class="report-panel-body trend-chart">
                            <div class="trend-meta">
                                <span>Highest day: &#8369;<?php echo number_format($maxTrendRevenue, 2); ?></span>
                                <div class="trend-legend">
                                    <span class="legend-item"><span class="legend-swatch"></span> Revenue</span>
                                </div>
                            </div>
                            <svg viewBox="0 0 640 170" role="img" aria-label="Revenue trend line graph">
                                <line class="trend-grid" x1="18" y1="18" x2="622" y2="18"></line>
                                <line class="trend-grid" x1="18" y1="85" x2="622" y2="85"></line>
                                <line class="trend-grid" x1="18" y1="152" x2="622" y2="152"></line>
                                <?php if ($revenueLinePoints): ?>
                                    <polyline class="trend-line revenue" points="<?php echo htmlspecialchars($revenueLinePoints); ?>"></polyline>
                                    <?php foreach (explode(' ', $revenueLinePoints) as $point): ?>
                                        <?php [$cx, $cy] = explode(',', $point); ?>
                                        <circle class="trend-dot revenue" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="4"></circle>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </svg>
                            <div class="chart-labels">
                                <?php foreach ($trendLabels as $label): ?>
                                    <span><?php echo htmlspecialchars($label); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-5">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Orders Line Graph</h2>
                            <span class="status-pill"><i class="fas fa-receipt"></i><?php echo array_sum($orderTrendValues); ?> orders</span>
                        </div>
                        <div class="report-panel-body trend-chart">
                            <div class="trend-meta">
                                <span>Peak day: <?php echo number_format($maxTrendOrders); ?> orders</span>
                                <div class="trend-legend">
                                    <span class="legend-item"><span class="legend-swatch orders"></span> Orders</span>
                                </div>
                            </div>
                            <svg viewBox="0 0 640 170" role="img" aria-label="Order volume trend line graph">
                                <line class="trend-grid" x1="18" y1="18" x2="622" y2="18"></line>
                                <line class="trend-grid" x1="18" y1="85" x2="622" y2="85"></line>
                                <line class="trend-grid" x1="18" y1="152" x2="622" y2="152"></line>
                                <?php if ($orderLinePoints): ?>
                                    <polyline class="trend-line orders" points="<?php echo htmlspecialchars($orderLinePoints); ?>"></polyline>
                                    <?php foreach (explode(' ', $orderLinePoints) as $point): ?>
                                        <?php [$cx, $cy] = explode(',', $point); ?>
                                        <circle class="trend-dot orders" cx="<?php echo $cx; ?>" cy="<?php echo $cy; ?>" r="4"></circle>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </svg>
                            <div class="chart-labels">
                                <?php foreach ($trendLabels as $label): ?>
                                    <span><?php echo htmlspecialchars($label); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-7">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Order Status Breakdown</h2>
                            <span class="status-pill"><i class="fas fa-chart-simple"></i><?php echo $pendingOrders; ?> pending</span>
                        </div>
                        <div class="report-panel-body">
                            <?php if (empty($statusRows)): ?>
                                <div class="text-muted small">No order data yet.</div>
                            <?php else: ?>
                                <?php foreach ($statusRows as $row): ?>
                                    <?php $width = $totalOrders > 0 ? ((int) $row['total'] / $totalOrders) * 100 : 0; ?>
                                    <div class="bar-row">
                                        <strong><?php echo htmlspecialchars($row['order_status']); ?></strong>
                                        <div class="bar-track"><div class="bar-fill" style="width: <?php echo max(4, $width); ?>%;"></div></div>
                                        <span><?php echo (int) $row['total']; ?> orders</span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-5">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Store Snapshot</h2>
                            <span class="status-pill"><i class="fas fa-ticket"></i><?php echo $activeDeals; ?> active deals</span>
                        </div>
                        <div class="report-panel-body">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="metric-label">Products</div>
                                    <div class="metric-value"><?php echo $totalProducts; ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-label">Today Sales</div>
                                    <div class="metric-value">&#8369;<?php echo number_format($todayRevenue, 0); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-label">Completed</div>
                                    <div class="metric-value"><?php echo $completedOrders; ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-label">Canceled</div>
                                    <div class="metric-value"><?php echo $canceledOrders; ?></div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-12 col-xl-6">
                    <section class="report-panel h-100">
                        <div class="report-panel-header"><h2>Category Analytics</h2></div>
                        <div class="report-panel-body">
                            <?php foreach ($categoryRows as $row): ?>
                                <?php $width = ((int) $row['products'] / $maxCategoryProducts) * 100; ?>
                                <div class="bar-row">
                                    <strong><?php echo htmlspecialchars($row['category_name']); ?></strong>
                                    <div class="bar-track"><div class="bar-fill" style="width: <?php echo max(6, $width); ?>%;"></div></div>
                                    <span><?php echo (int) $row['products']; ?> items</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-6">
                    <section class="report-panel h-100">
                        <div class="report-panel-header">
                            <h2>Top Selling Products</h2>
                            <span class="status-pill"><i class="fas fa-percent"></i>share of sales</span>
                        </div>
                        <div class="report-panel-body">
                            <table class="analytics-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Units</th>
                                        <th>Buyers</th>
                                        <th>Percent</th>
                                        <th class="text-end">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($topProductRows)): ?>
                                        <tr><td colspan="5" class="text-muted">No product sales yet.</td></tr>
                                    <?php endif; ?>
                                    <?php foreach ($topProductRows as $row): ?>
                                        <?php
                                            $unitPercent = $totalUnitsSold > 0 ? ((int) $row['units'] / $totalUnitsSold) * 100 : 0;
                                            $buyerPercent = $totalProductBuyers > 0 ? ((int) $row['buyers'] / $totalProductBuyers) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                                                <span class="text-muted"><?php echo htmlspecialchars($row['category_name']); ?></span>
                                            </td>
                                            <td><?php echo (int) $row['units']; ?></td>
                                            <td>
                                                <strong><?php echo (int) $row['buyers']; ?></strong><br>
                                                <span class="text-muted"><?php echo number_format($buyerPercent, 1); ?>% buyers</span>
                                            </td>
                                            <td>
                                                <div class="product-share">
                                                    <strong><?php echo number_format($unitPercent, 1); ?>%</strong>
                                                    <div class="share-track">
                                                        <div class="share-fill" style="width: <?php echo max(4, $unitPercent); ?>%;"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">&#8369;<?php echo number_format((float) $row['revenue'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-xl-7">
                    <section class="report-panel h-100">
                        <div class="report-panel-header"><h2>Recent Orders</h2></div>
                        <div class="report-panel-body">
                            <table class="analytics-table">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($order['order_ref_code']); ?></strong><br>
                                                <span class="text-muted"><?php echo date('M d, h:i A', strtotime($order['created_at'])); ?></span>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['buyer']); ?></td>
                                            <td><span class="status-pill"><?php echo htmlspecialchars($order['order_status']); ?></span></td>
                                            <td class="text-end">&#8369;<?php echo number_format((float) $order['total_amount'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-xl-5">
                    <section class="report-panel mb-4">
                        <div class="report-panel-header"><h2>Payment Status</h2></div>
                        <div class="report-panel-body">
                            <?php if (empty($paymentRows)): ?>
                                <div class="text-muted small">No payment records yet.</div>
                            <?php else: ?>
                                <?php foreach ($paymentRows as $row): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="status-pill"><?php echo htmlspecialchars($row['status']); ?></span>
                                        <strong><?php echo (int) $row['total']; ?></strong>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="report-panel">
                        <div class="report-panel-header"><h2>Admin Activity</h2></div>
                        <div class="report-panel-body">
                            <?php if (empty($activityRows)): ?>
                                <div class="text-muted small">No activity yet.</div>
                            <?php endif; ?>
                            <?php foreach ($activityRows as $activity): ?>
                                <div class="activity-item">
                                    <div class="activity-dot"></div>
                                    <div>
                                        <strong><?php echo htmlspecialchars(str_replace('_', ' ', $activity['activity_type'])); ?></strong><br>
                                        <span><?php echo htmlspecialchars($activity['message']); ?></span><br>
                                        <span class="text-muted"><?php echo date('M d, h:i A', strtotime($activity['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }

        const phMap = document.getElementById('phOrderMap');
        const defaultPhViewBox = { x: 90, y: 70, w: 560, h: 760 };
        let phViewBox = { ...defaultPhViewBox };
        let isDraggingPhMap = false;
        let phDragStart = null;

        function applyPhMapViewBox() {
            if (!phMap) return;
            phMap.setAttribute('viewBox', `${phViewBox.x} ${phViewBox.y} ${phViewBox.w} ${phViewBox.h}`);
        }

        function zoomPhMap(factor) {
            if (!phMap) return;
            const cx = phViewBox.x + phViewBox.w / 2;
            const cy = phViewBox.y + phViewBox.h / 2;
            const nextW = Math.max(230, Math.min(760, phViewBox.w * factor));
            const nextH = Math.max(310, Math.min(1030, phViewBox.h * factor));
            phViewBox = {
                x: cx - nextW / 2,
                y: cy - nextH / 2,
                w: nextW,
                h: nextH
            };
            applyPhMapViewBox();
        }

        function resetPhMap() {
            phViewBox = { ...defaultPhViewBox };
            applyPhMapViewBox();
        }

        function mapPointFromEvent(event) {
            const rect = phMap.getBoundingClientRect();
            return {
                x: phViewBox.x + ((event.clientX - rect.left) / rect.width) * phViewBox.w,
                y: phViewBox.y + ((event.clientY - rect.top) / rect.height) * phViewBox.h
            };
        }

        if (phMap) {
            phMap.addEventListener('wheel', event => {
                event.preventDefault();
                zoomPhMap(event.deltaY < 0 ? 0.88 : 1.12);
            }, { passive: false });

            phMap.addEventListener('pointerdown', event => {
                isDraggingPhMap = true;
                phDragStart = mapPointFromEvent(event);
                phMap.setPointerCapture(event.pointerId);
            });

            phMap.addEventListener('pointermove', event => {
                if (!isDraggingPhMap || !phDragStart) return;
                const point = mapPointFromEvent(event);
                phViewBox.x += phDragStart.x - point.x;
                phViewBox.y += phDragStart.y - point.y;
                applyPhMapViewBox();
            });

            phMap.addEventListener('pointerup', event => {
                isDraggingPhMap = false;
                phDragStart = null;
                phMap.releasePointerCapture(event.pointerId);
            });

            phMap.addEventListener('pointerleave', () => {
                isDraggingPhMap = false;
                phDragStart = null;
            });
        }
    </script>
</body>

</html>
