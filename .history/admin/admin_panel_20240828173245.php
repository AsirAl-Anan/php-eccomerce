<?php
session_start();
require_once '../config/config.php';

// Check if admin is logged in
if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: admin_login.php");
    exit;
}
require_once 'fetch_admin_data.php';

// Function to get count from a table
function getCount($conn, $table) {
    $sql = "SELECT COUNT(*) as count FROM $table";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Function to get data from a table
function getData($conn, $table, $limit = 5) {
    $sql = "SELECT * FROM $table LIMIT $limit";
    $result = $conn->query($sql);
    $data = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    return $data;
}

// Get counts and data for each table
$tables = ['users', 'products', 'orders', 'team_members', 'admin_users'];
$dashboardData = [];

foreach ($tables as $table) {
    $dashboardData[$table] = [
        'count' => getCount($conn, $table),
        'data' => getData($conn, $table)
    ];
}
$query = "
    SELECT
        DATE(order_date) AS day,
        SUM(total_amount) AS total_sales
    FROM orders
    WHERE order_date >= CURDATE() - INTERVAL 30 DAY
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date)
";

// Execute the query and fetch results
$result = $conn->query($query);

$days = [];
$sales = [];
$startDate = (new DateTime())->modify('-29 days'); // Start date 30 days ago

// Initialize array with zero sales for each day
while ($startDate <= new DateTime()) {
    $dateString = $startDate->format('Y-m-d');
    $days[] = $dateString;
    $sales[$dateString] = 0; // Default to 0 sales
    $startDate->modify('+1 day');
}

// Populate sales data
while ($row = $result->fetch_assoc()) {
    $days[] = $row['day'];
    $sales[$row['day']] = (float) $row['total_sales'];
}

// Convert to JSON for use in JavaScript
$salesDays = json_encode(array_keys($sales));
$salesTotals = json_encode(array_values($sales));

$query_yearly = "
    SELECT
        DATE_FORMAT(order_date, '%Y-%m') AS month,
        SUM(total_amount) AS total_sales
    FROM orders
    WHERE YEAR(order_date) = YEAR(CURDATE())
    GROUP BY DATE_FORMAT(order_date, '%Y-%m')
    ORDER BY DATE_FORMAT(order_date, '%Y-%m')
";

// Execute the query and fetch results
$result_yearly = $conn->query($query_yearly);

$months = [];
$sales_yearly = [];
$startDate = (new DateTime())->setDate((int) date('Y'), 1, 1); // Start date of the year

// Initialize array with zero sales for each month
for ($i = 1; $i <= 12; $i++) {
    $month = $startDate->format('Y-m');
    $months[] = $startDate->format('F Y'); // Display month name and year
    $sales_yearly[$month] = 0; // Default to 0 sales
    $startDate->modify('+1 month');
}

// Populate sales data
while ($row = $result_yearly->fetch_assoc()) {
    $months[] = $row['month'];
    $sales_yearly[$row['month']] = (float) $row['total_sales'];
}

// Convert to JSON for use in JavaScript
$salesMonths = json_encode(array_keys($sales_yearly));
$salesTotalsYearly = json_encode(array_values($sales_yearly));

