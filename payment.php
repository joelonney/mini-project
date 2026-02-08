<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout - SmartBus</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #E53935;
            --primary-dark: #b71c1c;
            --surface: #ffffff;
            --background: #f3f4f6;
            --text-main: #1f2937;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
            --gradient-card: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background);
            color: var(--text-main);
            min-height: 100vh;
            padding-bottom: 50px;
        }

        /* --- Header --- */
        .checkout-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .header-link {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 600;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
        }
        .header-link:hover { color: var(--primary); transform: translateX(-5px); }

        /* --- Layout --- */
        .main-container {
            padding-top: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* --- Cards --- */
        .glass-card {
            background: var(--surface);
            border-radius: 24px;
            padding: 30px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255,255,255,0.6);
            height: 100%;
            transition: transform 0.3s ease;
        }
        .glass-card:hover { transform: translateY(-5px); }

        .order-card {
            border-left: 5px solid var(--primary);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* --- Payment Tabs --- */
        .payment-tabs {
            display: flex;
            background: #e5e7eb;
            padding: 6px;
            border-radius: 16px;
            margin-bottom: 25px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .tab-item {
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tab-item:hover { background: rgba(255,255,255,0.8); }
        .tab-item.active {
            background: var(--surface);
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        /* --- Form Elements --- */
        .form-label-modern {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: block;
            margin-left: 4px;
        }

        .input-wrapper {
            position: relative;
            margin-bottom: 20px;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary);
            z-index: 5;
            opacity: 0.6;
            transition: 0.3s;
        }

        .form-control-modern {
            width: 100%;
            padding: 14px 16px 14px 45px; /* Left padding for icon */
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 1rem;
            color: var(--text-main);
            background: #fcfcfc;
            transition: all 0.3s;
            font-weight: 500;
        }

        .form-control-modern:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(229, 57, 53, 0.1);
            outline: none;
        }
        
        .form-control-modern:focus + i { opacity: 1; color: var(--primary); }

        /* --- Credit Card Visual --- */
        .card-visual-container {
            margin-bottom: 30px;
            perspective: 1000px;
        }

        .credit-card {
            background: var(--gradient-card);
            color: white;
            border-radius: 20px;
            padding: 30px;
            position: relative;
            box-shadow: 0 20px 50px rgba(30, 60, 114, 0.4);
            height: 230px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Shine effect */
        .credit-card::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(25deg);
            pointer-events: none;
        }

        .chip {
            width: 55px; height: 40px;
            background: linear-gradient(135deg, #ffd700 0%, #ffaa00 100%);
            border-radius: 8px;
            margin-bottom: 25px;
            position: relative;
            overflow: hidden;
        }
        /* Chip lines */
        .chip::after {
            content: ''; position: absolute; top: 50%; left: 0; width: 100%; height: 1px; background: rgba(0,0,0,0.2);
        }
        .chip::before {
            content: ''; position: absolute; left: 50%; top: 0; width: 1px; height: 100%; background: rgba(0,0,0,0.2);
        }

        .card-number { 
            font-size: 1.5rem; letter-spacing: 3px; 
            font-family: 'Courier New', monospace; margin-bottom: 25px; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.2); 
        }
        
        .card-details { display: flex; justify-content: space-between; font-size: 0.9rem; text-transform: uppercase; }
        .card-name { font-weight: 600; letter-spacing: 1px; }

        /* --- UPI --- */
        .upi-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .upi-btn {
            border: 2px solid var(--border);
            background: white;
            padding: 20px;
            border-radius: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .upi-btn.selected {
            border-color: var(--primary);
            background: rgba(229, 57, 53, 0.05);
            box-shadow: 0 4px 15px rgba(229, 57, 53, 0.1);
        }
        .upi-btn:hover { transform: translateY(-3px); }
        .upi-btn i { font-size: 1.8rem; margin-bottom: 8px; display: block; }
        .upi-btn span { font-size: 0.9rem; font-weight: 600; color: var(--text-muted); }
        .upi-btn.selected span { color: var(--primary); }

        /* --- Pay Button --- */
        .btn-pay {
            background: var(--primary);
            color: white;
            width: 100%;
            padding: 18px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1.1rem;
            border: none;
            transition: 0.3s;
            box-shadow: 0 4px 20px rgba(229, 57, 53, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        .btn-pay:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 8px 25px rgba(229, 57, 53, 0.4); }

        /* --- Loader --- */
        .payment-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255,255,255,0.98);
            z-index: 2000;
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(5px);
        }

        .loader-visual {
            width: 60px; height: 60px;
            border: 5px solid rgba(229, 57, 53, 0.1);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        .hidden { display: none !important; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="checkout-header">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Changed href to seats.php -->
            <a href="javascript:history.back()" class="header-link">
                <i class="fas fa-arrow-left me-2"></i> Back to Seats
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold">Secure Checkout</span>
                <span class="badge bg-white text-success border shadow-sm">
                    <i class="fas fa-lock me-1"></i> SSL Secured
                </span>
            </div>
        </div>
    </div>

    <div class="container main-container">
        <div class="row g-4">
            
            <!-- LEFT: Order Summary -->
            <div class="col-lg-4">
                <div class="glass-card order-card">
                    <h5 class="fw-bold mb-4 text-muted text-uppercase small">Booking Summary</h5>
                    
                    <!-- Bus Icon & Name -->
                    <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                        <div class="bg-light rounded-circle p-3 me-3" style="width:50px; height:50px; display:flex; align-items:center; justify-content:center; background:rgba(229,57,53,0.1);">
                            <i class="fas fa-bus text-danger fa-lg"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark h5 mb-0" id="summaryBusName">Bus Name</div>
                            <small class="text-muted" id="summaryRoute">Route</small>
                        </div>
                    </div>

                    <!-- Details List -->
                    <div style="flex-grow:1;">
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Seats</small>
                            <span class="fw-bold" id="summarySeats">--</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Type</small>
                            <span class="fw-bold" id="summaryType">--</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Taxes & Fees</small>
                            <span class="text-success small fw-bold">Included</span>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="pt-3 border-top mt-auto">
                        <div class="d-flex justify-content-between align-items-end">
                            <span class="text-muted fw-bold">Total Payable</span>
                            <span class="display-6 fw-bold text-danger" id="summaryTotal">₹0</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Payment Form -->
            <div class="col-lg-8">
                <div class="glass-card">
                    
                    <!-- START FORM TAG -->
                    <form id="paymentForm" action="includes/process_booking.php" method="POST">
                        
                        <!-- HIDDEN INPUTS FOR BACKEND -->
                        <input type="hidden" name="bus_id" id="inputBusId" value="">
                        <input type="hidden" name="travel_date" id="inputDate" value="">
                        <input type="hidden" name="seats" id="inputSeats" value="">
                        <input type="hidden" name="amount" id="inputAmount" value="">
                        <input type="hidden" name="from" id="inputFrom" value="">
                        <input type="hidden" name="to" id="inputTo" value="">

                        <!-- Payment Tabs -->
                        <div class="payment-tabs">
                            <div class="tab-item active" onclick="switchTab('card')">
                                <i class="far fa-credit-card"></i> Card
                            </div>
                            <div class="tab-item" onclick="switchTab('upi')">
                                <i class="fas fa-mobile-alt"></i> UPI
                            </div>
                            <div class="tab-item" onclick="switchTab('net')">
                                <i class="fas fa-university"></i> Net Banking
                            </div>
                        </div>

                        <!-- 1. CARD FORM -->
                        <div id="card-section">
                            <!-- Visual Card -->
                            <div class="card-visual-container">
                                <div class="credit-card">
                                    <div style="position: absolute; top: 20px; right: 20px; font-size: 2rem; font-weight: 900; opacity: 0.9;">
                                        <i class="fab fa-cc-mastercard"></i>
                                    </div>
                                    <div class="chip"></div>
                                    <div class="card-number" id="displayCardNumber">#### #### #### ####</div>
                                    <div class="card-details">
                                        <div>
                                            <small style="opacity:0.8; display:block; font-size:0.6rem; letter-spacing:1px;">CARD HOLDER</small>
                                            <div class="card-name" id="displayCardName">YOUR NAME</div>
                                        </div>
                                        <div style="text-align:right;">
                                            <small style="opacity:0.8; display:block; font-size:0.6rem;">EXPIRES</small>
                                            <div class="card-name" id="displayCardExpiry">MM/YY</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Input Form -->
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label-modern">Full Name</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-user"></i>
                                        <input type="text" class="form-control-modern" id="inputName" oninput="updateCardName()">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-modern">Email (for Receipt)</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-envelope"></i>
                                        <input type="email" class="form-control-modern" id="inputEmail" placeholder="name@example.com">
                                    </div>
                                </div>
                            </div>

                            <div class="input-wrapper">
                                <label class="form-label-modern">Card Number</label>
                                <i class="fas fa-credit-card"></i>
                                <input type="text" class="form-control-modern" id="inputCardNum" maxlength="19" oninput="formatCardNumber()">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label-modern">Expiry Date</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-calendar"></i>
                                        <input type="text" class="form-control-modern" id="inputExpiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry()">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-modern">CVV / PIN</label>
                                    <div class="input-wrapper">
                                        <i class="fas fa-lock"></i>
                                        <input type="password" class="form-control-modern" id="inputCvv" maxlength="3">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. UPI FORM -->
                        <div id="upi-section" class="hidden">
                            <div class="upi-grid">
                                <div class="upi-btn selected" onclick="selectUpi(this)">
                                    <i class="fab fa-google" style="color:#4285F4"></i>
                                    <span>GPay</span>
                                </div>
                                <div class="upi-btn" onclick="selectUpi(this)">
                                    <i class="fas fa-qrcode" style="color:#6739B7"></i>
                                    <span>PhonePe</span>
                                </div>
                                <div class="upi-btn" onclick="selectUpi(this)">
                                    <i class="fas fa-wallet" style="color:#00B9F1"></i>
                                    <span>Paytm</span>
                                </div>
                            </div>

                            <div class="text-center mb-3">
                                <span class="text-muted small fw-bold">OR</span>
                            </div>

                            <div class="input-wrapper">
                                <label class="form-label-modern">UPI ID</label>
                                <i class="fas fa-at"></i>
                                <input type="text" class="form-control-modern" id="inputUpiId" placeholder="e.g. name@okhdfcbank">
                            </div>
                        </div>

                        <!-- 3. NET BANKING FORM -->
                        <div id="net-section" class="hidden">
                            <div class="input-wrapper">
                                <label class="form-label-modern">Select Bank</label>
                                <i class="fas fa-university"></i>
                                <select class="form-select form-control-modern" id="selectBank">
                                    <option value="">-- Choose Bank --</option>
                                    <option value="hdfc">HDFC Bank</option>
                                    <option value="icici">ICICI Bank</option>
                                    <option value="sbi">State Bank of India</option>
                                    <option value="axis">Axis Bank</option>
                                    <option value="koti">Kotak Mahindra Bank</option>
                                </select>
                            </div>
                            <div class="alert alert-warning small mt-3 border-0 bg-light">
                                <i class="fas fa-info-circle me-2"></i> You will be securely redirected to your bank's 3D Secure page.
                            </div>
                        </div>

                        <!-- Pay Button -->
                        <div class="mt-4">
                            <button type="submit" class="btn-pay" onclick="return processPayment()">
                                <i class="fas fa-lock"></i> Confirm Payment <span id="btnAmount">₹0</span>
                            </button>
                        </div>

                    </form> <!-- END FORM -->
                </div>
            </div>
        </div>
    </div>

    <!-- Processing Overlay -->
    <div class="payment-overlay" id="paymentOverlay">
        <div class="loader-visual"></div>
        <h4 class="fw-bold mt-3">Processing Transaction...</h4>
        <p class="text-muted">Please do not close this window.</p>
    </div>

    <script>
        let activeMethod = 'card';

        // --- 1. INITIALIZATION ---
        document.addEventListener("DOMContentLoaded", () => {
            const params = new URLSearchParams(window.location.search);
            const amount = params.get('amount');
            const seats = params.get('seats');
            const bus = params.get('bus');
            const from = params.get('from');
            const to = params.get('to');
            const isSleeper = params.get('isSleeper');
            
            // IMPORTANT: Capture bus_id and date for backend processing
            const bus_id = params.get('bus_id'); 
            const travel_date = params.get('date'); 

            // Populate Summary
            document.getElementById('summaryTotal').innerText = '₹' + amount;
            document.getElementById('btnAmount').innerText = '₹' + amount;
            document.getElementById('summarySeats').innerText = seats.replace(/,/g, ', ');
            document.getElementById('summaryBusName').innerText = decodeURIComponent(bus);
            document.getElementById('summaryRoute').innerText = `${from} to ${to}`;
            document.getElementById('summaryType').innerText = isSleeper === 'true' ? 'Sleeper Bus' : 'Seater Bus';
            
            // Populate Hidden Inputs (Crucial for backend)
            if(bus_id) document.getElementById('inputBusId').value = bus_id;
            if(travel_date) document.getElementById('inputDate').value = travel_date;
            document.getElementById('inputSeats').value = seats;
            document.getElementById('inputAmount').value = amount;
            document.getElementById('inputFrom').value = from;
            document.getElementById('inputTo').value = to;
        });

        // --- 2. TAB LOGIC ---
        function switchTab(method) {
            activeMethod = method;
            
            // Update Visual Tabs
            document.querySelectorAll('.tab-item').forEach(btn => btn.classList.remove('active'));
            event.currentTarget.classList.add('active');

            // Hide Sections
            document.getElementById('card-section').classList.add('hidden');
            document.getElementById('upi-section').classList.add('hidden');
            document.getElementById('net-section').classList.add('hidden');

            // Show Selected
            if(method === 'card') document.getElementById('card-section').classList.remove('hidden');
            if(method === 'upi') document.getElementById('upi-section').classList.remove('hidden');
            if(method === 'net') document.getElementById('net-section').classList.remove('hidden');
        }

        function selectUpi(el) {
            document.querySelectorAll('.upi-btn').forEach(b => b.classList.remove('selected'));
            el.classList.add('selected');
        }

        // --- 3. CARD LOGIC ---
        function updateCardName() {
            const name = document.getElementById('inputName').value;
            document.getElementById('displayCardName').innerText = name.toUpperCase() || "YOUR NAME";
        }
        function formatCardNumber() {
            let input = document.getElementById('inputCardNum');
            let value = input.value.replace(/\D/g, '').substring(0, 16);
            let parts = [];
            for (let i = 0; i < value.length; i += 4) parts.push(value.substring(i, i + 4));
            input.value = parts.join(' ');
            document.getElementById('displayCardNumber').innerText = parts.join(' ') || "#### #### #### ####";
        }
        function formatExpiry() {
            let input = document.getElementById('inputExpiry');
            let value = input.value.replace(/\D/g, '');
            if (value.length >= 2) value = value.substring(0, 2) + '/' + value.substring(2, 4);
            input.value = value;
            document.getElementById('displayCardExpiry').innerText = value || "MM/YY";
        }

        // --- 4. PAYMENT PROCESSING ---
        function processPayment() {
            let isValid = false;

            // Validation
            if (activeMethod === 'card') {
                const name = document.getElementById('inputName').value;
                const num = document.getElementById('inputCardNum').value.replace(/\s/g, '');
                const expiry = document.getElementById('inputExpiry').value;
                const cvv = document.getElementById('inputCvv').value;
                const email = document.getElementById('inputEmail').value;

                if (name.length < 3) return alert("Please enter a valid full name.");
                if (!email.includes('@') || !email.includes('.')) return alert("Please enter a valid email address.");
                if (num.length < 16) return alert("Please enter a valid 16-digit card number.");
                if (!expiry.match(/^\d{2}\/\d{2}$/)) return alert("Please enter valid expiry (MM/YY).");
                if (cvv.length < 3) return alert("Please enter valid CVV.");
                isValid = true;

            } else if (activeMethod === 'upi') {
                const upiId = document.getElementById('inputUpiId').value;
                if(!upiId.includes('@')) return alert("Please enter a valid UPI ID (e.g., user@bank).");
                isValid = true;

            } else if (activeMethod === 'net') {
                const bank = document.getElementById('selectBank').value;
                if(bank === "") return alert("Please select your bank.");
                isValid = true;
            }

            if(!isValid) return;

            // Show Loader
            const loader = document.getElementById('paymentOverlay');
            loader.style.display = 'flex';

            // Simulate API Delay then Submit
            setTimeout(() => {
                document.getElementById('paymentForm').submit(); 
            }, 2000); 
            
            return false; // Prevent immediate submission
        }
        console.log("Bus ID:", document.getElementById('inputBusId').value);
        console.log("Date:", document.getElementById('inputDate').value);
    </script>
</body>
</html>