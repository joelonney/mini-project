<?php
include 'includes/db.php';
// Disable error reporting for production/demo
error_reporting(0);
ini_set('display_errors', 0);

// --- Fetch distinct cities for dropdowns ---
$sources = [];
$destinations = [];

$sourceSql = "SELECT DISTINCT source FROM routes ORDER BY source ASC";
$sourceResult = $conn->query($sourceSql);
while ($row = $sourceResult->fetch_assoc()) {
    $sources[] = $row['source'];
}

$destSql = "SELECT DISTINCT destination FROM routes ORDER BY destination ASC";
$destResult = $conn->query($destSql);
while ($row = $destResult->fetch_assoc()) {
    $destinations[] = $row['destination'];
}

// Check if search parameters exist
$searchFrom = $_GET['from'] ?? '';
$searchTo = $_GET['to'] ?? '';
$searchDate = $_GET['date'] ?? '';

if ($searchFrom && $searchTo) {
    $busData = []; // Initialize to prevent undefined variable error
    // JOIN buses and routes tables
    // Use prepared statements for security
    // Removed b.price as it doesn't exist in the table
    $sql = "SELECT b.bus_id, b.bus_number, b.bus_type, b.base_price, b.departure_time, b.arrival_time,
                   r.source, r.destination, r.distance_km
            FROM buses b
            JOIN routes r ON b.route_id = r.route_id
            WHERE r.source = ? AND r.destination = ?";
            
    // Note: Using exact match now since we have dropdowns
    
    // Use direct query with escaping since analyze_data.php confirmed this works
    // (Avoids potential issues with prepared statements/get_result driver support)
    $safeFrom = $conn->real_escape_string($searchFrom);
    $safeTo = $conn->real_escape_string($searchTo);
    
    $sql = "SELECT b.bus_id, b.bus_number, b.bus_type, b.base_price, b.departure_time, b.arrival_time,
                   r.source, r.destination, r.distance_km
            FROM buses b
            JOIN routes r ON b.route_id = r.route_id
            WHERE r.source = '$safeFrom' AND r.destination = '$safeTo'";
            
    $result = $conn->query($sql);
    
    if (!$result) {
        $debugCount = "ERROR";
        $debugError = $conn->error;
    } else {
        $debugCount = $result->num_rows;
        $debugError = "";
    }

    // DEBUG LOGGING
    $logMsg = "Search: From=$safeFrom, To=$safeTo, QueryStatus=" . ($result ? "OK" : "FAIL") . "\n";
    if(!$result) $logMsg .= "Error: " . $conn->error . "\n";
    // file_put_contents('debug_log.txt', $logMsg, FILE_APPEND); // Optional logging
    
    // List of realistic operators for the demo
    $operators = ['KSRTC Airavat', 'VRL Travels', 'SRS Travels', 'Orange Tours', 'GreenLine', 'Jabbar Travels', 'IntrCity SmartBus'];
    
    while ($row = $result->fetch_assoc()) {
        // Use base_price if available, otherwise fallback to distance-based dummy price
        $price = (!empty($row['base_price']) && $row['base_price'] > 0) ? $row['base_price'] : ($row['distance_km'] * 2); 
        
        // Generate consistent mock data based on ID
        $opIndex = $row['bus_id'] % count($operators);
        $operatorName = $operators[$opIndex];
        
        // Determine sub-type nicely
        $subType = 'Club Class';
        if (strpos(strtoupper($row['bus_type']), 'SLEEPER') !== false) {
            $subType = 'Sleeper';
        } else if (strpos(strtoupper($row['bus_type']), 'NON-AC') !== false) {
            $subType = 'Seater / Non-AC';
        }

        // Tag government buses
        $isGovt = (strpos($operatorName, 'KSRTC') !== false);
        $displayType = $isGovt ? 'Government' : 'Private';

        // Map DB columns to your Frontend JS keys
        $busData[] = [
            'id' => $row['bus_id'],
            'from' => $row['source'],
            'to' => $row['destination'],
            'name' => $operatorName,
            'type' => $displayType,
            'sub' => $subType, 
            'dep' => substr($row['departure_time'], 0, 5),
            'arr' => substr($row['arrival_time'], 0, 5),
            'price' => $price,
            'rate' => 4.5 + (($row['bus_id'] % 5) / 10), // Random rating 4.5 - 5.0
            'cat' => ($row['bus_type'] == 'AC') ? 'comfort' : 'budget',
            'status' => 'ontime',
            'am' => ['ac', 'wifi', 'plug', 'water'] 
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Buses - SmartBus</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #E53935;
            --primary-hover: #D32F2F;
            --primary-bg: #FFEBEE;
            --text-main: #1E293B;
            --text-secondary: #64748B;
            --glass-bg: rgba(255, 255, 255, 0.92);
            --glass-border: rgba(255, 255, 255, 0.6);
            --shadow-soft: 0 10px 40px -10px rgba(0,0,0,0.08);
            --shadow-hover: 0 20px 50px -12px rgba(0,0,0,0.15);
            --radius: 24px;
            --transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); 
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0F172A; /* Dark base for contrast */
            color: var(--text-main);
            margin: 0;
            padding-bottom: 80px; 
            overflow-x: hidden;
        }

        /* --- Background Layers --- */
        .bg-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            z-index: -1; overflow: hidden;
        }
        .bg-image {
            width: 100%; height: 100%; object-fit: cover;
            opacity: 0.4; filter: blur(3px) brightness(0.8);
            transform: scale(1.1);
        }

        /* --- Header --- */
        .top-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 16px 0; position: sticky; top: 0; z-index: 1000;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .brand-area { cursor: pointer; transition: var(--transition); }
        .brand-area:hover { opacity: 0.8; }

        /* --- Main Container --- */
        .main-content {
            max-width: 900px; 
            margin: 0 auto; padding-top: 30px;
            position: relative; z-index: 1;
        }

        /* --- Search Box --- */
        .search-container {
            background: var(--glass-bg); padding: 24px;
            border-radius: var(--radius); box-shadow: var(--shadow-soft);
            margin-bottom: 30px; border: 1px solid var(--glass-border);
            transform: translateY(0); transition: var(--transition);
        }
        .form-input-modern, .form-select-modern {
            width: 100%; border: 2px solid transparent; background: #F8FAFC;
            padding: 16px 20px; border-radius: 16px; font-weight: 600;
            font-size: 1rem; color: var(--text-main); transition: var(--transition);
        }
        .form-input-modern:focus, .form-select-modern:focus {
            background: #fff; border-color: var(--primary);
            box-shadow: 0 0 0 6px rgba(229, 57, 53, 0.05); outline: none;
        }
        .btn-search {
            background: var(--primary); color: white; border: none;
            padding: 16px 32px; border-radius: 16px; font-weight: 700;
            width: 100%; transition: var(--transition);
            box-shadow: 0 4px 12px rgba(229, 57, 53, 0.25);
        }
        .btn-search:hover {
            background: var(--primary-hover); transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(229, 57, 53, 0.35);
        }

        /* --- Popular Routes --- */
        .popular-tags {
            display: flex; gap: 10px; overflow-x: auto;
            padding-bottom: 10px; margin-bottom: 25px; scrollbar-width: none;
        }
        .popular-tags::-webkit-scrollbar { display: none; }
        .tag-pill {
            background: rgba(255,255,255,0.15); backdrop-filter: blur(10px);
            color: white; padding: 10px 20px; border-radius: 50px;
            font-weight: 600; font-size: 0.9rem; white-space: nowrap;
            cursor: pointer; border: 1px solid rgba(255,255,255,0.2);
            transition: var(--transition);
        }
        .tag-pill:hover { background: white; color: var(--primary); transform: translateY(-2px); }

        /* --- Tips Section --- */
        .tips-box {
            background: linear-gradient(135deg, #FFF7ED 0%, #FFFFFF 100%);
            border-left: 4px solid #F97316; padding: 20px;
            border-radius: 16px; margin-bottom: 30px; box-shadow: var(--shadow-soft);
        }
        .tips-box h6 { color: #C2410C; font-weight: 700; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .tips-list li { font-size: 0.9rem; color: #475569; margin-bottom: 8px; display: flex; gap: 10px; }
        .tips-list li i { color: #F97316; margin-top: 3px; }

        /* --- Filter Tabs & Sort Bar --- */
        .controls-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 24px; gap: 15px;
        }
        .filters {
            display: flex; gap: 12px; flex-wrap: wrap;
        }
        .filter-btn {
            background: rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
            color: white; padding: 10px 24px; border-radius: 50px;
            font-weight: 600; cursor: pointer; transition: var(--transition);
        }
        .filter-btn.active, .filter-btn:hover {
            background: var(--primary); border-color: var(--primary);
            box-shadow: 0 4px 15px rgba(229, 57, 53, 0.4);
        }
        .sort-select {
            background: white; border: none; padding: 10px 15px;
            border-radius: 12px; font-weight: 600; color: var(--text-main);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05); cursor: pointer;
        }

        /* --- BUS CARD --- */
        .bus-card {
            background: var(--glass-bg); border-radius: var(--radius);
            padding: 28px; margin-bottom: 20px; box-shadow: var(--shadow-soft);
            border: 1px solid rgba(255,255,255,0.5); transition: var(--transition);
            cursor: pointer; position: relative; display: grid; gap: 20px;
            grid-template-columns: 1fr auto;
            opacity: 1; transform: none; /* Forced Visibility */
        }
        .bus-card.visible { opacity: 1; transform: none; }
        .bus-card:hover {
            transform: translateY(-6px); box-shadow: var(--shadow-hover);
            border-color: rgba(229, 57, 53, 0.3);
        }

        .bus-info { display: grid; grid-template-columns: auto 1fr; gap: 20px; align-items: start; }
        .bus-logo {
            width: 64px; height: 64px; background: #F1F5F9; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: var(--primary);
        }
        .bus-details { display: flex; flex-direction: column; justify-content: center; height: 100%; }
        .bus-name-row { display: flex; align-items: center; gap: 10px; margin-bottom: 6px; flex-wrap: wrap; }
        .bus-name { font-weight: 800; font-size: 1.15rem; color: var(--text-main); }
        .bus-type { font-size: 0.8rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; padding: 4px 10px; border-radius: 8px; }
        .type-govt { background: #E0F2FE; color: #0369A1; }
        .type-priv { background: #FEF3C7; color: #B45309; }
        .bus-sub { font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 12px; }

        .time-display {
            display: flex; align-items: center; gap: 20px; background: #F8FAFC;
            padding: 12px 20px; border-radius: 16px; width: fit-content; margin-bottom: 12px;
        }
        .time-col { text-align: center; }
        .time-val { font-size: 1.2rem; font-weight: 800; color: var(--text-main); display: block; line-height: 1.2; }
        .dur-val { font-size: 0.8rem; color: var(--text-secondary); font-weight: 600; background: #E2E8F0; padding: 2px 8px; border-radius: 6px; }
        .dash-line { height: 2px; width: 40px; background: #CBD5E1; position: relative; }
        .dash-line::after { content: '>'; position: absolute; right: -4px; top: -7px; color: #CBD5E1; font-size: 10px; font-weight: bold; }

        .amenities-row { display: flex; gap: 15px; color: #94A3B8; font-size: 1.1rem; }
        .amenities-row i { transition: 0.2s; }
        .amenities-row i:hover { color: var(--primary); transform: scale(1.1); }

        /* Right Side: Price & Action */
        .bus-action {
            display: flex; flex-direction: column; justify-content: space-between;
            align-items: flex-end; padding-left: 20px;
            border-left: 1px solid #E2E8F0; min-width: 140px;
        }

        .rating-box { font-size: 0.85rem; font-weight: 700; color: #F59E0B; margin-bottom: 10px; display: flex; align-items: center; gap: 4px; }
        
        /* Price Styling */
        .price-container { display: flex; flex-direction: column; align-items: flex-end; margin-bottom: 15px; }
        .discount-tag {
            background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA;
            padding: 2px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;
            animation: pulse 2s infinite;
        }
        .original-price { font-size: 0.9rem; color: #94A3B8; text-decoration: line-through; font-weight: 500; }
        .price-tag { font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1; }

        .btn-seat {
            background: var(--text-main); color: white; padding: 12px 28px;
            border-radius: 50px; font-weight: 700; text-decoration: none;
            transition: var(--transition); display: inline-block; width: 100%;
            text-align: center; border: 2px solid var(--text-main);
        }
        .bus-card:hover .btn-seat {
            background: var(--primary); border-color: var(--primary);
            box-shadow: 0 10px 20px rgba(229, 57, 53, 0.3);
        }

        /* --- Sub-Bus / Variant Styling --- */
        .variant-section {
            grid-column: 1 / -1; /* Span full width */
            border-top: 1px solid #E2E8F0;
            padding-top: 15px;
            margin-top: 10px;
            animation: slideDown 0.3s ease-out;
        }
        .variant-title {
            font-size: 0.8rem; font-weight: 700; text-transform: uppercase;
            color: var(--text-secondary); margin-bottom: 10px; letter-spacing: 1px;
        }
        .variant-row {
            background: #F8FAFC; border-radius: 12px; padding: 12px 16px;
            margin-bottom: 8px; display: flex; justify-content: space-between;
            align-items: center; border: 1px solid transparent; transition: var(--transition);
        }
        .variant-row:hover {
            border-color: var(--primary); background: white;
            transform: translateX(5px);
        }
        .variant-info { display: flex; align-items: center; gap: 15px; }
        .variant-type { font-weight: 700; color: var(--text-main); }
        .variant-price { font-weight: 800; color: var(--text-main); }
        .variant-btn {
            background: white; color: var(--text-main); border: 1px solid #CBD5E1;
            padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;
        }
        .variant-row:hover .variant-btn {
            background: var(--primary); color: white; border-color: var(--primary);
        }

        /* Status Badge */
        .status-badge {
            position: absolute; top: 24px; right: 24px;
            font-size: 0.75rem; font-weight: 700; padding: 6px 14px;
            border-radius: 8px; z-index: 2; opacity: 0.9;
        }
        .st-ontime { background: #DCFCE7; color: #166534; }
        .st-delayed { background: #FEF9C3; color: #854D0E; }
        .st-boarding { background: #DBEAFE; color: #1E40AF; }

        /* --- Responsive Logic (Mobile) --- */
        @media (max-width: 768px) {
            .bus-card { grid-template-columns: 1fr; gap: 20px; padding: 20px; }
            .bus-info { grid-template-columns: auto 1fr; gap: 15px; }
            .bus-action {
                flex-direction: row; justify-content: space-between; align-items: center;
                border-left: none; border-top: 1px solid #E2E8F0;
                padding-left: 0; padding-top: 15px; min-width: 100%;
            }
            .price-container { align-items: flex-start; margin-bottom: 0; }
            .price-tag { font-size: 1.4rem; }
            .btn-seat { padding: 10px 20px; width: auto; font-size: 0.9rem; }
            .time-display { width: 100%; justify-content: space-between; }
            .dash-line { width: 100%; }
            .controls-bar { flex-direction: column; align-items: flex-start; }
            .filters { width: 100%; overflow-x: auto; flex-wrap: nowrap; }
        }

        /* --- Loader --- */
        .loader-wrapper {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.8); backdrop-filter: blur(5px);
            z-index: 2000; display: none; align-items: center; justify-content: center; flex-direction: column;
        }
        .spinner {
            width: 60px; height: 60px; border: 5px solid rgba(229, 57, 53, 0.1);
            border-left-color: var(--primary); border-radius: 50%;
            animation: spin 0.8s linear infinite; margin-bottom: 20px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0% { opacity: 0.8; } 50% { opacity: 1; } 100% { opacity: 0.8; } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

    </style>
</head>
<body>

    <!-- Background -->
    <div class="bg-wrapper">
        <img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?q=80&w=2672&auto=format&fit=crop" class="bg-image" alt="Background">
    </div>

    <!-- Header -->
    <div class="top-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php" class="text-dark text-decoration-none d-flex align-items-center gap-2 fw-bold fs-5">
                    <i class="fas fa-arrow-left"></i> <span>Home</span>
                </a>
                
                <div class="d-flex gap-3 align-items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <span class="d-none d-md-block fw-bold small text-muted">Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></span>
                        <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                            <a href="admin/dashboard.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">Dashboard</a>
                        <?php else: ?>
                            <a href="my_bookings.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">My Bookings</a>
                        <?php endif; ?>
                        <a href="includes/logout.php" class="btn btn-sm btn-danger rounded-pill px-3">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container main-content">
        
        <!-- Search Form -->
        <div class="search-container">
            <form id="searchForm" action="search.php" method="GET">
                <div class="row g-3">
                    <div class="col-md-3 col-6">
                        <label class="small text-muted fw-bold mb-2">FROM</label>
                        <select name="from" id="inputFrom" class="form-select-modern" required>
                            <option value="" disabled selected>Select Source</option>
                            <?php foreach ($sources as $source): ?>
                                <option value="<?php echo htmlspecialchars($source); ?>" <?php if($source == $searchFrom) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($source); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="small text-muted fw-bold mb-2">TO</label>
                        <select name="to" id="inputTo" class="form-select-modern" required>
                            <option value="" disabled selected>Select Destination</option>
                            <?php foreach ($destinations as $dest): ?>
                                <option value="<?php echo htmlspecialchars($dest); ?>" <?php if($dest == $searchTo) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($dest); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 col-6">
                        <label class="small text-muted fw-bold mb-2">DATE</label>
                        <input type="date" name="date" id="inputDate" class="form-input-modern" value="<?php echo htmlspecialchars($searchDate); ?>" required>
                    </div>
                    <div class="col-md-2 col-6 d-flex align-items-end">
                        <button type="submit" class="btn-search">
                            <i class="fas fa-search me-2"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Popular Routes -->
        <div class="popular-tags">
            <div class="small text-white fw-bold me-2 align-self-center opacity-75">POPULAR:</div>
            <div class="tag-pill" onclick="quickRoute('Bangalore', 'Kochi')">Blr ➔ Kochi</div>
            <div class="tag-pill" onclick="quickRoute('Bangalore', 'Chennai')">Blr ➔ Cbe</div>
            <div class="tag-pill" onclick="quickRoute('Mumbai', 'Pune')">Mum ➔ Pune</div>
            <div class="tag-pill" onclick="quickRoute('Delhi', 'Jaipur')">Del ➔ Jai</div>
        </div>

        <!-- Pro Tips -->
        <div id="tipsSection" class="tips-box" style="display: none;">
            <h6><i class="fas fa-lightbulb"></i> Smart Tips</h6>
            <ul class="list-unstyled tips-list" id="tipsList"></ul>
        </div>

        <!-- Controls (Filters & Sort) -->
        <div id="controlsSection" class="controls-bar" style="display: none;">
            <div class="filters">
                <div class="filter-btn active" onclick="filterBuses('all')">All</div>
                <div class="filter-btn" onclick="filterBuses('comfort')">Premium</div>
                <div class="filter-btn" onclick="filterBuses('budget')">Budget</div>
                <div class="filter-btn" onclick="filterBuses('ac')">A/C Only</div>
            </div>
            <select id="sortSelect" class="sort-select" onchange="handleSort()">
                <option value="dep">Sort: Departure</option>
                <option value="priceLow">Sort: Price (Low)</option>
                <option value="rating">Sort: Rating</option>
            </select>
        </div>

        <!-- Bus List -->
        <div id="resultsContainer">
            <!-- Cards Injected Here -->
        </div>

    </div>

    <!-- Full Screen Loader -->
    <div id="loader" class="loader-wrapper">
        <div class="spinner"></div>
        <div class="fw-bold text-muted">Finding the best routes...</div>
    </div>

    <script>
        // --- DATA ---
        // Ensure strictly valid JSON using constant check
        const buses = <?php echo json_encode($busData ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        console.log("JS Loaded Bus Data:", buses);
        if(buses.length > 0) { console.log("SUCCESS: Buses are present in JS."); }
        else { console.error("FAILURE: Buses array is empty in JS."); }
        
        const icons = {
            'wifi': '<i class="fas fa-wifi"></i>', 'ac': '<i class="fas fa-snowflake"></i>',
            'plug': '<i class="fas fa-plug"></i>', 'bed': '<i class="fas fa-bed"></i>',
            'water': '<i class="fas fa-tint"></i>', 'cctv': '<i class="fas fa-video"></i>'
        };

        let currentList = [];
        let sortedList = [];

        document.addEventListener('DOMContentLoaded', () => {
            try {
                // If date was not provided in server-side render, set to today
                const dateHeader = document.getElementById('inputDate');
                if(dateHeader && !dateHeader.value) {
                   dateHeader.valueAsDate = new Date();
                }
                
                const params = new URLSearchParams(window.location.search);
                const from = params.get('from');
                const to = params.get('to');
                // Only search if params exist and haven't run yet
                if (from && to) {
                    performSearch(from, to);
                }
            } catch (e) {
                console.error("Init error:", e);
                document.getElementById('loader').style.display = 'none';
            }
        });

        // --- HELPER: Calculate Dynamic Duration ---
        function getDuration(dep, arr) {
            if(!dep || !arr) return '0h 0m';
            try {
                let [dh, dm] = dep.split(':').map(Number);
                let [ah, am] = arr.split(':').map(Number);
                let depMins = dh * 60 + dm;
                let arrMins = ah * 60 + am;
                if (arrMins < depMins) arrMins += 24 * 60;
                let diff = arrMins - depMins;
                let hours = Math.floor(diff / 60);
                let mins = diff % 60;
                return `${hours}h ${mins}m`;
            } catch(e) { return '0h 0m'; }
        }

        // --- HELPER: Apply Random Discounts & Tweak Times ---
        function processBuses(list) {
            if(!list) return [];
            try {
                let processed = JSON.parse(JSON.stringify(list));

                // 1. Tweak Times (-5 to +5 mins)
                processed.forEach(b => {
                    const addMins = (time, mins) => {
                        if(!time) return '00:00';
                        let [h, m] = time.split(':').map(Number);
                        let total = h * 60 + m + mins;
                        let newH = Math.floor(total / 60) % 24;
                        let newM = total % 60;
                        return `${String(newH).padStart(2,'0')}:${String(newM).padStart(2,'0')}`;
                    };
                    let drift = Math.floor(Math.random() * 11) - 5;
                    if(drift !== 0) {
                        b.dep = addMins(b.dep, drift);
                        b.arr = addMins(b.arr, drift);
                    }
                });

                // 2. Random Discounts (Max 2 per list)
                let indices = [...Array(processed.length).keys()];
                indices.sort(() => Math.random() - 0.5);
                let appliedCount = 0;
                indices.forEach(i => {
                    if(appliedCount >= 2) return;
                    if(Math.random() > 0.4) {
                        let isPercent = Math.random() > 0.5;
                        let val = isPercent 
                            ? (Math.floor(Math.random() * 3) + 1) * 5 
                            : Math.floor(Math.random() * 100) + 50;
                        processed[i].discount = {
                            type: isPercent ? 'percent' : 'flat',
                            val: val, text: isPercent ? `${val}% OFF` : `₹${val} OFF`
                        };
                        appliedCount++;
                    }
                });
                return processed;
            } catch(e) {
                console.error("Process error:", e);
                return list;
            }
        }

        // --- HELPER: Group Buses (Main + Sub Variants) ---
        function groupBuses(list) {
            const groups = {};
            list.forEach(bus => {
                // Default handling for missing fields
                 const safeName = bus.name || 'Bus';
                 const safeDep = bus.dep || '00:00';
                 
                // Create a unique key based on From, To, Name, and Departure Time
                const key = `${bus.from}-${bus.to}-${safeName}-${safeDep}`;
                
                if (!groups[key]) {
                    groups[key] = { main: bus, variants: [] };
                } else {
                    if (bus.price < groups[key].main.price) {
                        let temp = groups[key].main;
                        groups[key].main = bus;
                        groups[key].variants.push(temp);
                    } else {
                        groups[key].variants.push(bus);
                    }
                }
            });
            return Object.values(groups);
        }

        function handleSearch(e) {
            e.preventDefault(); // This is mostly for programmatic calls, the form usually submits naturally
            const from = document.getElementById('inputFrom').value;
            const to = document.getElementById('inputTo').value;
            performSearch(from, to);
        }

        function quickRoute(from, to) {
            document.getElementById('inputFrom').value = from;
            document.getElementById('inputTo').value = to;
            // Submit form to trigger reload with params
            document.getElementById('searchForm').submit();
        }

        // --- INITIALIZATION ---
        // If data exists, render it immediately (PHP has already filtered it)
        if(Array.isArray(buses) && buses.length > 0) {
            renderList(buses);
        }

        function performSearch(from, to) {
            const loader = document.getElementById('loader');
            loader.style.display = 'flex';
            document.getElementById('pageTitle').innerText = `${from} to ${to}`;
            
            setTimeout(() => {
                try {
                    // Since PHP already filtered the main list by From/To, 
                    // we can mostly trust 'buses'. 
                    // However, if we want to do client-side filtering (e.g. typing in new boxes), 
                    // we can keep a loose filter.
                    
                    const fromKey = from.toLowerCase().trim();
                    const toKey = to.toLowerCase().trim();
                    
                    let filtered = buses;
                    if(fromKey && toKey) {
                         filtered = buses.filter(b => 
                            (b.from && b.from.toLowerCase().includes(fromKey)) && 
                            (b.to && b.to.toLowerCase().includes(toKey))
                        );
                    }

                    // Apply Discounts & Tweaks
                    currentList = processBuses(filtered);
                    
                    // Sort Default (Departure)
                    currentList.sort((a, b) => (a.dep || '').localeCompare(b.dep || ''));
                    sortedList = [...currentList]; 

                    renderList(sortedList);
                    showTips(fromKey, toKey);
                } catch (e) {
                    console.error("Search Error:", e);
                    document.getElementById('resultsContainer').innerHTML = 
                        `<div class="text-center text-danger mt-5"><h5>Something went wrong loading buses.</h5></div>`;
                } finally {
                    loader.style.display = 'none';
                    document.getElementById('controlsSection').style.display = 'flex';
                }
            }, 500);
        }

        function handleSort() {
            try {
                const val = document.getElementById('sortSelect').value;
                if(val === 'dep') {
                    currentList.sort((a, b) => (a.dep || '').localeCompare(b.dep || ''));
                } else if (val === 'priceLow') {
                    currentList.sort((a, b) => {
                        let priceA = a.discount ? (a.discount.type === 'percent' ? a.price*(1-a.discount.val/100) : a.price-a.discount.val) : a.price;
                        let priceB = b.discount ? (b.discount.type === 'percent' ? b.price*(1-b.discount.val/100) : b.price-b.discount.val) : b.price;
                        return priceA - priceB;
                    });
                } else if (val === 'rating') {
                    currentList.sort((a, b) => b.rate - a.rate);
                }
                renderList(currentList);
            } catch(e) { console.error("Sort error:", e); }
        }

        // --- FULL RENDER LIST FUNCTION (RESTORED & FAIL-SAFE) ---
        function renderList(list) {
            const container = document.getElementById('resultsContainer');
            container.innerHTML = '';
            
            if (!list || list.length === 0) {
                container.innerHTML = `
                    <div class="text-center mt-5">
                        <h4 class="text-white mb-3">No available buses found.</h4>
                        <p class="text-white-50">This might be because the database is empty.</p>
                        <a href="seed_data_web.php" class="btn btn-warning fw-bold pulse-animation">
                            <i class="fas fa-database me-2"></i> Click here to Generate Sample Data
                        </a>
                    </div>
                `;
                return;
            }

            // 1. Group the buses
            try {
                const groupedData = groupBuses(list);

                groupedData.forEach((group, i) => {
                    const b = group.main;
                    const typeClass = b.type === 'Government' ? 'type-govt' : 'type-priv';
                    const statusClass = b.status === 'ontime' ? 'st-ontime' : (b.status === 'boarding' ? 'st-boarding' : 'st-delayed');
                    const statusText = b.status === 'ontime' ? 'On Time' : (b.status === 'boarding' ? 'Boarding' : 'Delayed 10m');
                    const duration = getDuration(b.dep, b.arr);
                    
                    // Fail-safe Masking
                    try {
                        let priceHtml = '';
                        let displayPrice = b.price || 0;
                        if (b.discount && typeof b.discount === 'object') {
                           try {
                                if(b.discount.type === 'percent') displayPrice = Math.round(b.price * (1 - (b.discount.val/100)));
                                else displayPrice = b.price - b.discount.val;
                                priceHtml = `<div class="discount-tag">${b.discount.text}</div><span class="original-price">₹${b.price}</span>`;
                           } catch(err) { console.warn("Discount Calc Error:", err); }
                        }

                        const safeAm = Array.isArray(b.am) ? b.am : [];
                        const amIcons = safeAm.map(x => icons[x] || '').join(' ');

                        const card = document.createElement('div');
                        card.className = 'bus-card visible'; 
                        
                        const safeName = (b.name || 'Unknown Bus').replace(/'/g, "\\'");
                        const safeSub = (b.sub || '').replace(/'/g, "\\'");
                        const safeDep = b.dep || '00:00';
                        const safeArr = b.arr || '00:00';

                        // Card HTML
                        let html = `
                            <div class="status-badge ${statusClass}">${statusText}</div>
                            
                            <div class="bus-info">
                                <div class="bus-logo">
                                    <i class="fas fa-${(b.sub || '').includes('Sleeper') ? 'bed' : 'bus-alt'}"></i>
                                </div>
                                <div class="bus-details">
                                    <div class="bus-name-row">
                                        <span class="bus-name">${b.name || 'Bus'}</span>
                                        <span class="bus-type ${typeClass}">${b.type || 'Private'}</span>
                                    </div>
                                    <div class="bus-sub">${b.sub || ''}</div>
                                    
                                    <div class="time-display">
                                        <div class="time-col"><span class="time-val">${safeDep}</span></div>
                                        <div class="dash-line"></div>
                                        <div class="time-col"><span class="dur-val">${duration}</span></div>
                                        <div class="dash-line"></div>
                                        <div class="time-col"><span class="time-val">${safeArr}</span></div>
                                    </div>

                                    <div class="amenities-row">
                                        ${amIcons}
                                    </div>
                                </div>
                            </div>

                            <div class="bus-action">
                                <div class="rating-box"><i class="fas fa-star"></i> ${b.rate || '4.5'}</div>
                                <div class="price-container">
                                    ${priceHtml}
                                    <div class="price-tag">₹${displayPrice}</div>
                                </div>
                                <a href="#" onclick="selectBus(${b.id}, '${safeName}', ${displayPrice}, '${safeSub}'); return false;" class="btn-seat">View Seats <i class="fas fa-arrow-right ms-1 small"></i></a>
                            </div>
                        `;

                        // Add Variants safely
                        if (group.variants && group.variants.length > 0) {
                             html += `<div class="variant-section"><div class="variant-title">Other variants available</div>`;
                             group.variants.forEach(v => {
                                 // Minimal variant render
                                 let vPrice = v.price || 0;
                                 html += `
                                    <div class="variant-row">
                                        <div class="variant-info"><div class="variant-type">${v.sub || 'Seat'}</div></div>
                                        <div class="variant-price">₹${vPrice}</div>
                                    </div>`;
                             });
                             html += `</div>`;
                        }

                        card.innerHTML = html;
                        card.onclick = (e) => {
                            if(!e.target.closest('.variant-section') && !e.target.closest('.btn-seat')) {
                                selectBus(b.id, b.name, displayPrice, b.sub); 
                            }
                        };
                        container.appendChild(card);
                    } catch(innerE) {
                        console.error("Error rendering individual card:", innerE, b);
                    }
                });
            } catch (e) {
                console.error("Render Error:", e);
                container.innerHTML = `<div class="text-center text-danger mt-5"><h5>Error rendering results.</h5></div>`;
            }
        }
        





        function filterBuses(cat) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            
            let filtered = [];
            if(cat === 'all') filtered = [...currentList]; // Clone current state
            else if(cat === 'comfort') filtered = currentList.filter(b => b.cat === 'comfort');
            else if(cat === 'budget') filtered = currentList.filter(b => b.cat === 'budget');
            else if(cat === 'ac') filtered = currentList.filter(b => b.am.includes('ac'));
            
            renderList(filtered);
        }

        function showTips(from, to) {
            const tipsBox = document.getElementById('tipsSection');
            const tipsList = document.getElementById('tipsList');
            tipsBox.style.display = 'block';
            
            let tips = [];
            if(from.includes('bangalore') && to.includes('kochi')) {
                tips = ["<b>Reliability:</b> KSRTC Swift is safest on Ghats.", "<b>Comfort:</b> Try Kallada G4 Sleepers."];
            } else if (from.includes('mumbai') && to.includes('pune')) {
                tips = ["<b>Best:</b> MSRTC Shivneri runs every 15 mins.", "<b>Tip:</b> Purple Metrolink is great for daily passes."];
            } else if (from.includes('delhi') && to.includes('jaipur')) {
                tips = ["<b>Eco:</b> NueGo Electric buses are silent & smooth.", "<b>Tip:</b> RSRTC Goldline is highly punctual."];
            } else {
                tips = ["<b>General:</b> Govt buses rarely cancel.", "<b>Comfort:</b> Private buses offer live tracking."];
            }
            tipsList.innerHTML = tips.map(t => `<li>${t}</li>`).join('');
        }

        function selectBus(id, name, price, sub) {
            const from = document.getElementById('inputFrom').value;
            const to = document.getElementById('inputTo').value;
            const date = document.getElementById('inputDate').value; // Get Travel Date
            const isSleeper = sub.toLowerCase().includes('sleeper');
            
            // Updated URL to include bus_id and date
            window.location.href = `seats.php?bus_id=${id}&date=${date}&name=${encodeURIComponent(name)}&price=${price}&isSleeper=${isSleeper}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}`;
        }
    </script>
</body>
</html>