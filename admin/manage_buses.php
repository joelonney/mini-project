<?php
include '../includes/db.php';

// 1. Security Check: Ensure user is Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

// 2. Handle Form Submissions (Add or Delete)
$message = "";

// A. Add Bus Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_bus'])) {
    $bus_number = $_POST['bus_number'];
    $route_id = $_POST['route_id'];
    $bus_type = $_POST['bus_type'];
    $seats = $_POST['total_seats'];
    $base_price = $_POST['base_price'] ?? 0.00;
    $dep_time = $_POST['departure_time'];
    $arr_time = $_POST['arrival_time'];

    // Prepared statement to prevent SQL Injection
    $sql = "INSERT INTO buses (bus_number, route_id, bus_type, total_seats, base_price, departure_time, arrival_time) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sisidss", $bus_number, $route_id, $bus_type, $seats, $base_price, $dep_time, $arr_time);

    if ($stmt->execute()) {
        $new_bus_id = $conn->insert_id;
        
        // Generate seats for the newly added bus
        $seat_numbers = [];
        if ($bus_type === 'SLEEPER') {
            // Sleeper layout: U1..U(n/2) and L1..L(n/2)
            $rows = ceil($seats / 2);
            for ($i = 1; $i <= $rows; $i++) {
                if (count($seat_numbers) < $seats) $seat_numbers[] = 'U' . $i;
                if (count($seat_numbers) < $seats) $seat_numbers[] = 'L' . $i;
            }
        } else {
            // Seater layout: L, M, A, R
            $rows = ceil($seats / 4);
            $positions = ['L', 'M', 'A', 'R'];
            for ($i = 1; $i <= $rows; $i++) {
                foreach ($positions as $pos) {
                    if (count($seat_numbers) < $seats) {
                        $seat_numbers[] = $pos . $i;
                    }
                }
            }
        }
        
        $seat_sql = "INSERT INTO seats (bus_id, seat_number, is_available) VALUES (?, ?, 1)";
        $seat_stmt = $conn->prepare($seat_sql);
        foreach ($seat_numbers as $s_num) {
            $seat_stmt->bind_param("is", $new_bus_id, $s_num);
            $seat_stmt->execute();
        }

        $message = "<div class='alert alert-success'>Bus added successfully with " . count($seat_numbers) . " seats!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
    }
}

// B. Delete Bus Logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM buses WHERE bus_id=$id");
    header("Location: manage_buses.php"); // Refresh page to show updated list
    exit();
}

// 3. Fetch Data for Display
// Get all routes for the selection dropdown
$routes = $conn->query("SELECT * FROM routes");

// Get all buses joined with routes to show Source and Destination
$buses = $conn->query("
    SELECT b.*, r.source, r.destination 
    FROM buses b 
    JOIN routes r ON b.route_id = r.route_id 
    ORDER BY b.bus_id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Buses - SmartBus Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { background-color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important; }
        .table thead { background-color: #E53935; color: white; }
        .btn-primary { background-color: #E53935; border-color: #E53935; }
        .btn-primary:hover { background-color: #D32F2F; border-color: #D32F2F; }
        .text-primary { color: #E53935 !important; }
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-tools me-2 text-primary"></i>Manage Fleet</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Manage Buses</li>
                </ol>
            </nav>
        </div>

        <?php echo $message; ?>

        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Add New Bus</div>
                    <div class="card-body">
                        <form method="POST" action="manage_buses.php">
                            <div class="mb-3">
                                <label class="form-label small text-muted">Bus Number</label>
                                <input type="text" name="bus_number" class="form-control" placeholder="e.g. KL-01-AX-9999" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small text-muted">Route Assignment</label>
                                <select name="route_id" class="form-select" required>
                                    <option value="">-- Select Route --</option>
                                    <?php while($r = $routes->fetch_assoc()): ?>
                                        <option value="<?php echo $r['route_id']; ?>">
                                            <?php echo htmlspecialchars($r['source'] . " ➔ " . $r['destination']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Type</label>
                                    <select name="bus_type" class="form-select">
                                        <option value="AC">AC</option>
                                        <option value="NON-AC">Non-AC</option>
                                        <option value="SLEEPER">Sleeper</option>
                                    </select>
                                </div>
                                <div class="col-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Seats</label>
                                    <input type="number" name="total_seats" class="form-control" value="40" required>
                                </div>
                                <div class="col-4 mb-3">
                                    <label class="form-label small fw-bold text-muted">Price (₹)</label>
                                    <input type="number" step="0.01" name="base_price" class="form-control" value="500.00" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label small text-muted">Departure Time</label>
                                    <input type="time" name="departure_time" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label small text-muted">Arrival Time</label>
                                    <input type="time" name="arrival_time" class="form-control" required>
                                </div>
                            </div>

                            <button type="submit" name="add_bus" class="btn btn-primary w-100">Add to Fleet</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white fw-bold">Fleet Overview</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Bus No.</th>
                                        <th>Route</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Seats</th>
                                        <th>Schedule</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($buses->num_rows > 0): ?>
                                        <?php while($row = $buses->fetch_assoc()): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($row['bus_number']); ?></td>
                                            <td>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($row['source']); ?></small>
                                                <i class="fas fa-long-arrow-alt-down text-primary" style="font-size: 0.7rem;"></i>
                                                <small class="text-muted d-block"><?php echo htmlspecialchars($row['destination']); ?></small>
                                            </td>
                                            <td><span class="badge bg-secondary"><?php echo $row['bus_type']; ?></span></td>
                                            <td class="fw-bold text-success">₹<?php echo $row['base_price']; ?></td>
                                            <td><?php echo $row['total_seats']; ?></td>
                                            <td>
                                                <small><?php echo substr($row['departure_time'], 0, 5); ?> - <?php echo substr($row['arrival_time'], 0, 5); ?></small>
                                            </td>
                                            <td>
                                                <a href="manage_buses.php?delete=<?php echo $row['bus_id']; ?>" 
                                                   class="btn btn-outline-danger btn-sm" 
                                                   onclick="return confirm('Delete this bus and all associated seat records?');">
                                                   <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No buses available.</td>
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