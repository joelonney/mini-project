<?php
include '../includes/db.php';

header('Content-Type: application/json');

// Security Check (could add a token or check session if called from browser)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$today = date('Y-m-d');
$updates = 0;

// Fetch all "In Transit" trips
$q = "SELECT ts.id, ts.bus_id, ts.current_stop_index, b.route_id 
      FROM trip_status ts 
      JOIN buses b ON ts.bus_id = b.bus_id 
      WHERE ts.trip_date = '$today' AND ts.status = 'In Transit'";

$result = $conn->query($q);

if ($result && $result->num_rows > 0) {
    while($trip = $result->fetch_assoc()) {
        $trip_id = $trip['id'];
        $route_id = $trip['route_id'];
        $current_stop = $trip['current_stop_index'];
        
        // Find max stop index for this route
        $max_res = $conn->query("SELECT MAX(order_index) as max_idx FROM route_stops WHERE route_id = $route_id");
        $max_row = $max_res->fetch_assoc();
        $max_idx = $max_row['max_idx'] ?? 0;
        
        if ($current_stop < $max_idx) {
            // Increment stop
            $new_stop = $current_stop + 1;
            $conn->query("UPDATE trip_status SET current_stop_index = $new_stop WHERE id = $trip_id");
            $updates++;
        } else if ($current_stop >= $max_idx) {
            // Mark completed
            $conn->query("UPDATE trip_status SET status = 'Completed', current_stop_index = 999 WHERE id = $trip_id");
            $updates++;
        }
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Simulation tick completed.',
    'buses_updated' => $updates
]);
?>
