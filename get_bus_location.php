<?php
include 'includes/db.php';
header('Content-Type: application/json');

if (!isset($_GET['bus_no'])) {
    echo json_encode(['error' => 'Bus number is required']);
    exit();
}

$bus_no = $conn->real_escape_string(strtoupper(trim($_GET['bus_no'])));
$today = date('Y-m-d');

// Find the trip status for this bus today
$q = "SELECT ts.current_stop_index, ts.status, ts.last_updated, b.route_id, r.source, r.destination
      FROM trip_status ts
      JOIN buses b ON ts.bus_id = b.bus_id
      JOIN routes r ON b.route_id = r.route_id
      WHERE b.bus_number = '$bus_no' AND ts.trip_date = '$today'";

$res = $conn->query($q);

if ($res && $res->num_rows > 0) {
    $trip = $res->fetch_assoc();
    $route_id = $trip['route_id'];
    
    // Fetch stops
    $stops = [];
    $s_res = $conn->query("SELECT order_index, stop_name, time_from_start FROM route_stops WHERE route_id = $route_id ORDER BY order_index ASC");
    while($s = $s_res->fetch_assoc()) {
        $stops[] = $s;
    }
    
    echo json_encode([
        'success' => true,
        'bus_no' => $bus_no,
        'source' => $trip['source'],
        'destination' => $trip['destination'],
        'status' => $trip['status'],
        'current_stop_index' => (int)$trip['current_stop_index'],
        'last_updated' => $trip['last_updated'],
        'stops' => $stops
    ]);
} else {
    echo json_encode(['error' => 'No active trip found for this bus today.']);
}
?>
