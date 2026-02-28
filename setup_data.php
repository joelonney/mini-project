<?php
include 'includes/db.php';

function seedData($conn) {
    echo "Starting Data Seeding...\n";

    // 1. Clear existing data (Optional: comment out if you want to keep data)
    // $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    // $conn->query("TRUNCATE TABLE seats");
    // $conn->query("TRUNCATE TABLE buses");
    // $conn->query("TRUNCATE TABLE routes");
    // $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    // 2. Routes
    $routes = [
        ['Bangalore', 'Kochi', 550],
        ['Bangalore', 'Chennai', 350],
        ['Mumbai', 'Pune', 150],
        ['Delhi', 'Jaipur', 280],
        ['Chennai', 'Madurai', 460],
        ['Hyderabad', 'Bangalore', 570]
    ];

    echo "Seeding Routes...\n";
    foreach ($routes as $r) {
        $check = $conn->query("SELECT route_id FROM routes WHERE source = '$r[0]' AND destination = '$r[1]'");
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO routes (source, destination, distance_km) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $r[0], $r[1], $r[2]);
            $stmt->execute();
        }
    }

    // 3. Buses
    echo "Seeding Buses...\n";
    $busTypes = ['AC', 'NON-AC', 'SLEEPER'];
    $travels = ['KSRTC', 'Orange', 'SRS', 'VRL', 'Jabbar', 'GreenLine', 'Kallada'];
    
    // Fetch all route IDs
    $routeRes = $conn->query("SELECT route_id FROM routes");
    $routeIds = [];
    while ($row = $routeRes->fetch_assoc()) {
        $routeIds[] = $row['route_id'];
    }

    if (empty($routeIds)) {
        die("Error: No routes found. Cannot seed buses.\n");
    }

    for ($i = 1; $i <= 20; $i++) {
        $busNum = 'KA-' . rand(10, 99) . '-BUS-' . rand(1000, 9999);
        
        // Check if bus exists
        $check = $conn->query("SELECT bus_id FROM buses WHERE bus_number = '$busNum'");
        if ($check->num_rows > 0) continue;

        $routeId = $routeIds[array_rand($routeIds)];
        $type = $busTypes[array_rand($busTypes)];
        $totalSeats = ($type === 'SLEEPER') ? 20 : 40; // 20 for sleeper (10U + 10L), 40 for seater
        
        // Random Times
        $h = rand(0, 23); $m = rand(0, 59);
        $depTime = sprintf("%02d:%02d:00", $h, $m);
        // Arr time adds 5-10 hours
        $ah = ($h + rand(5, 10)) % 24;
        $am = rand(0, 59);
        $arrTime = sprintf("%02d:%02d:00", $ah, $am);

        // Operator Name (stored in bus_number or specialized field? 
        // Schema doesn't have operator name, so we'll prepend to bus number or just assume generic)
        // Let's just use the bus_number for now as per schema specific. 
        // Wait, schema has: bus_id, route_id, bus_number, bus_type, total_seats, departure_time, arrival_time.
        // We lack "operator name" in schema. I will add it if I can, but user wants schema usage. 
        // I will assume we can stick to standard schema. 
        
        $stmt = $conn->prepare("INSERT INTO buses (route_id, bus_number, bus_type, total_seats, departure_time, arrival_time) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ississ", $routeId, $busNum, $type, $totalSeats, $depTime, $arrTime);
        if ($stmt->execute()) {
            $busId = $conn->insert_id;
            seedSeats($conn, $busId, $type);
        }
    }

    echo "Seeding Completed!\n";
}

function seedSeats($conn, $busId, $type) {
    if ($type === 'SLEEPER') {
        // 10 Upper, 10 Lower
        for ($i = 1; $i <= 10; $i++) {
            $conn->query("INSERT INTO seats (bus_id, seat_number, is_available) VALUES ($busId, 'L$i', 1)");
            $conn->query("INSERT INTO seats (bus_id, seat_number, is_available) VALUES ($busId, 'U$i', 1)");
        }
    } else {
        // 40 Seater (L1..L10, R1..R10 etc. - simpler naming like L1-L10, R1-R10, or just 1-40)
        // Previous seats.php logic uses L1..L10, R1..R10, M1..M10, A1..A10 to match layout
        // Let's stick to the logic seen in seats.php: L, M, A, R columns * 10 rows
        $cols = ['L', 'M', 'A', 'R'];
        for ($row = 1; $row <= 10; $row++) {
            foreach ($cols as $col) {
                $seatNum = $col . $row;
                $conn->query("INSERT INTO seats (bus_id, seat_number, is_available) VALUES ($busId, '$seatNum', 1)");
            }
        }
    }
}

seedData($conn);
?>
