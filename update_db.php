<?php
include 'includes/db.php';

// Add base_price to buses
$sql = "ALTER TABLE buses ADD COLUMN IF NOT EXISTS base_price DECIMAL(10,2) DEFAULT 0.00";
if ($conn->query($sql) === TRUE) {
    echo "Column base_price added successfully or already exists.";
} else {
    echo "Error adding column: " . $conn->error;
}
?>
