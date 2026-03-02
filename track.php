<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db.php';
session_write_close(); // Close session lock early so AJAX polling works on built-in server
$search_bus = isset($_GET['bus_no']) ? htmlspecialchars(trim($_GET['bus_no'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Bus - SmartBus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        
        :root {
            --primary: #E53935;
            --text-dark: #1E293B;
            --text-muted: #64748B;
            --bg-color: #F8F9FC;
            --glass-bg: rgba(255, 255, 255, 0.85);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-dark);
        }

        .navbar-custom {
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            padding: 1rem 0;
        }
        
        .navbar-brand { font-weight: 800; font-size: 1.5rem; color: var(--text-dark); text-decoration: none; }
        .navbar-brand i { color: var(--primary); margin-right: 8px; }

        .tracking-header {
            padding: 60px 0 40px;
            text-align: center;
        }

        .search-box {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
        }

        .timeline-container {
            background: white;
            padding: 50px 30px;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-top: 40px;
            display: none; /* Hidden until loaded */
        }

        /* Responsive Horizontal Timeline */
        .timeline {
            position: relative;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 60px;
            padding-bottom: 20px;
        }
        
        /* The base gray line under nodes */
        .timeline::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            width: 100%;
            height: 6px;
            background: #E2E8F0;
            z-index: 1;
            border-radius: 4px;
        }
        
        /* The dynamic filled primary line */
        .timeline-progress {
            position: absolute;
            top: 15px;
            left: 0;
            height: 6px;
            background: var(--primary);
            z-index: 2;
            transition: width 1s ease-in-out;
            width: 0%;
            border-radius: 4px;
        }

        .stop-node {
            position: relative;
            z-index: 3;
            text-align: center;
            flex-basis: 0;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stop-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: white;
            border: 4px solid #E2E8F0;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            color: #94A3B8;
        }

        .stop-node.active .stop-circle,
        .stop-node.passed .stop-circle {
            border-color: var(--primary);
            background: var(--primary);
            color: white;
            box-shadow: 0 0 0 5px rgba(229, 57, 53, 0.2);
        }

        .stop-name {
            margin-top: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .stop-node.active .stop-name,
        .stop-node.passed .stop-name {
            color: var(--text-dark);
        }

        .bus-icon {
            position: absolute;
            top: -45px;
            font-size: 2rem;
            color: var(--primary);
            transition: left 1s ease-in-out;
            filter: drop-shadow(0 4px 6px rgba(229,57,53,0.3));
            z-index: 5;
            left: 0; /* Default start */
            transform: translateX(-50%);
        }

        /* Pulse animation for active stop */
        @keyframes pulseActive {
            0% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(229, 57, 53, 0); }
            100% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0); }
        }
        
        .stop-node.active .stop-circle {
            animation: pulseActive 2s infinite;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .timeline {
                flex-direction: column;
                align-items: flex-start;
                padding-left: 20px;
                margin-top: 20px;
            }
            .timeline::before {
                top: 0; left: 35px; width: 6px; height: 100%;
            }
            .timeline-progress {
                top: 0; left: 35px; width: 6px; height: 0%; transition: height 1s ease;
            }
            .stop-node {
                flex-direction: row;
                margin-bottom: 40px;
                width: 100%;
            }
            .stop-name { margin-top: 0; margin-left: 15px; }
            .bus-icon {
                top: 0; left: -10px !important; transform: translateY(-50%); transition: top 1s ease;
            }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bus-alt"></i>SmartBus
            </a>
            <a href="index.php" class="btn btn-outline-dark rounded-pill fw-bold px-4">Home</a>
        </div>
    </nav>

    <div class="container">
        <div class="tracking-header">
            <h1 class="fw-bold mb-3">Live Bus Tracking</h1>
            <p class="text-muted">Enter your Bus Number to see real-time updates of your journey.</p>
        </div>

        <div class="search-box">
            <form method="GET" action="track.php">
                <div class="input-group">
                    <input type="text" name="bus_no" id="bus_no" class="form-control form-control-lg" placeholder="e.g., KA-15-BUS-8342" value="<?php echo $search_bus; ?>" required>
                    <button class="btn btn-primary px-4 fw-bold" type="submit" style="background:var(--primary); border:none;">Track Now</button>
                </div>
            </form>
        </div>

        <div id="timelineWrapper" class="timeline-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold mb-1" id="routeText">Loading...</h4>
                    <span class="text-muted small">Updated: <span id="lastUpdated">Just now</span></span>
                </div>
                <div id="tripStatus" class="status-badge bg-light text-dark border">
                    <i class="fas fa-spinner fa-spin"></i> Checking Status
                </div>
            </div>

            <div class="timeline" id="trackingTimeline">
                <div class="timeline-progress" id="timelineProgress"></div>
                <i class="fas fa-bus bus-icon" id="busIcon"></i>
                <!-- Stops will be injected here by JS -->
            </div>
        </div>
        
        <div id="errorBox" class="alert alert-danger mt-4 text-center fw-bold" style="display:none;"></div>
    </div>

    <!-- Tracking Logic -->
    <script>
        const busNo = "<?php echo $search_bus; ?>";
        let pollInterval;
        const isMobile = window.innerWidth <= 768;

        if (busNo) {
            document.getElementById('timelineWrapper').style.display = 'block';
            fetchLocation(true); // first run
            pollInterval = setInterval(() => fetchLocation(false), 8000); // poll every 8s
        }

        function fetchLocation(isFirstRun) {
            fetch(`get_bus_location.php?bus_no=${encodeURIComponent(busNo)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.error) {
                        document.getElementById('timelineWrapper').style.display = 'none';
                        document.getElementById('errorBox').style.display = 'block';
                        document.getElementById('errorBox').innerText = data.error;
                        clearInterval(pollInterval);
                        return;
                    }

                    // Update Headers
                    document.getElementById('routeText').innerText = `${data.source} → ${data.destination}`;
                    document.getElementById('lastUpdated').innerText = data.last_updated;
                    
                    const statusBadge = document.getElementById('tripStatus');
                    statusBadge.innerHTML = `<i class="fas fa-location-arrow me-2"></i>${data.status}`;
                    
                    if (data.status === 'Completed') {
                        statusBadge.className = 'status-badge bg-success text-white';
                    } else if (data.status === 'Delayed') {
                        statusBadge.className = 'status-badge bg-danger text-white';
                    } else if (data.status === 'In Transit') {
                        statusBadge.className = 'status-badge bg-primary text-white';
                    } else {
                        statusBadge.className = 'status-badge bg-secondary text-white';
                    }

                    renderTimeline(data.stops, data.current_stop_index, data.status);
                    
                    if(data.status === 'Completed' || data.status === 'Not Started') {
                        // Optionally stop polling if completed
                        // clearInterval(pollInterval);
                    }
                })
                .catch(err => console.error(err));
        }

        function renderTimeline(stops, currentIndex, status) {
            const container = document.getElementById('trackingTimeline');
            // Check if we need to build DOM (first run) or just update classes
            const existingNodes = container.querySelectorAll('.stop-node');
            
            if (existingNodes.length !== stops.length) {
                // Build fresh
                container.innerHTML = `<div class="timeline-progress" id="timelineProgress"></div><i class="fas fa-bus bus-icon" id="busIcon"></i>`;
                stops.forEach((stop, i) => {
                    const node = document.createElement('div');
                    node.className = 'stop-node';
                    node.id = `node-${i}`;
                    node.innerHTML = `
                        <div class="stop-circle">${i+1}</div>
                        <div class="stop-name">${stop.stop_name}</div>
                    `;
                    container.appendChild(node);
                });
            }

            // Update state
            const progressLine = document.getElementById('timelineProgress');
            const busIcon = document.getElementById('busIcon');
            let numStops = stops.length;
            
            let targetNodeIndex = currentIndex;
            if (currentIndex >= numStops) targetNodeIndex = numStops - 1; // Completed goes to last stop
            if (currentIndex === 999) targetNodeIndex = numStops - 1; // Special completed flag
            
            // Calculate percentage
            const percentage = (targetNodeIndex / (numStops - 1)) * 100;

            stops.forEach((stop, i) => {
                const node = document.getElementById(`node-${i}`);
                if (i < targetNodeIndex || currentIndex === 999) {
                    node.className = 'stop-node passed';
                    node.querySelector('.stop-circle').innerHTML = '<i class="fas fa-check"></i>';
                } else if (i === targetNodeIndex && currentIndex !== 999 && status !== 'Not Started') {
                    node.className = 'stop-node active';
                    node.querySelector('.stop-circle').innerHTML = '<i class="fas fa-bus"></i>';
                } else {
                    node.className = 'stop-node';
                    node.querySelector('.stop-circle').innerHTML = i+1;
                }
            });

            // Move Progress line & Bus Icon
            if (isMobile) {
                progressLine.style.height = `${percentage}%`;
                progressLine.style.width = '6px';
                // Adjust position based on nodes
                busIcon.style.top = `calc(${percentage}% - 15px)`;
                busIcon.style.left = '-10px';
                busIcon.style.transform = 'rotate(90deg)'; // point down
            } else {
                progressLine.style.width = `${percentage}%`;
                // Position bus on the line exactly at the node
                busIcon.style.left = `${percentage}%`;
            }
        }
    </script>
</body>
</html>
