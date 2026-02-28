<?php
include 'includes/db.php';

$name = "Admin User";
$email = "admin@smartbus.com";
$phone = "9999999999";
$password = "admin123"; // This is the plain text password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = "ADMIN";

// Check if admin already exists
$check = $conn->query("SELECT * FROM users WHERE email='$email'");
if ($check->num_rows > 0) {
    echo "<h3>Admin user already exists!</h3>";
    echo "<p>Email: <b>$email</b></p>";
    echo "<p>Password: <b>$password</b> (if you haven't changed it)</p>";
} else {
    $sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "<h3>Admin User Created Successfully!</h3>";
        echo "<p>Email: <b>$email</b></p>";
        echo "<p>Password: <b>$password</b></p>";
        echo "<br><a href='login.php'>Go to Login</a>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
