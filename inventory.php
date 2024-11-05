<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include your database connection file
include 'db.php'; 

// Initialize variables
$supply_name = $quantity = $unit = $type = '';
$errors = [];

// Handle form submission for adding a new supply
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['supply_name'])) {
        $errors[] = 'Supply name is required.';
    } else {
        $supply_name = $_POST['supply_name'];
    }
    if (empty($_POST['quantity'])) {
        $errors[] = 'Quantity is required.';
    } else {
        $quantity = (int)$_POST['quantity'];
    }
    if (empty($_POST['unit'])) {
        $errors[] = 'Unit is required.';
    } else {
        $unit = $_POST['unit'];
    }
    if (empty($_POST['type'])) {
        $errors[] = 'Type is required.';
    } else {
        $type = $_POST['type'];
    }

    // If no errors, proceed with inserting data into the database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO supplies (supply_name, quantity, unit, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siss", $supply_name, $quantity, $unit, $type);
        
        if ($stmt->execute()) {
            header("Location: inventory.php"); // Redirect to the same page to avoid resubmission
            exit();
        } else {
            $errors[] = "Error adding supply: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch supplies from the database
$supplies = [];
$result = $conn->query("SELECT * FROM supplies");
if ($result) {
    $supplies = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Error fetching supplies: " . $conn->error;
}

// Handle deletion of a supply
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM supplies WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: inventory.php"); // Redirect to the same page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
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
                <h1>Inventory Management</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>

            <section class="form-section">
                <h2>Add New Supply</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="text" name="supply_name" placeholder="Supply Name" required>
                    <input type="number" name="quantity" placeholder="Quantity" required>
                    <input type="text" name="unit" placeholder="Unit (e.g., kg)" required>
                    <input type="text" name="type" placeholder="Type (e.g., feed)" required>
                    <button type="submit">Add Supply</button>
                </form>
            </section>

            <section class="table-section">
                <h2>Supplies List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supply Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($supplies) > 0): ?>
                            <?php foreach ($supplies as $supply): ?>
                                <tr>
                                    <td><?php echo $supply['id']; ?></td>
                                    <td><?php echo $supply['supply_name']; ?></td>
                                    <td><?php echo $supply['quantity']; ?></td>
                                    <td><?php echo $supply['unit']; ?></td>
                                    <td><?php echo $supply['type']; ?></td>
                                    <td>
                                        <a href="inventory.php?delete=<?php echo $supply['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No supplies found.</td>
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
