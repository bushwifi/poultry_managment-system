<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Include your database connection file
include 'db.php';

// Initialize variables
$activity = $scheduled_date = $frequency = '';
$errors = [];
$filterActivity = '';

// Handle delete request
if (isset($_GET['delete'])) {
    $schedule_id = (int)$_GET['delete'];

    // Prepare and execute delete statement
    $stmt = $conn->prepare("DELETE FROM schedule WHERE id = ?");
    $stmt->bind_param("i", $schedule_id);

    if ($stmt->execute()) {
        // Redirect to avoid re-executing delete on page refresh
        header("Location: scheduling.php");
        exit();
    } else {
        $errors[] = "Error deleting schedule: " . $stmt->error;
    }
    $stmt->close();
}

// Handle form submission for adding schedules
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty($_POST['activity'])) {
        $errors[] = 'Activity is required.';
    } else {
        $activity = $_POST['activity'];
    }
    if (empty($_POST['scheduled_date'])) {
        $errors[] = 'Scheduled date is required.';
    } else {
        $scheduled_date = $_POST['scheduled_date'];
    }
    if (empty($_POST['frequency'])) {
        $errors[] = 'Frequency is required.';
    } else {
        $frequency = $_POST['frequency'];
    }

    // If no errors, proceed with inserting data into the database
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO schedule (activity, scheduled_date, frequency) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $activity, $scheduled_date, $frequency);

        if ($stmt->execute()) {
            header("Location: scheduling.php");
            exit();
        } else {
            $errors[] = "Error adding schedule: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle filter request
if (isset($_GET['filter'])) {
    $filterActivity = $_GET['filter'];
}

// Fetch schedules from the database with optional filtering
$scheduleList = [];
$sql = "SELECT * FROM schedule";
if (!empty($filterActivity)) {
    $sql .= " WHERE activity = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filterActivity);
} else {
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $scheduleList = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $errors[] = "Error fetching schedules: " . $conn->error;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheduling</title>
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
                <li><a href="sales.php"><i class="fas fa-chart-line"></i> Sales Tracking</a></li>
                <li><a href="scheduling.php" class="active"><i class="fas fa-calendar-alt"></i> Scheduling</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <main>
            <header>
                <h1>Scheduling</h1>
                <a href="logout.php" class="logout-btn">Logout</a>
            </header>

            <section class="form-section">
                <h2>Add New Schedule</h2>
                <?php if (!empty($errors)): ?>
                    <div class="error-messages">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="post" action="">
                    <input type="text" name="activity" placeholder="Activity (e.g., Feeding, Vaccination)" required>
                    <input type="date" name="scheduled_date" required>
                    <input type="text" name="frequency" placeholder="Frequency (e.g., Daily, Weekly)" required>
                    <button type="submit">Add Schedule</button>
                </form>
            </section>

            <section class="filter-section">
                <h2>Filter Schedules</h2>
                <form method="get" action="">
                    <select name="filter" onchange="this.form.submit()">
                        <option value="">-- Select Activity --</option>
                        <option value="Feeding" <?php if($filterActivity == 'Feeding') echo 'selected'; ?>>Feeding</option>
                        <option value="Vaccination" <?php if($filterActivity == 'Vaccination') echo 'selected'; ?>>Vaccination</option>
                        <option value="Cleaning" <?php if($filterActivity == 'Cleaning') echo 'selected'; ?>>Cleaning</option>
                        <!-- Add more activities as needed -->
                    </select>
                </form>
            </section>

            <section class="table-section">
                <h2>Schedule List</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Activity</th>
                            <th>Scheduled Date</th>
                            <th>Frequency</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($scheduleList) > 0): ?>
                            <?php foreach ($scheduleList as $schedule): ?>
                                <tr>
                                    <td><?php echo $schedule['id']; ?></td>
                                    <td><?php echo $schedule['activity']; ?></td>
                                    <td><?php echo $schedule['scheduled_date']; ?></td>
                                    <td><?php echo $schedule['frequency']; ?></td>
                                    <td>
                                        <a href="scheduling.php?delete=<?php echo $schedule['id']; ?>" onclick="return confirm('Are you sure you want to delete this schedule?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No schedules found.</td>
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
