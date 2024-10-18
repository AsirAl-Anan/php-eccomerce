<?php
require_once '../config/config.php';

$timeframe = $_GET['timeframe'];

switch ($timeframe) {
    case 'year':
        $query = "SELECT MONTH(order_date) as period, SUM(total_amount) as total_sales FROM orders WHERE YEAR(order_date) = YEAR(CURDATE()) GROUP BY MONTH(order_date)";
        break;
    case 'week':
        $query = "SELECT DAYNAME(order_date) as period, SUM(total_amount) as total_sales FROM orders WHERE YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1) GROUP BY DAYNAME(order_date)";
        break;
    case 'month':
    default:
        $query = "SELECT DAY(order_date) as period, SUM(total_amount) as total_sales FROM orders WHERE MONTH(order_date) = MONTH(CURDATE()) AND YEAR(order_date) = YEAR(CURDATE()) GROUP BY DAY(order_date)";
        break;
}

$result = $conn->query($query);

$labels = [];
$sales = [];

while ($row = $result->fetch_assoc()) {
    $labels[] = $row['period'];
    $sales[] = $row['total_sales'];
}

echo json_encode(['labels' => $labels, 'sales' => $sales]);

$conn->close();
?>
