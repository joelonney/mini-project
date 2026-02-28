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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { background-color: #f8f9fa; font-family: 'Plus Jakarta Sans', sans-serif; }
        .card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05) !important; transition: transform 0.2s; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
        .navbar-custom { background-color: #E53935; box-shadow: 0 4px 15px rgba(229, 57, 53, 0.2); }
        .btn-light { border-radius: 50px; font-weight: 600; padding: 6px 20px; color: #E53935; }
        .btn-light:hover { background-color: #f8f9fa; color: #D32F2F; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom p-3">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="#">
                <i class="fas fa-bus-alt me-2 text-white"></i>SmartBus Admin
            </a>
            <a href="../includes/logout.php" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Logout</a>
        </div>
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