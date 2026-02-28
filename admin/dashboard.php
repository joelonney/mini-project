<?php
include '../includes/db.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ADMIN') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark p-3">
        <a class="navbar-brand" href="#">SmartBus Admin</a>
        <a href="../includes/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </nav>

    <div class="container mt-4">
        <h2>Dashboard</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Manage Buses</h5>
                        <p class="card-text">Add or remove buses from the fleet.</p>
                        <a href="manage_buses.php" class="btn btn-light">Go</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h5 class="card-title">View Bookings</h5>
                        <p class="card-text">See all customer reservations.</p>
                        <a href="view_bookings.php" class="btn btn-light">Go</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Lost & Found</h5>
                        <p class="card-text">Manage reported lost items.</p>
                        <a href="lost_and_found.php" class="btn btn-light">Go</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>