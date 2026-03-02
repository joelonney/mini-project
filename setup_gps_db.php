<?php
include 'includes/db.php';

echo "Setting up GPS Tracking Database Tables...\n";

// 1. Create `route_stops` table
$sql_route_stops = "CREATE TABLE IF NOT EXISTS route_stops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    route_id INT NOT NULL,
    order_index INT NOT NULL,
    stop_name VARCHAR(100) NOT NULL,
    time_from_start INT NOT NULL, -- minutes from the start of the journey
    FOREIGN KEY (route_id) REFERENCES routes(route_id) ON DELETE CASCADE
)";

if ($conn->query($sql_route_stops) === TRUE) {
    echo "Table 'route_stops' created successfully or already exists.\n";
} else {
    echo "Error creating table 'route_stops': " . $conn->error . "\n";
}

// 2. Create `trip_status` table
$sql_trip_status = "CREATE TABLE IF NOT EXISTS trip_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bus_id INT NOT NULL,
    trip_date DATE NOT NULL,
    current_stop_index INT DEFAULT 0,
    status ENUM('Not Started', 'In Transit', 'Delayed', 'Completed') DEFAULT 'Not Started',
    last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (bus_id) REFERENCES buses(bus_id) ON DELETE CASCADE,
    UNIQUE KEY unique_trip (bus_id, trip_date)
)";

if ($conn->query($sql_trip_status) === TRUE) {
    echo "Table 'trip_status' created successfully or already exists.\n";
} else {
    echo "Error creating table 'trip_status': " . $conn->error . "\n";
}

// 3. Seed `route_stops`
echo "Seeding route stops...\n";
$routes = $conn->query("SELECT * FROM routes");
if ($routes->num_rows > 0) {
    while ($route = $routes->fetch_assoc()) {
        $route_id = $route['route_id'];
        $source = $route['source'];
        $dest = $route['destination'];
        
        // Only seed if no stops exist for this route
        $check = $conn->query("SELECT COUNT(*) as count FROM route_stops WHERE route_id = $route_id")->fetch_assoc();
        if ($check['count'] == 0) {
            // Generate some dummy stops between source and destination
            $stops = [
                ['name' => $source . ' Main Stand', 'time' => 0],
                ['name' => 'Highway Plaza', 'time' => 60],
                ['name' => 'Midway Rest Stop', 'time' => 180],
                ['name' => 'City Outskirts', 'time' => 300],
                ['name' => $dest . ' Central', 'time' => 360]
            ];
            
            $order = 0;
            foreach ($stops as $stop) {
                $name = $conn->real_escape_string($stop['name']);
                $time = $stop['time'];
                $conn->query("INSERT INTO route_stops (route_id, order_index, stop_name, time_from_start) VALUES ($route_id, $order, '$name', $time)");
                $order++;
            }
        }
    }
}

// 4. Initialize `trip_status` for today's buses
echo "Initializing trip status for today...\n";
$today = date('Y-m-d');
$buses = $conn->query("SELECT bus_id FROM buses");
if ($buses->num_rows > 0) {
    while ($bus = $buses->fetch_assoc()) {
        $bus_id = $bus['bus_id'];
        $stmt = $conn->prepare("INSERT IGNORE INTO trip_status (bus_id, trip_date, current_stop_index, status) VALUES (?, ?, 0, 'Not Started')");
        $stmt->bind_param("is", $bus_id, $today);
        $stmt->execute();
    }
}

echo "GPS Database Setup Completed.\n";
?>
