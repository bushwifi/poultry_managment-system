<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include your database connection file
include 'db.php';

// Initialize variables
$date = $expense_type = $amount = '';
$errors = [];

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);

    if ($delete_stmt->execute()) {
        header("Location: financial.php"); // Redirect back to financial.php
        exit();
    } else {
        $errors[] = "Error deleting expense: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

// Handle form submission for adding expenses
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['date'])) {
        $errors[] = 'Date is required.';
    } else {
        $date = $_POST['date'];
    }
    if (empty($_POST['expense_type'])) {
        $errors[] = 'Expense type is required.';
    } else {
        $expense_type = $_POST['expense_type'];
    }
    if (empty($_POST['amount'])) {
        $errors[] = 'Amount is required.';
    } else {
        $amount = (float)$_POST['amount'];
    }

    // If no errors, proceed with inserting data into the database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO expenses (date, expense_type, amount) VALUES (?, ?, ?)");
        $stmt->bind_param("ssd", $date, $expense_type, $amount);
        
        if ($stmt->execute()) {
            header("Location: financial.php"); // Redirect back to financial.php
            exit();
        } else {
            $errors[] = "Error adding expense: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch expenses from the database
$expensesList = [];
$result = $conn->query("SELECT * FROM expenses");
if ($result) {
    $expensesList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Error fetching expenses: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Financial Tracking</title>
    <link rel="stylesheet" href="css/d.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
                <li><a href="sales.php"><i class="fas fa-chart-line"></i> Sales Tracking</a></li>
                <li><a href="scheduling.php" class="active"><i class="fas fa-calendar-alt"></i> Scheduling</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main>
            <header>
                <h1>Financial Tracking</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>

            <section class="form-section">
                <h2>Add New Expense</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="date" name="date" required>
                    <input type="text" name="expense_type" placeholder="Expense Type" required>
                    <input type="number" step="0.01" name="amount" placeholder="Amount (Ksh)" required>
                    <button type="submit">Add Expense</button>
                </form>
            </section>

            <section class="table-section">
                <h2>Expenses List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Expense Type</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($expensesList) > 0): ?>
                            <?php foreach ($expensesList as $expense): ?>
                                <tr>
                                    <td><?php echo $expense['id']; ?></td>
                                    <td><?php echo $expense['date']; ?></td>
                                    <td><?php echo $expense['expense_type']; ?></td>
                                    <td>Ksh <?php echo number_format($expense['amount'], 2); ?></td> <!-- Displaying amount in Ksh -->
                                    <td>
                                        <a href="financial.php?delete=<?php echo $expense['id']; ?>" onclick="return confirm('Are you sure you want to delete this expense?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No expenses found.</td>
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
