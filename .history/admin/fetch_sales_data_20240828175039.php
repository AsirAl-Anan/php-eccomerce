<?php
require_once '../config/config.php';

$timeframe = $_GET['timeframe'];
$labels = [];
$sales = [];

if ($timeframe == 'year') {
    // Generate labels for all months of the current year
    for ($i = 1; $i <= 12; $i++) {
        $labels[] = date('F', mktime(0, 0, 0, $i, 10));
        $sales[$i] = 0;
    }

    // Fetch sales data for the current year
    $query = "SELECT MONTH(order_date) as period, SUM(total_amount) as total_sales FROM orders WHERE YEAR(order_date) = YEAR(CURDATE()) GROUP BY MONTH(order_date)";
} elseif ($timeframe == 'week') {
    // Generate labels for all days of the current week
    $startOfWeek = strtotime("last Sunday midnight");
    $endOfWeek = strtotime("next Saturday");
    for ($i = $startOfWeek; $i <= $endOfWeek; $i += 86400) {
        $labels[] = date('l', $i);  // Full name of the day
        $sales[date('l', $i)] = 0;
    }

    // Fetch sales data for the current week
    $query = "SELECT DAYNAME(order_date) as period, SUM(total_amount) as total_sales FROM orders WHERE YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1) GROUP BY DAYNAME(order_date)";
} else {
    // Generate labels for all days of the current month
    $daysInMonth = date('t');
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $labels[] = $i;
        $sales[$i] = 0;
    }

    // Fetch sales data for the current month
    $query = "SELECT DAY(order_date) as period, SUM(total_amount) as total_sales FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE()) GROUP BY DAY(order_date)";
}

$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    $sales[(int)$row['period']] = $row['total_sales'];
}

$salesData = [];
foreach ($labels as $label) {
    $salesData[] = $sales[$label] ?? 0;
}

echo json_encode(['labels' => $labels, 'sales' => $salesData]);

$conn->close();
?>
