<?php
include '../includes/db.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// Handle Status Update
if (isset($_GET['mark_claimed'])) {
    $id = intval($_GET['mark_claimed']);
    $conn->query("UPDATE lost_and_found SET status='CLAIMED' WHERE item_id=$id");
    header("Location: lost_and_found.php");
    exit();
}

// Handle Add Item
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_item'])) {
    $bus_id = $_POST['bus_id'];
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("INSERT INTO lost_and_found (bus_id, description) VALUES (?, ?)");
    $stmt->bind_param("is", $bus_id, $description);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Item recorded successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// Fetch Data
$items = $conn->query("
    SELECT l.*, b.bus_number 
    FROM lost_and_found l 
    LEFT JOIN buses b ON l.bus_id = b.bus_id 
    ORDER BY l.found_date DESC
");

$buses = $conn->query("SELECT bus_id, bus_number FROM buses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Lost & Found - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { background-color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important; }
        .table thead { background-color: #E53935; color: white; }
        .btn-warning { background-color: #E53935 !important; border-color: #E53935 !important; color: white !important;}
        .text-warning { color: #E53935 !important; }
        .navbar-custom { background-color: #E53935; box-shadow: 0 4px 15px rgba(229, 57, 53, 0.2); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom p-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-bus-alt me-2 text-white"></i>SmartBus Admin
            </a>
            <a href="../includes/logout.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-search me-2 text-warning"></i>Lost & Found Records</h2>
        <?php echo $message; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm p-3">
                    <h5>Report Found Item</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label>Bus Number</label>
                            <select name="bus_id" class="form-select" required>
                                <option value="">-- Select Bus --</option>
                                <?php while($b = $buses->fetch_assoc()): ?>
                                    <option value="<?php echo $b['bus_id']; ?>">
                                        <?php echo htmlspecialchars($b['bus_number']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <button type="submit" name="add_item" class="btn btn-warning w-100">Add Record</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date</th>
                                    <th>Bus</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($items->num_rows > 0): ?>
                                    <?php while($row = $items->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('d M Y', strtotime($row['found_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($row['bus_number']); ?></td>
                                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $row['status'] == 'FOUND' ? 'danger' : 'success'; ?>">
                                                    <?php echo $row['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($row['status'] == 'FOUND'): ?>
                                                    <a href="lost_and_found.php?mark_claimed=<?php echo $row['item_id']; ?>" 
                                                       class="btn btn-sm btn-outline-success">
                                                       Mark Claimed
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted"><i class="fas fa-check-circle"></i> Done</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No records found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
