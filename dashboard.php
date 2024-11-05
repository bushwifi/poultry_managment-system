<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Database connection
include 'db.php'; // Include your database connection file

// Fetch total livestock_number
$result = $conn->query("SELECT SUM(livestock_number) as total FROM livestock");
$row = $result->fetch_assoc();
$total_livestock_number = $row['total'];

// Fetch total expenses
$result = $conn->query("SELECT SUM(amount) as total FROM expenses");
$row = $result->fetch_assoc();
$total_expenses = $row['total'];

// Fetch total sales
$result = $conn->query("SELECT SUM(amount) as total FROM sales");
$row = $result->fetch_assoc();
$total_sales = $row['total'];

// Fetch sales data for chart
$salesList = [];
$result = $conn->query("SELECT date, amount FROM sales");
if ($result) {
    $salesList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle error
}

// Fetch expenses data for chart
$expensesList = [];
$result = $conn->query("SELECT date, amount FROM expenses");
if ($result) {
    $expensesList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle error
}

// Fetch livestock data for chart
$livestockList = [];
$result = $conn->query("SELECT date, livestock_number FROM livestock");
if ($result) {
    $livestockList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    // Handle error
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poultry Management Dashboard</title>
    <link rel="stylesheet" href="css/d.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Add custom styles for charts */
        .charts {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
        }
        .chart-container {
            width: 30%; /* Adjust the width for smaller charts */
            margin-bottom: 20px;
        }
        canvas {
            max-width: 100%; /* Ensure canvas fits within container */
            height: auto;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <aside>
            <img src="logo.png" alt="Poultry Management Logo" class="logo"> <!-- Your logo here -->
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="livestock.php"><i class="fas fa-feather"></i> Livestock Management</a></li>
                <li><a href="financial.php" class="active"><i class="fas fa-coins"></i> Financial Tracking</a></li>
                <li><a href="sales.php"><i class="fas fa-chart-line"></i> Sales Tracking</a></li>
                <li><a href="scheduling.php"><i class="fas fa-calendar-alt"></i> Scheduling</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>
        
        <main> 
            <header>
                <h1>Poultry Management Dashboard</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>
            
            <section class="stats">
                <div class="stat-item">
                    <h3><i class="fas fa-eye"></i> Total Livestock</h3>
                    <p id="total-livestock"><?php echo $total_livestock_number; ?></p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-dollar-sign"></i> Total Expenses</h3>
                    <p id="total-expenses">Ksh <?php echo number_format($total_expenses, 2); ?></p>
                </div>
                <div class="stat-item">
                    <h3><i class="fas fa-chart-line"></i> Total Sales</h3>
                    <p id="total-sales">Ksh <?php echo number_format($total_sales, 2); ?></p>
                </div>
            </section>

            <section class="charts">
                <div class="chart-container">
                    <h2>Sales Distribution</h2>
                    <canvas id="salesChart" width="400" height="200"></canvas>
                </div>

                <div class="chart-container">
                    <h2>Expenses Distribution</h2>
                    <canvas id="expensesChart" width="400" height="200"></canvas>
                </div>

                <div class="chart-container">
                    <h2>Livestock Over Time</h2>
                    <canvas id="livestockChart" width="400" height="200"></canvas>
                </div>
            </section>
            
            <footer>
                <p>&copy; 2024 Poultry Management System</p>
            </footer>
        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const salesData = {
            labels: <?php echo json_encode(array_column($salesList, 'date')); ?>,
            datasets: [{
                label: 'Sales in Ksh',
                data: <?php echo json_encode(array_column($salesList, 'amount')); ?>,
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 159, 64, 0.6)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };

        const expensesData = {
            labels: <?php echo json_encode(array_column($expensesList, 'date')); ?>,
            datasets: [{
                label: 'Expenses in Ksh',
                data: <?php echo json_encode(array_column($expensesList, 'amount')); ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.6)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 1
            }]
        };

        const livestockData = {
            labels: <?php echo json_encode(array_column($livestockList, 'date')); ?>,
            datasets: [{
                label: 'Livestock Number',
                data: <?php echo json_encode(array_column($livestockList, 'livestock_number')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        const salesChart = new Chart(
            document.getElementById('salesChart'),
            {
                type: 'pie', // Sales Chart
                data: salesData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Sales Distribution'
                        }
                    }
                }
            }
        );

        const expensesChart = new Chart(
            document.getElementById('expensesChart'),
            {
                type: 'pie', // Expenses Chart
                data: expensesData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Expenses Distribution'
                        }
                    }
                }
            }
        );

        const livestockChart = new Chart(
            document.getElementById('livestockChart'),
            {
                type: 'bar', // Livestock Chart
                data: livestockData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Livestock Over Time'
                        }
                    }
                }
            }
        );
    </script>
</body>
</html>
