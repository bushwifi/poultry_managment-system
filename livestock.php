<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include your database connection file
include 'db.php'; 

// Initialize variables
$livestock_number = $breed = $age = $health_status = $production_type = '';
$errors = [];

// Handle form submission for adding new livestock
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['livestock_number'])) {
        $errors[] = 'Livestock number is required.';
    } else {
        $livestock_number = $_POST['livestock_number'];
    }
    if (empty($_POST['breed'])) {
        $errors[] = 'Breed is required.';
    } else {
        $breed = $_POST['breed'];
    }
    if (empty($_POST['age'])) {
        $errors[] = 'Age is required.';
    } else {
        $age = (int)$_POST['age'];
    }
    if (empty($_POST['health_status'])) {
        $errors[] = 'Health status is required.';
    } else {
        $health_status = $_POST['health_status'];
    }
    if (empty($_POST['production_type'])) {
        $production_type = NULL; // Allow this to be optional
    } else {
        $production_type = $_POST['production_type'];
    }

    // If no errors, proceed with inserting data into the database
    if (empty($errors)) {
        $date_added = date('Y-m-d'); // Current date
        $stmt = $conn->prepare("INSERT INTO livestock (livestock_number, breed, age, health_status, production_type, date_added) VALUES (?, ?, ?, ?, ?, ?)");
        // Updated bind_param to match the number and type of parameters
        $stmt->bind_param("ssisss", $livestock_number, $breed, $age, $health_status, $production_type, $date_added);
        
        if ($stmt->execute()) {
            header("Location: livestock.php"); // Redirect to the same page to avoid resubmission
            exit();
        } else {
            $errors[] = "Error adding livestock: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch livestock from the database
$livestockList = [];
$result = $conn->query("SELECT * FROM livestock");
if ($result) {
    $livestockList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Error fetching livestock: " . $conn->error;
}

// Handle deletion of livestock
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM livestock WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: livestock.php"); // Redirect to the same page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Livestock Management</title>
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
                <h1>Livestock Management</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>

            <section class="form-section">
                <h2>Add New Livestock</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="text" name="livestock_number" placeholder="Livestock Number" required>
                    <input type="text" name="breed" placeholder="Breed" required>
                    <input type="number" name="age" placeholder="Age (weeks)" required>
                    <input type="text" name="health_status" placeholder="Health Status" required>
                    <input type="text" name="production_type" placeholder="Production Type (optional)">
                    <button type="submit">Add Livestock</button>
                </form>
            </section>

            <section class="table-section">
                <h2>Livestock List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Livestock Number</th>
                            <th>Breed</th>
                            <th>Age (weeks)</th>
                            <th>Health Status</th>
                            <th>Production Type</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($livestockList) > 0): ?>
                            <?php foreach ($livestockList as $livestock): ?>
                                <tr>
                                    <td><?php echo $livestock['id']; ?></td>
                                    <td><?php echo $livestock['livestock_number']; ?></td>
                                    <td><?php echo $livestock['breed']; ?></td>
                                    <td><?php echo $livestock['age']; ?></td>
                                    <td><?php echo $livestock['health_status']; ?></td>
                                    <td><?php echo $livestock['production_type']; ?></td>
                                    <td><?php echo $livestock['date_added']; ?></td>
                                    <td>
                                        <a href="livestock.php?delete=<?php echo $livestock['id']; ?>" onclick="return confirm('Are you sure you want to delete this livestock?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8">No livestock found.</td>
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
