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

<body class="bg-gray-100">

<nav class="bg-gray-800 text-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <a href="#" class="flex-shrink-0">
                    <img class="h-8 w-8" src="https://tailwindui.com/img/logos/workflow-mark-indigo-500.svg" alt="Workflow">
                </a>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="admin_panel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300  text-white">Dashboard</a>
                        <a href="users_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Users</a>
                        <a href="manage_product.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Products</a>
                        <a href="order_manage.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Orders</a>
                        <a href="edit_carousel.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Carousel</a>
                        <a href="manage_admin.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Admins</a>
                        <a href="manage_teammembers.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Team members</a>
                        <a href="edit_about_page.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Edit About page</a>
                        <a href="edit_offers_section.php" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-300 text-white">Edit offer section</a>
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
                            <a href="create_admin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add new admin</a>
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
    <h1 class="text-3xl font-bold mb-6">Admin Panel</h1>

    <!-- Tab navigation -->
  
  <div class="mb-4">
        <ul class="flex border-b">
            <li class="-mb-px mr-1">
                <a id="dashboard-tab" class="bg-white inline-block border-l border-t border-r rounded-t py-2 px-4 text-blue-700 font-semibold cursor-pointer" onclick="switchTab('dashboard')">
                    Dashboard
                </a>
            </li>
            <li class="mr-1">
                <a id="sales-tab" class="bg-white inline-block py-2 px-4 text-blue-500 hover:text-blue-800 font-semibold cursor-pointer" onclick="switchTab('sales')">
                    Sales Reports
                </a>
            </li>
        </ul>
    </div>
    <!-- Dashboard content -->
    <div id="dashboard-content" class="tab-content">
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

    <!-- Sales Reports content -->
    <div id="sales-content" class="tab-content hidden">
        <h2 class="text-2xl font-bold mb-4">Sales Reports</h2>
        <!-- Toggle Buttons -->
        <div class="mb-4 flex justify-center">
            <button id="toggleMonth" class="bg-blue-500 text-white px-4 py-2 rounded mx-2" onclick="fetchSalesData('month')">Sales by Month</button>
            <button id="toggleYear" class="bg-green-500 text-white px-4 py-2 rounded mx-2" onclick="fetchSalesData('year')">Sales by Year</button>
            <button id="toggleWeek" class="bg-purple-500 text-white px-4 py-2 rounded mx-2" onclick="fetchSalesData('week')">Sales by Week</button>
        </div>

        <!-- Canvas Elements for Charts -->
        <div id="chartContainer">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
    </div>
</div>

<script>
let salesChart;

function switchTab(tabName) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.classList.add('hidden'));
    document.getElementById(`${tabName}-content`).classList.remove('hidden');

    // Update active tab style
    const tabLinks = document.querySelectorAll('#dashboard-tab, #sales-tab');
    tabLinks.forEach(link => {
        link.classList.remove('text-blue-700', 'border-b-2', 'border-blue-700');
        link.classList.add('text-blue-500', 'hover:text-blue-800');
    });
    document.getElementById(`${tabName}-tab`).classList.remove('text-blue-500', 'hover:text-blue-800');
    document.getElementById(`${tabName}-tab`).classList.add('text-blue-700', 'border-b-2', 'border-blue-700');

    if (tabName === 'sales' && !salesChart) {
        fetchSalesData('month');
    }
}

function fetchSalesData(timeframe) {
    fetch(`fetch_sales_data.php?timeframe=${timeframe}`)
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('salesChart').getContext('2d');

            if (salesChart) {
                salesChart.destroy();
            }

            let yAxisMax = 20000;
            let stepSize = 5000;

            if (timeframe === 'year') {
                yAxisMax = 240000;
                stepSize = 5000;
            }

            salesChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Total Sales',
                        data: data.sales,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: yAxisMax,
                            ticks: {
                                stepSize: stepSize,
                            }
                        }
                    }
                }
            });
        });
}

// User menu dropdown toggle
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

// Initialize the dashboard tab as active
switchTab('dashboard');
</script>
</body>
</html>