// Sample SQL query to get weekly sales data
$query_weekly = "
    SELECT
        DAYOFWEEK(order_date) AS day_of_week,
        SUM(total_amount) AS total_sales
    FROM orders
    WHERE YEARWEEK(order_date, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY DAYOFWEEK(order_date)
    ORDER BY DAYOFWEEK(order_date)
";

// Execute the query and fetch results
$result_weekly = $conn->query($query_weekly);

$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$sales_weekly = array_fill_keys($daysOfWeek, 0); // Default to 0 sales

// Populate sales data
while ($row = $result_weekly->fetch_assoc()) {
    $dayIndex = (int) $row['day_of_week'] - 1;
    $sales_weekly[$daysOfWeek[$dayIndex]] = (float) $row['total_sales'];
}

// Convert to JSON for use in JavaScript
$salesDaysOfWeek = json_encode(array_keys($sales_weekly));
$salesTotalsWeekly = json_encode(array_values($sales_weekly));

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body class="bg-gray-100 ">

<nav class="bg-gray-800 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="#" class="flex-shrink-0">
                    <img class="h-8 w-8" src="https://tailwindui.com/img/logos/workflow-mark-indigo-500.svg" alt="Workflow">
                </a>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="admin_panel.php" class="px-3 py-2 rounded-md text-sm font-medium bg-gray-900">Dashboard</a>
                        <a href="manage_users.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Users</a>
                        <a href="manage_product.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Products</a>
                        <a href="order_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Orders</a>
                        <a href="edit_carousel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Carousel</a>
                        <a href="manage_admin.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Admins</a>
                        <a href="manage_teammembers.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Team members</a>
                        <a href="edit_about_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit About page</a>
                        <a href="edit_offers_section.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Edit offer section</a>
                    </div>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="ml-4 flex items-center md:ml-6">
                    <button
                        class="p-1 rounded-full hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                        <span class="sr-only">View notifications</span>
                        <i class="fas fa-bell"></i>
                    </button>
                    <div class="ml-3 relative">
                        <div>
                            <button id="user-menu-button" class="max-w-xs bg-gray-800 rounded-full flex items-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                                <span class="sr-only">Open user menu</span>
                                <?php
$profile_picture_path = isset($admin_data['profile_picture']) ? '../uploads/' . $admin_data['profile_picture'] : '../uploads/default_avatar.jpg';
?>
                                <img class="h-8 w-8 rounded-full" src="<?php echo htmlspecialchars($profile_picture_path); ?>" alt="Admin profile">
                            </button>
                        </div>
                        <!-- Dropdown menu -->
                        <div id="user-menu" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 hidden z-10">
                            <a href="admin-profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
                            <a href="create_admin.php.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add new admin</a>
                            <a href="logout.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign Out</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="-mr-2 flex md:hidden">
                <button type="button"
                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-800 focus:ring-white">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php
        $colors = [
            'users' => 'blue',
            'products' => 'green',
            'orders' => 'yellow',
            'team_members' => 'purple',
            'admin_users' => 'red'
        ];

        $edit_links = [
            'users' => 'users_manage.php',
            'products' => 'manage_product.php',
            'orders' => 'order_manage.php',
            'team_members' => 'manage_teammembers.php',
            'admin_users' => 'manage_admin.php'
        ];

        foreach ($dashboardData as $table => $data): 
        ?>
        <div x-data="{ open: false }" class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-2"><?= ucfirst(str_replace('_', ' ', $table)) ?></h2>
            <p class="text-4xl font-bold text-<?= $colors[$table] ?>-600 mb-4"><?= $data['count'] ?></p>
            <div class="flex space-x-2">
                <button 
                    @click="open = !open" 
                    class="bg-<?= $colors[$table] ?>-500 text-white px-4 py-2 rounded hover:bg-<?= $colors[$table] ?>-600 transition duration-300"
                >
                    View Details
                </button>
                <a href="<?= $edit_links[$table] ?>" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition duration-300">
                    Edit
                </a>
            </div>
            <div x-show="open" class="mt-4 overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <?php foreach (array_keys($data['data'][0]) as $column): ?>
                            <th class="py-2 px-4 text-left"><?= ucfirst(str_replace('_', ' ', $column)) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['data'] as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                            <td class="py-2 px-4"><?= htmlspecialchars($value) ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Sales Report for month</h1>
   <!-- Sales Report Chart -->
   <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Sales Report for the Current Month</h2>
        <canvas id="salesChart"></canvas>
    </div>
 
</div>
<div class="container mx-auto px-4 py-8">
<canvas id="yearlySalesChart" width="400" height="200"></canvas>
</div>
<div class="container mx-auto px-4 py-8">
<canvas id="weeklySalesChart" width="400" height="200"></canvas>
</div>
<script>

    
            //admin-data and drop down
            document.getElementById('user-menu-button').addEventListener('click', function() {
        var menu = document.getElementById('user-menu');
        menu.classList.toggle('hidden');
    });
            function previewImage(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        document.getElementById('current_image').src = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }

      // Get sales data from PHP
   // Get sales data from PHP
   const salesDays = <?= $salesDays ?>; // Array of days in YYYY-MM-DD format
    const salesTotals = <?= $salesTotals ?>; // Array of sales totals

    // Initialize the chart
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: salesDays.map(day => {
                const date = new Date(day);
                return `Day ${date.getDate()}`; // Display just the day of the month
            }), 
            datasets: [{
                label: 'Total Sales',
                data: salesTotals,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Day of Month'
                    },
                    ticks: {
                        autoSkip: false, // Ensure all days are displayed
                        maxRotation: 45, // Rotate labels if necessary
                        minRotation: 45
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Total Sales ($)'
                    },
                    beginAtZero: true,
                    max: 20000,       // Set maximum value of Y-axis to 20,000
                    ticks: {
                        stepSize: 2000, // Set step size for the Y-axis
                        callback: function(value) {
                            return value.toLocaleString(); // Format numbers with commas
                        }
                    }
                }
            }
        }
    });
    // Get yearly sales data from PHP
    const salesMonths = <?= $salesMonths ?>; // Array of months
    const salesTotalsYearly = <?= $salesTotalsYearly ?>; // Array of sales totals

    // Initialize the chart
    const ctxYearly = document.getElementById('yearlySalesChart').getContext('2d');
    const yearlySalesChart = new Chart(ctxYearly, {
        type: 'bar',
        data: {
            labels: salesMonths,
            datasets: [{
                label: 'Total Sales',
                data: salesTotalsYearly,
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Month'
                    },
                    ticks: {
                        autoSkip: false, // Ensure all months are displayed
                        maxRotation: 45, // Rotate labels if necessary
                        minRotation: 45
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Total Sales ($)'
                    },
                    beginAtZero: true,
                    max: 20000,       // Set maximum value of Y-axis (adjust as needed)
                    ticks: {
                        stepSize: 2000, // Set step size for the Y-axis
                        callback: function(value) {
                            return value.toLocaleString(); // Format numbers with commas
                        }
                    }
                }
            }
        }
    });
</script>
</body>
</html>
