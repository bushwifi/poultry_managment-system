<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include your database connection file
include 'db.php';

// Initialize variables
$date = $type_of_sale = $amount = '';
$errors = [];

// Handle delete request
if (isset($_GET['delete'])) {
    $sale_id = (int)$_GET['delete'];

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->bind_param("i", $sale_id);

    if ($stmt->execute()) {
        // Redirect to sales page to avoid re-executing delete on page refresh
        header("Location: sales.php");
        exit();
    } else {
        $errors[] = "Error deleting sale: " . $stmt->error;
    }
    $stmt->close();
}

// Handle form submission for adding sales
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['date'])) {
        $errors[] = 'Date is required.';
    } else {
        $date = $_POST['date'];
    }
    if (empty($_POST['type_of_sale'])) {
        $errors[] = 'Type of sale is required.';
    } else {
        $type_of_sale = $_POST['type_of_sale'];
    }
    if (empty($_POST['amount'])) {
        $errors[] = 'Amount is required.';
    } else {
        $amount = (float)$_POST['amount'];
    }

    // If no errors, proceed with inserting data into the database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO sales (date, type_of_sale, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $date, $type_of_sale, $amount);

        if ($stmt->execute()) {
            header("Location: sales.php");
            exit();
        } else {
            $errors[] = "Error adding sale: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch sales from the database
$salesList = [];
$result = $conn->query("SELECT * FROM sales");
if ($result) {
    $salesList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Error fetching sales: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Tracking</title>
    <link rel="stylesheet" href="css/d.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="dashboard">
        <aside>
            <img src="logo.png" alt="Poultry Management Logo" class="logo">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="inventory.php"><i class="fas fa-boxes"></i> Inventory</a></li>
                <li><a href="livestock.php"><i class="fas fa-feather"></i> Livestock Management</a></li>
                <li><a href="financial.php"><i class="fas fa-coins"></i> Financial Tracking</a></li>
                <li><a href="sales.php" class="active"><i class="fas fa-chart-line"></i> Sales Tracking</a></li>
                <li><a href="scheduling.php"><i class="fas fa-calendar-alt"></i> Scheduling</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main>
            <header>
                <h1>Sales Tracking</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>

            <section class="form-section">
                <h2>Add New Sale</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="date" name="date" required>
                    <input type="text" name="type_of_sale" placeholder="Type of Sale" required>
                    <input type="number" step="0.01" name="amount" placeholder="Amount (Ksh)" required>
                    <button type="submit">Add Sale</button>
                </form>
            </section>

            <section class="table-section">
                <h2>Sales List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Type of Sale</th>
                            <th>Amount (Ksh)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($salesList) > 0): ?>
                            <?php foreach ($salesList as $sale): ?>
                                <tr>
                                    <td><?php echo $sale['id']; ?></td>
                                    <td><?php echo $sale['date']; ?></td>
                                    <td><?php echo $sale['type_of_sale']; ?></td>
                                    <td>Ksh <?php echo number_format($sale['amount'], 2); ?></td>
                                    <td>
                                        <a href="sales.php?delete=<?php echo $sale['id']; ?>" onclick="return confirm('Are you sure you want to delete this sale?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No sales found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <footer>
                <p>&copy; 2024 Poultry Management System</p>
            </footer>
        </main>
    </div>
</body>
</html>
