<?php
include '../includes/db.php';

// 1. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// 2. Handle Form Submissions
 $message = "";

// A. Add Route Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_route'])) {
    $source = $_POST['source'];
    $destination = $_POST['destination'];
    $distance = $_POST['distance_km'];

    // Prepared statement to prevent SQL Injection
    $sql = "INSERT INTO routes (source, destination, distance_km) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $source, $destination, $distance);

    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Route added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// B. Delete Route Logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Note: In a real app, check if buses are using this route before deleting.
    $conn->query("DELETE FROM routes WHERE route_id=$id");
    header("Location: manage_route.php"); // Refresh page
    exit();
}

// 3. Fetch Data for Display
 $routes = $conn->query("SELECT * FROM routes ORDER BY route_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Routes - SmartBus Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 15px; }
        .table thead { background-color: #212529; color: white; }
    </style>
</head>
<body>

    <nav class="navbar navbar-dark bg-dark p-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-bus-alt me-2 text-danger"></i>SmartBus Admin
            </a>
            <a href="../includes/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-map-marked-alt me-2 text-primary"></i>Manage Routes</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage Routes</li>
                </ol>
            </nav>
        </div>

        <?php echo $message; ?>

        <div class="row">
            <!-- Add Route Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Add New Route</div>
                    <div class="card-body">
                        <form method="POST" action="manage_route.php">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Source City</label>
                                <input type="text" name="source" class="form-control" placeholder="e.g. Kochi" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted">Destination City</label>
                                <input type="text" name="destination" class="form-control" placeholder="e.g. Bangalore" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted">Distance (km)</label>
                                <input type="number" name="distance_km" class="form-control" placeholder="e.g. 550" required>
                            </div>

                            <button type="submit" name="add_route" class="btn btn-primary w-100">Add Route</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Routes Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">Route Overview</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Source</th>
                                        <th>Destination</th>
                                        <th>Distance (km)</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($routes->num_rows > 0): ?>
                                        <?php while($row = $routes->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['route_id']; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['source']); ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['destination']); ?></td>
                                            <td><?php echo $row['distance_km']; ?> km</td>
                                            <td>
                                                <a href="manage_route.php?delete=<?php echo $row['route_id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('Are you sure you want to delete this route? Buses linked to this route might be affected.');">
                                                   <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No routes available.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>