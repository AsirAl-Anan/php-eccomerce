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
        while ($row = $result->fetch_assoc()) {
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

<!-- Tab Navigation -->
<div class="container mx-auto px-4 py-4">
    <div class="flex justify-center mb-6">
        <button id="dashboardTab" class="px-4 py-2 bg-blue-500 text-white rounded-l-md hover:bg-blue-600 focus:outline-none">Admin Dashboard</button>
        <button id="salesReportTab" class="px-4 py-2 bg-gray-300 text-black rounded-r-md hover:bg-gray-400 focus:outline-none">Sales Report</button>
    </div>

    <!-- Admin Dashboard Section -->
    <div id="dashboardSection" class="hidden">
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
                <div x-show="open" class="mt-4">
                    <table class="min-w-full bg-white rounded-lg overflow-hidden">
                        <thead class="bg-<?= $colors[$table] ?>-200 text-black">
                            <tr>
                                <?php foreach (array_keys($data['data'][0] ?? []) as $column): ?>
                                <th class="py-2 px-4"><?= ucfirst($column) ?></th>
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

    <!-- Sales Report Section -->
    <div id="salesReportSection" class="hidden">
        <h1 class="text-3xl font-bold mb-6">Sales Report</h1>
        <canvas id="salesChart" class="bg-white p-6 rounded-lg shadow-md"></canvas>
    </div>
</div>

<script>
// Toggle Tabs
document.getElementById('dashboardTab').addEventListener('click', function () {
    document.getElementById('dashboardSection').classList.remove('hidden');
    document.getElementById('salesReportSection').classList.add('hidden');
    this.classList.add('bg-blue-500', 'text-white');
    document.getElementById('salesReportTab').classList.remove('bg-blue-500', 'text-white');
    document.getElementById('salesReportTab').classList.add('bg-gray-300', 'text-black');
});

document.getElementById('salesReportTab').addEventListener('click', function () {
    document.getElementById('salesReportSection').classList.remove('hidden');
    document.getElementById('dashboardSection').classList.add('hidden');
    this.classList.add('bg-blue-500', 'text-white');
    document.getElementById('dashboardTab').classList.remove('bg-blue-500', 'text-white');
    document.getElementById('dashboardTab').classList.add('bg-gray-300', 'text-black');
});

// Render Sales Chart
const ctx = document.getElementById('salesChart').getContext('2d');
const salesData = {
    labels: ['January', 'February', 'March', 'April', 'May', 'June'],
    datasets: [{
        label: 'Sales',
        data: [12, 19, 3, 5, 2, 3],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
    }]
};

const salesChart = new Chart(ctx, {
    type: 'bar',
    data: salesData,
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

</body>

</html>
