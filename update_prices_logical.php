<?php
include 'includes/db.php';

// Fetch all buses with route distances
$sql = "SELECT b.bus_id, b.bus_type, r.distance_km FROM buses b JOIN routes r ON b.route_id = r.route_id";
$result = $conn->query($sql);

$updatedCount = 0;
while($row = $result->fetch_assoc()){
    $bus_id = $row['bus_id'];
    $type = $row['bus_type'];
    $dist = $row['distance_km'];
    
    // Base multiplier based on type
    $mult = 0;
    if($type == 'SLEEPER'){
        $mult = 2.5; 
    } else if($type == 'AC'){
        $mult = 1.8;
    } else { // NON-AC
        $mult = 1.2;
    }
    
    // Calculate reasonable price and round to nearest 10
    $price = ceil($dist * $mult / 10) * 10;
    if($price < 150) $price = 150; // Minimum reasonable price
    
    // Add some random variation (-20 to +20 INR) so identical route buses aren't perfectly identical
    $variation = rand(-2, 2) * 10;
    $final_price = $price + $variation;
    
    $updateStmt = $conn->prepare("UPDATE buses SET base_price = ? WHERE bus_id = ?");
    $updateStmt->bind_param("di", $final_price, $bus_id);
    if($updateStmt->execute()){
        $updatedCount++;
    }
}
echo "Prices reasonably updated for $updatedCount buses based on route distances.";
?>
