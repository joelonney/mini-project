<?php
include 'includes/db.php';

// 1. Get Data from URL
 $bus_id = isset($_GET['bus_id']) ? intval($_GET['bus_id']) : 0;
 $travel_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// 2. Fetch Bus Details (to verify bus exists and get type)
 $busQuery = $conn->prepare("SELECT * FROM buses WHERE bus_id = ?");
 $busQuery->bind_param("i", $bus_id);
 $busQuery->execute();
 $busResult = $busQuery->get_result();
 $busInfo = $busResult->fetch_assoc();

if (!$busInfo) {
    die("Bus not found.");
}

// 3. Fetch ALL seats for this bus
 $seatData = []; 
// We fetch seat_id and seat_number
 $seatsSql = "SELECT seat_id, seat_number FROM seats WHERE bus_id = ?";
 $seatsStmt = $conn->prepare($seatsSql);
 $seatsStmt->bind_param("i", $bus_id);
 $seatsStmt->execute();
 $seatsRes = $seatsStmt->get_result();

// Get Booked Seats for this specific date
 $bookedSeatsSql = "SELECT seat_id FROM bookings WHERE bus_id = ? AND travel_date = ? AND status = 'BOOKED'";
 $bookedStmt = $conn->prepare($bookedSeatsSql);
 $bookedStmt->bind_param("is", $bus_id, $travel_date);
 $bookedStmt->execute();
 $bookedRes = $bookedStmt->get_result();

 $booked_ids = [];
while($row = $bookedRes->fetch_assoc()){
    $booked_ids[] = $row['seat_id'];
}

