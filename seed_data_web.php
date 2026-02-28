<?php
include 'includes/db.php';

echo "<html><body style='font-family:sans-serif; padding:20px;'>";
echo "<h2>Database Seeder</h2>";
echo "<p>Starting data population...</p>";

echo "<ul>";

// 1. Routes
$routes = [
    ['Bangalore', 'Kochi', 550],
    ['Bangalore', 'Chennai', 350],
    ['Mumbai', 'Pune', 150],
    ['Delhi', 'Jaipur', 280],
    ['Chennai', 'Madurai', 460],
    ['Hyderabad', 'Bangalore', 570]
];

foreach ($routes as $r) {
    $check = $conn->query("SELECT route_id FROM routes WHERE source = '$r[0]' AND destination = '$r[1]'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO routes (source, destination, distance_km) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $r[0], $r[1], $r[2]);
        if($stmt->execute()) echo "<li>Added Route: $r[0] to $r[1]</li>";
    } else {
        echo "<li style='color:gray'>Route $r[0]-$r[1] exists.</li>";
    }
}

// 2. Buses & Seats
$busTypes = ['AC', 'NON-AC', 'SLEEPER'];
$routeRes = $conn->query("SELECT route_id FROM routes");
$routeIds = [];
while ($row = $routeRes->fetch_assoc()) $routeIds[] = $row['route_id'];

if (!empty($routeIds)) {
    $count = 0;
    for ($i = 1; $i <= 20; $i++) {
        $busNum = 'KA-' . rand(10, 99) . '-BUS-' . rand(1000, 9999);
        $check = $conn->query("SELECT bus_id FROM buses WHERE bus_number = '$busNum'");
        if ($check->num_rows > 0) continue;

        $routeId = $routeIds[array_rand($routeIds)];
        $type = $busTypes[array_rand($busTypes)];
        $totalSeats = ($type === 'SLEEPER') ? 20 : 40;
        
        $h = rand(0, 23); $m = rand(0, 59);
        $depTime = sprintf("%02d:%02d:00", $h, $m);
        $ah = ($h + rand(5, 10)) % 24;
        $am = rand(0, 59);
        $arrTime = sprintf("%02d:%02d:00", $ah, $am);

        $stmt = $conn->prepare("INSERT INTO buses (route_id, bus_number, bus_type, total_seats, departure_time, arrival_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $routeId, $busNum, $type, $totalSeats, $depTime, $arrTime);
        if ($stmt->execute()) {
            $busId = $conn->insert_id;
            seedSeats($conn, $busId, $type);
            $count++;
        }
    }
    echo "<li>Added $count new buses with seats.</li>";
}

echo "</ul>";
echo "<h3 style='color:green'>Seeding Completed!</h3>";
echo "<a href='index.php'>Go to Home</a>";
echo "</body></html>";

function seedSeats($conn, $busId, $type) {
    if ($type === 'SLEEPER') {
        for ($i = 1; $i <= 10; $i++) {
            $conn->query("INSERT INTO seats (bus_id, seat_number, is_available) VALUES ($busId, 'L$i', 1)");
            $conn->query("INSERT INTO seats (bus_id, seat_number, is_available) VALUES ($busId, 'U$i', 1)");
        }
    } else {
        $cols = ['L', 'M', 'A', 'R'];
        for ($row = 1; $row <= 10; $row++) {
            foreach ($cols as $col) {
                $seatNum = $col . $row;
                $conn->query("INSERT INTO seats (bus_id, seat_number, is_available) VALUES ($busId, '$seatNum', 1)");
            }
        }
    }
}
?>
