<?php
include '../includes/db.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}

$today = date('Y-m-d');
$message = '';

// Handle manual overrides
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_trip') {
    $trip_id = (int)$_POST['trip_id'];
    $new_status = $conn->real_escape_string($_POST['status']);
    $new_stop = (int)$_POST['current_stop_index'];
    
    $stmt = $conn->prepare("UPDATE trip_status SET status = ?, current_stop_index = ? WHERE id = ?");
    $stmt->bind_param("sii", $new_status, $new_stop, $trip_id);
    if ($stmt->execute()) {
        $message = "Trip updated successfully.";
    } else {
        $message = "Error updating trip.";
    }
}

// Handle bulk complete override
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'complete_all') {
    $stmt = $conn->prepare("UPDATE trip_status SET status = 'Completed', current_stop_index = 999 WHERE trip_date = ?");
    $stmt->bind_param("s", $today);
    if ($stmt->execute()) {
        $message = "All trips for today have been marked as Completed.";
    }
}

// Fetch all trips for today
$q = "SELECT ts.id as trip_id, ts.bus_id, ts.status, ts.current_stop_index, b.bus_number, r.source, r.destination, r.route_id, b.departure_time 
      FROM trip_status ts 
      JOIN buses b ON ts.bus_id = b.bus_id 
      JOIN routes r ON b.route_id = r.route_id 
      WHERE ts.trip_date = '$today'
      ORDER BY b.departure_time ASC";
$trips_result = $conn->query($q);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>GPS Trip Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { background-color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar-custom { background-color: #E53935; box-shadow: 0 4px 15px rgba(229, 57, 53, 0.2); }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom p-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-arrow-left me-2 text-white"></i>Back to Dashboard
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Live GPS Trip Management</h2>
            <div class="d-flex gap-2">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="complete_all">
                    <button type="submit" class="btn btn-success fw-bold rounded-pill" onclick="return confirm('Are you sure you want to mark ALL trips today as Completed?');">
                        <i class="fas fa-check-double me-2"></i> Complete All Trips
                    </button>
                </form>
                <button id="toggleSim" class="btn btn-outline-primary fw-bold rounded-pill">
                    <i class="fas fa-play me-2"></i> Start Auto-Simulation
                </button>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="card p-4">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Bus Number</th>
                            <th>Route</th>
                            <th>Departure</th>
                            <th>Status</th>
                            <th>Current Stop</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if ($trips_result && $trips_result->num_rows > 0): 
                            while($trip = $trips_result->fetch_assoc()):
                                // Fetch stops for this route
                                $stops_res = $conn->query("SELECT * FROM route_stops WHERE route_id = " . $trip['route_id'] . " ORDER BY order_index ASC");
                                $stops = [];
                                while($s = $stops_res->fetch_assoc()){
                                    $stops[] = $s;
                                }
                        ?>
                        <tr>
                            <form method="POST">
                                <input type="hidden" name="action" value="update_trip">
                                <input type="hidden" name="trip_id" value="<?php echo $trip['trip_id']; ?>">
                                
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($trip['bus_number']); ?></span></td>
                                <td><?php echo htmlspecialchars($trip['source'] . ' → ' . $trip['destination']); ?></td>
                                <td><?php echo file_exists('format') ? date('h:i A', strtotime($trip['departure_time'])) : $trip['departure_time']; ?></td>
                                <td>
                                    <select name="status" class="form-select form-select-sm">
                                        <option value="Not Started" <?php echo $trip['status'] == 'Not Started' ? 'selected' : ''; ?>>Not Started</option>
                                        <option value="In Transit" <?php echo $trip['status'] == 'In Transit' ? 'selected' : ''; ?>>In Transit</option>
                                        <option value="Delayed" <?php echo $trip['status'] == 'Delayed' ? 'selected' : ''; ?>>Delayed</option>
                                        <option value="Completed" <?php echo $trip['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </td>
                                <td>
                                    <select name="current_stop_index" class="form-select form-select-sm">
                                        <?php foreach($stops as $stop): ?>
                                            <option value="<?php echo $stop['order_index']; ?>" <?php echo $trip['current_stop_index'] == $stop['order_index'] ? 'selected' : ''; ?>>
                                                (<?php echo $stop['order_index']; ?>) <?php echo htmlspecialchars($stop['stop_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="999" <?php echo $trip['current_stop_index'] == 999 ? 'selected' : ''; ?>>Finished Line</option>
                                    </select>
                                </td>
                                <td>
                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">Update</button>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr><td colspan="6" class="text-center text-muted">No trips initialized for today. Did you run setup_gps_db.php?</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        let simInterval = null;
        const btn = document.getElementById('toggleSim');
        
        btn.addEventListener('click', () => {
            if (simInterval) {
                clearInterval(simInterval);
                simInterval = null;
                btn.innerHTML = '<i class="fas fa-play me-2"></i> Start Auto-Simulation';
                btn.classList.replace('btn-danger', 'btn-outline-primary');
            } else {
                btn.innerHTML = '<i class="fas fa-stop me-2"></i> Stop Auto-Simulation';
                btn.classList.replace('btn-outline-primary', 'btn-danger');
                
                // Run immediately, then every 10 seconds
                runSim();
                simInterval = setInterval(runSim, 10000);
            }
        });

        function runSim() {
            fetch('simulate_gps.php')
                .then(res => res.json())
                .then(data => {
                    console.log('Simulated tick:', data);
                    // Reload page to reflect changes slightly delayed so user sees updates
                    setTimeout(() => window.location.reload(), 500);
                })
                .catch(err => console.error('Sim error', err));
        }
    </script>
</body>
</html>