// 4. Build Data Array for JavaScript
while($row = $seatsRes->fetch_assoc()){
    $status = 'available';
    $price = $_GET['price']; // Base price from search

    // Check if booked
    if(in_array($row['seat_id'], $booked_ids)){
        $status = 'sold';
    }
    
    $seatData[] = [
        'id' => $row['seat_id'],
        'number' => $row['seat_number'],
        'status' => $status,
        'price' => $price
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats - SmartBus</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SunCalc Library -->
    <script src="https://unpkg.com/suncalc"></script>

    <style>
        :root {
            --primary: #E53935;
            --primary-hover: #c62828;
            --bg-color: #F0F2F5;
            --text-dark: #1B1B1E;
            --seat-base: #e9ecef;
            --seat-selected: var(--primary);
            --seat-shaded: #198754;
            --premium-border: #ffd700;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-color);
            color: var(--text-dark);
            min-height: 100vh;
            padding-bottom: 120px; /* Space for FAB */
        }

        /* --- Header --- */
        .top-bar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .back-btn {
            color: var(--text-dark);
            font-weight: 600;
            text-decoration: none;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .back-btn:hover { color: var(--primary); transform: translateX(-5px); }

        /* --- Controls Toolbar --- */
        .controls-toolbar {
            background: white;
            margin-top: 25px;
            border-radius: 16px;
            padding: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .tool-btn {
            flex: 1;
            border: 1px solid #eee;
            background: white;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .tool-btn:hover { background: #f8f9fa; border-color: var(--primary); color: var(--primary); }
        .tool-btn.active { background: var(--text-dark); color: white; border-color: var(--text-dark); box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        
        .btn-reset { color: #dc3545; border-color: #ffebeb; }
        .btn-reset:hover { background: #dc3545; color: white; }

        /* --- Sun Analyzer Card --- */
        .analyzer-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: 20px;
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
        }

        .input-modern {
            border: 1px solid #eee;
            padding: 12px 15px;
            border-radius: 12px;
            font-weight: 500;
            color: var(--text-dark);
            width: 100%;
            transition: 0.3s;
        }
        .input-modern:focus {
            border-color: #FF9800;
            box-shadow: 0 0 0 4px rgba(255,152,0,0.1);
        }

        .btn-sun-check {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(245, 124, 0, 0.25);
        }
        .btn-sun-check:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(245, 124, 0, 0.35); }

        .sun-action-btn {
            background: #28a745; 
            color: white;
            border: none;
            width: 100%;
            margin-top: 15px;
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            display: none; 
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        @keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        .sun-status-text {
            font-size: 0.9rem;
            margin-top: 20px;
            display: none;
            padding: 12px;
            border-radius: 12px;
            background: #f0fff4;
            color: #198754;
            border: 1px solid #b7eb8f;
            animation: slideDown 0.4s ease;
            font-weight: 500;
        }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

        /* --- Bus Layout --- */
        .bus-container {
            background: #ffffff;
            border-radius: 40px;
            padding: 40px 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #e9ecef; 
            position: relative;
            margin-top: 30px;
        }

        .driver-area {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
            border-bottom: 2px dashed #ccc;
            padding-bottom: 15px;
        }
        .driver-icon {
            font-size: 1.8rem;
            color: #555;
            padding: 12px;
            background: #f1f3f5;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .seat-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 50px 1fr 1fr; 
            gap: 18px;
            justify-items: center;
            justify-content: center;
        }

        .seat-grid.sleeper-layout {
            grid-template-columns: 1fr 25px 1fr;
            gap: 12px;
        }

        .seat {
            height: 48px;
            background: linear-gradient(145deg, #f8f9fa, #e9ecef);
            border-radius: 10px 10px 14px 14px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 600;
            color: #888;
            border: 2px solid transparent;
            width: 52px;
            opacity: 0;
            /* For entrance animation */
            transform: translateY(15px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .seat.animate-in {
            animation: seatPop 0.5s forwards;
        }

        @keyframes seatPop {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* --- IMPROVED SEAT VISUALS --- */
        .seat::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 70%;
            height: 12px;
            background: inherit;
            border-radius: 6px 6px 0 0;
            /* Headrest */
            border: 1px solid rgba(0, 0, 0, 0.1);
            z-index: -1;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
        }

        .seat.sleeper {
            width: 100%;
            height: 38px;
            border-radius: 6px;
            background: linear-gradient(to right, #f8f9fa, #e9ecef);
        }

        .seat.sleeper.upper {
            margin-bottom: 8px;
        }

        .seat.sleeper::before {
            /* PILLOW for sleeper */
            content: '';
            position: absolute;
            top: 50%;
            left: 5px;
            /* Head at left */
            transform: translateY(-50%);
            width: 8px;
            height: 70%;
            background: #cbd3da;
            border-radius: 4px;
            border: none;
            box-shadow: inset 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .seat.sleeper.upper::before {
            left: auto;
            right: 5px;
            /* Head at right for upper? Just variation */
        }

        .seat.premium {
            border-color: var(--premium-border);
            background: linear-gradient(145deg, #fff8e1, #ffecb3);
        }

        /* Premium Headrest */
        .seat.premium::before {
            background: linear-gradient(145deg, #fff8e1, #ffecb3);
            border-color: #ffd700;
        }

        .seat.premium::after {
            /* Crown icon for premium */
            content: '\f521';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            top: -14px;
            right: -8px;
            font-size: 0.6rem;
            color: #f57c00;
            transform: rotate(15deg);
        }

        .seat:hover:not(.sold):not(.sun-hot):not(.sun-safe) {
            background: #e2e6ea;
            transform: scale(1.1);
            color: var(--text-dark);
            z-index: 100 !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .seat.selected {
            background: var(--seat-selected);
            color: white;
            box-shadow: 0 4px 12px rgba(229, 57, 53, 0.4);
            transform: scale(1.05);
            z-index: 10;
            border-color: white;
        }

        /* --- SOLD SEATS: TEXTURE --- */
        .seat.sold {
            background: repeating-linear-gradient(45deg,
                    #2b2b2b,
                    #2b2b2b 10px,
                    #222 10px,
                    #222 20px);
            color: transparent;
            cursor: not-allowed;
            pointer-events: none;
            border: 1px solid #444;
            opacity: 0.7;
        }

        .seat.sold::before {
            display: none;
        }

        /* No headrest for sold */
        /* Add Lock Icon for sold seats */
        .seat.sold::after {
            content: '\f023';
            /* FontAwesome Lock */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            font-size: 1rem;
            color: #444;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Amenity Icons */
        .amenity-icon {
            position: absolute;
            bottom: 3px;
            right: 3px;
            font-size: 0.65rem;
            color: #999;
            pointer-events: none;
        }

        /* --- SUN SAFE: BRIGHT GREEN GLOW --- */
        .seat.sun-safe {
            background: #2ecc71 !important;
            /* Brighter, more vibrant green */
            color: #fff !important;
            border: 2px solid #27ae60;
            box-shadow: 0 0 15px rgba(46, 204, 113, 0.6), inset 0 0 10px rgba(255, 255, 255, 0.2);
            animation: pulse-green 2s infinite;
            z-index: 50;
            cursor: pointer !important;
            /* Force clickable */
        }

        .seat.sun-safe::after {
            content: 'Cool';
            /* Changed from Best to Cool */
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: #27ae60;
            color: #fff;
            font-size: 0.5rem;
            font-weight: 800;
            padding: 2px 6px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(46, 204, 113, 0.4);
            white-space: nowrap;
        }

        @keyframes pulse-green {
            0% {
                box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(46, 204, 113, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(46, 204, 113, 0);
            }
        }

        /* --- SUN HOT: WARM TINT --- */
        .seat.sun-hot {
            opacity: 0.6;
            filter: grayscale(0.5) sepia(0.3) hue-rotate(-30deg);
            /* Warm/Orange tint */
            border: 1px solid #e74c3c;
            background: #fadbd8;
        }

        .scan-line {
            position: absolute; top: 0; left: 0; width: 100%; height: 5px;
            background: linear-gradient(to right, transparent, #FF9800, transparent);
            z-index: 5; display: none; box-shadow: 0 0 20px #FF9800;
            animation: scanMove 2s ease-in-out forwards;
        }
        @keyframes scanMove {
            0% { top: 10%; opacity: 0; }
            20% { opacity: 1; }
            80% { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }

        .summary-fab {
            position: fixed; bottom: 25px; left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #1a1a1a; color: white;
            padding: 18px 35px; border-radius: 50px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
            display: flex; align-items: center; justify-content: space-between;
            gap: 25px; min-width: 320px; z-index: 2000;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .summary-fab.visible { transform: translateX(-50%) translateY(0); }
    </style>
</head>
<body>

    <div class="top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h5 class="fw-bold m-0 text-truncate" style="max-width: 200px;" id="busTitle">Select Seats</h5>
            <div style="width: 50px;"></div>
        </div>
    </div>

    <div class="container">
        <div class="controls-toolbar">
            <button class="tool-btn btn-reset" onclick="resetSelection()">
                <i class="fas fa-undo me-1"></i> Reset
            </button>
            <button class="tool-btn active" onclick="filterView('all')">
                <i class="fas fa-th me-1"></i> All
            </button>
            <button class="tool-btn" onclick="filterView('window')">
                <i class="far fa-square me-1"></i> Window
            </button>
            <button class="tool-btn" onclick="filterView('aisle')">
                <i class="fas fa-grip-lines-vertical me-1"></i> Aisle
            </button>
        </div>

        <div class="analyzer-card">
            <div class="row align-items-end">
                <div class="col-md-5 mb-3 mb-md-0">
                    <label class="form-label small fw-bold text-muted">JOURNEY START TIME</label>
                    <input type="datetime-local" id="tripTime" class="input-modern">
                </div>
                <div class="col-md-7">
                    <button class="btn-sun-check" onclick="analyzeSunlight()">
                        <i class="fas fa-sun"></i> Analyze Sun Route
                    </button>
                    <button id="autoSelectBtn" class="sun-action-btn" onclick="autoSelectBest()">
                        <i class="fas fa-magic me-2"></i> Auto-Select Best Seats
                    </button>
                </div>
            </div>
            <div id="sunStatus" class="sun-status-text"></div>
        </div>

        <div class="bus-container">
            <div class="scan-line" id="scanLine"></div>
            <div class="driver-area">
                <div class="driver-icon"><i class="fas fa-steering-wheel"></i></div>
            </div>

            <div class="seat-grid mb-2" id="gridLabels">
                <div class="aisle-label small text-muted">Win</div>
                <div class="aisle-label small text-muted">Ais</div>
                <div></div> 
                <div class="aisle-label small text-muted">Ais</div>
                <div class="aisle-label small text-muted">Win</div>
            </div>

            <div id="seatContainer" class="seat-grid">
                <!-- Injected via JS -->
            </div>
        </div>
        
        <div class="text-center mt-4 mb-5">
            <small class="text-muted fw-bold">
                <span class="me-3"><i class="fas fa-square text-secondary"></i> Standard</span>
                <span class="me-3"><i class="fas fa-square border-warning" style="border: 2px solid gold; display:inline-block; width:12px; height:12px;"></i> Premium</span>
                <span class="me-3"><i class="fas fa-square text-success"></i> Sun Safe</span>
                <span class="me-3"><i class="fas fa-square text-dark" style="background:#000; border-radius:2px; width:12px; height:12px; display:inline-block;"></i> Sold</span>
            </small>
        </div>
    </div>

    <div id="summaryBar" class="summary-fab">
        <div>
            <div class="small text-white-50">Total Price</div>
            <div class="fw-bold fs-5" id="totalPrice">₹0</div>
        </div>
        <div>
            <div class="small text-white-50">Seats: <span id="selectedSeatsText">None</span></div>
            <button class="btn btn-light btn-sm fw-bold rounded-pill px-3" onclick="goToPayment()">
                Pay
            </button>
        </div>
    </div>

    <script>
        // --- 1. CONFIG & DATA ---
        const cityCoords = {
            'Bangalore': { lat: 12.9716, lon: 77.5946 },
            'Kochi': { lat: 9.9312, lon: 76.2673 },
            'Chennai': { lat: 13.0827, lon: 80.2707 },
            'Mumbai': { lat: 19.0760, lon: 72.8777 },
            'Delhi': { lat: 28.7041, lon: 77.1025 },
            'Goa': { lat: 15.2993, lon: 74.1240 },
            'Jaipur': { lat: 26.9124, lon: 75.7873 },
            'Hyderabad': { lat: 17.3850, lon: 78.4867 },
            'Pune': { lat: 18.5204, lon: 73.8567 },
            'Trivandrum': { lat: 8.5241, lon: 76.9366 },
            'Agra': { lat: 27.1751, lon: 78.0421 },
            'Vijayawada': { lat: 16.5062, lon: 80.6480 },
            'Mysore': { lat: 12.2958, lon: 76.6394 }
        };

        let selectedSeats = [];
        let basePrice = 0;
        let busType = ''; 

        document.addEventListener("DOMContentLoaded", () => {
            const params = new URLSearchParams(window.location.search);
            basePrice = parseInt(params.get('price')) || 0;
            busType = params.get('isSleeper') === 'true' ? 'sleeper' : 'seater';
            
            document.getElementById('busTitle').innerText = params.get('name') || "Select Seats";
            
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('tripTime').value = now.toISOString().slice(0,16);

            generateSeatLayout(busType);
        });

        // --- 2. GENERATE SEATS (UPDATED) ---
        function generateSeatLayout(type) {
            const container = document.getElementById('seatContainer');
            const labels = document.getElementById('gridLabels');
            container.innerHTML = '';
            
            // PHP Data Injection
            const serverSeatData = <?php echo json_encode($seatData); ?>;
            
            // Group data into rows based on your naming convention (L, M, A, R)
            // This logic maps DB data to the visual grid
            
            if (type === 'sleeper') {
                container.classList.add('sleeper-layout');
                labels.innerHTML = `<div class="aisle-label">L (Window)</div><div></div><div class="aisle-label">R (Window)</div>`;
                
                // Create map for easy lookup
                let seatMap = {};
                serverSeatData.forEach(s => seatMap[s.number] = s);

                for(let i=1; i<=10; i++) {
                    // Upper Sleeper
                    let uKey = 'U' + i;
                    let uData = seatMap[uKey] || {status: 'available', price: basePrice};
                    let isSold = (uData.status === 'sold');
                    createSeat(container, uKey, isSold, 'seat sleeper upper', uKey, uData.price);

                    // Lower Sleeper
                    let lKey = 'L' + i;
                    let lData = seatMap[lKey] || {status: 'available', price: basePrice};
                    isSold = (lData.status === 'sold');
                    createSeat(container, lKey, isSold, 'seat sleeper', lKey, lData.price);
                }
            } else {
                container.classList.remove('sleeper-layout');
                labels.innerHTML = `<div class="aisle-label">Win</div><div class="aisle-label">Ais</div><div></div><div class="aisle-label">Ais</div><div class="aisle-label">Win</div>`;

                let seatMap = {};
                serverSeatData.forEach(s => seatMap[s.number] = s);

                for(let i=1; i<=10; i++) {
                    // Seater Layout: L, M, A, R
                    ['L', 'M', 'A', 'R'].forEach(pos => {
                        let key = pos + i;
                        // Fallback for data mismatch
                        let data = seatMap[key] || {status: 'available', price: basePrice}; 
                        let isSold = (data.status === 'sold');
                        let isPremium = (i <= 2); // First 2 rows premium logic
                        
                        createSeat(container, key, isSold, 'seat', key, data.price, isPremium);
                        
                        if(pos === 'M') {
                            const gap = document.createElement('div'); container.appendChild(gap);
                        }
                    });
                }
            }
        }

        function createSeat(container, id, isSold, classes, label, price, isPremium = false) {
            const div = document.createElement('div');
            div.className = classes;
            if(isPremium) div.classList.add('premium');
            div.id = id;
            // If sold, don't show text, rely on CSS lock icon
            if(isSold) {
                div.innerText = ''; 
            } else {
                div.innerText = label;
            }
            div.dataset.price = price;
            
            if(!isSold && Math.random() > 0.7) {
                const icon = document.createElement('i');
                icon.className = `amenity-icon fas ${Math.random() > 0.5 ? 'fa-bolt' : 'fa-wifi'}`;
                div.appendChild(icon);
            }

            setTimeout(() => div.classList.add('animate-in'), Math.random() * 500);

            if(isSold) {
                div.classList.add('sold');
                div.onclick = null; 
            } else {
                div.onclick = function() { selectSeat(div, id); };
            }
            container.appendChild(div);
        }

        // --- 3. SELECTION & INTERACTION ---
        function selectSeat(element, seatId) {
            if (element.classList.contains('sold') || element.classList.contains('sun-hot')) return;
            if (element.classList.contains('selected')) {
                element.classList.remove('selected');
                selectedSeats = selectedSeats.filter(s => s !== seatId);
            } else {
                element.classList.add('selected');
                selectedSeats.push(seatId);
            }
            updateSummary();
        }

        function updateSummary() {
            const bar = document.getElementById('summaryBar');
            const count = selectedSeats.length;
            
            let total = 0;
            selectedSeats.forEach(id => {
                const el = document.getElementById(id);
                if(el) total += parseInt(el.dataset.price);
            });

            document.getElementById('selectedSeatsText').innerText = selectedSeats.join(', ') || "None";
            document.getElementById('totalPrice').innerText = '₹' + total.toLocaleString('en-IN');
            if (count > 0) bar.classList.add('visible');
            else bar.classList.remove('visible');
        }

        function resetSelection() {
            selectedSeats.forEach(id => {
                const el = document.getElementById(id);
                if(el) el.classList.remove('selected');
            });
            selectedSeats = [];
            updateSummary();
        }

        function filterView(type) {
            document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');
            const allSeats = document.querySelectorAll('.seat');
            allSeats.forEach(seat => {
                const id = seat.id;
                let show = true;
                if(type === 'window' && !(id.startsWith('L') || id.startsWith('R'))) show = false;
                if(type === 'aisle' && (id.startsWith('L') || id.startsWith('R'))) show = false;
                seat.style.display = show ? 'flex' : 'none';
            });
        }

        // --- 4. SMART SUNLIGHT ---
        function analyzeSunlight() {
            const timeInput = document.getElementById('tripTime').value;
            const params = new URLSearchParams(window.location.search);
            const fromCity = params.get('from');
            
            if(!timeInput) return alert("Please select a journey time.");
            if(!fromCity) return; 

            const startTime = new Date(timeInput);
            const coords = cityCoords[fromCity] || { lat: 20, lon: 77 }; 
            const sunPos = SunCalc.getPosition(startTime, coords.lat, coords.lon);
            const azimuth = sunPos.azimuth * (180 / Math.PI);
            const altitude = sunPos.altitude * (180 / Math.PI);

            let safeSide = 'Right';
            let unsafeSide = 'Left';
            
            if (azimuth < 180) { safeSide = 'Right'; unsafeSide = 'Left'; }
            else { safeSide = 'Left'; unsafeSide = 'Right'; }

            const isHighNoon = altitude > 50;
            const scanLine = document.getElementById('scanLine');
            const statusBox = document.getElementById('sunStatus');
            const autoBtn = document.getElementById('autoSelectBtn');
            const allSeats = document.querySelectorAll('.seat:not(.sold):not(.selected)');
            
            scanLine.style.display = 'block';
            statusBox.style.display = 'none';
            autoBtn.style.display = 'none';
            
            // Remove previous highlights
            allSeats.forEach(s => s.classList.remove('sun-safe', 'sun-hot'));

            setTimeout(() => {
                scanLine.style.display = 'none';
                
                if(isHighNoon) {
                    statusBox.innerHTML = `<i class="fas fa-sun fa-beat text-warning"></i> It's high noon! <b>Window seats are hot</b>. We recommend <b>Aisle</b> seats.`;
                    statusBox.style.borderColor = "#ffc107"; statusBox.style.color = "#856404";
                    allSeats.forEach(s => {
                        if(s.innerText.includes('M') || s.innerText.includes('A') || s.innerText.startsWith('U')) s.classList.add('sun-safe');
                        else s.classList.add('sun-hot');
                    });
                } else {
                    statusBox.innerHTML = `<i class="fas fa-check-circle text-success"></i> Sun is on the <b>${unsafeSide}</b>. We recommend seats on the <b>${safeSide}</b> side.`;
                    statusBox.style.borderColor = "#198754"; statusBox.style.color = "#0f5132";
                    allSeats.forEach(s => {
                        let isSafe = false;
                        if(safeSide === 'Left' && (s.id.startsWith('L'))) isSafe = true;
                        if(safeSide === 'Right' && (s.id.startsWith('R'))) isSafe = true;
                        if(isSafe) s.classList.add('sun-safe');
                        else s.classList.add('sun-hot');
                    });
                }
                
                statusBox.style.display = 'block';
                const safeCount = document.querySelectorAll('.seat.sun-safe').length;
                if(safeCount > 0) autoBtn.style.display = 'block';

            }, 2000); 
        }

        function autoSelectBest() {
            const safeSeats = document.querySelectorAll('.seat.sun-safe:not(.selected)');
            if(safeSeats.length === 0) return alert("No safe seats available!");
            let count = 0;
            safeSeats.forEach(seat => {
                if(count < 2) {
                    seat.click();
                    count++;
                }
            });
        }

        // --- 5. PAYMENT REDIRECT ---
        function goToPayment() {
            try {
                const totalElement = document.getElementById('totalPrice');
                if (!totalElement) {
                    alert("Error: Could not find total price element.");
                    return;
                }
                const total = totalElement.innerText.replace('₹', '').replace(/,/g, '').trim();
                const seats = selectedSeats.join(',');
                const busName = document.getElementById('busTitle').innerText;
                
                const urlParams = new URLSearchParams(window.location.search);
                const from = urlParams.get('from');
                const to = urlParams.get('to');
                const isSleeper = urlParams.get('isSleeper');
                const bus_id = urlParams.get('bus_id');
                const date = urlParams.get('date');

                if (!total) return alert("Please select at least one seat.");
                if (!seats) return alert("No seats selected.");

                const targetUrl = `payment.php?bus_id=${bus_id}&date=${date}&amount=${total}&seats=${seats}&bus=${encodeURIComponent(busName)}&from=${from}&to=${to}&isSleeper=${isSleeper}`;

                window.location.href = targetUrl;

            } catch (error) {
                console.error(error);
                alert("An error occurred: " + error.message);
            }
        }
    </script>
</body>
</html>