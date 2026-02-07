<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartBus - Travel Simplified</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #E53935;       /* Vibrant Red */
            --primary-dark: #B71C1C;  /* Darker Red for hover */
            --primary-light: #FFEBEE; /* Very light red for backgrounds */
            --accent: #F8F9FC;        /* Soft blue-grey background */
            --text-dark: #1E293B;     /* Slate 800 */
            --text-muted: #64748B;    /* Slate 500 */
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.5);
            --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
            --radius-xl: 24px;
            --radius-2xl: 32px;
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif; /* Modern geometric sans */
            background: var(--accent);
            color: var(--text-dark);
            overflow-x: hidden;
            line-height: 1.6;
        }

        /* --- Animations --- */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
            100% { transform: translateY(0px); }
        }
        
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }

        @keyframes driveBus {
            0% { left: -5%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { left: 105%; opacity: 0; }
        }

        @keyframes pulseSoft {
            0% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0.2); }
            70% { box-shadow: 0 0 0 10px rgba(229, 57, 53, 0); }
            100% { box-shadow: 0 0 0 0 rgba(229, 57, 53, 0); }
        }

        /* Staggered Animation Utility */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: var(--transition);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* --- Navbar --- */
        .navbar {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0,0,0,0.03);
            transition: all 0.3s ease;
            padding: 1rem 0;
        }
        
        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: var(--shadow-sm);
            padding: 0.7rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        
        .navbar-brand i { 
            font-size: 1.6rem; 
            color: var(--primary); 
            background: var(--primary-light);
            padding: 8px;
            border-radius: 12px;
        }

        /* --- FIX FOR 'BUSBUS' BUG --- */
        /* Removed the ::after pseudo-element. The HTML contains the text 'Bus' inside the span, 
           so we just style that span directly now. */
        .navbar-brand span { 
            color: var(--primary); 
            position: relative;
        }

        .btn-nav {
            border: 2px solid #E2E8F0;
            color: var(--text-dark);
            font-weight: 600;
            padding: 10px 24px;
            border-radius: 50px;
            transition: var(--transition);
            background: transparent;
        }
        
        .btn-nav:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
            transform: translateY(-2px);
        }

        .btn-nav.primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }
        
        .btn-nav.primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(229, 57, 53, 0.4);
        }

        /* --- Hero Section --- */
        .hero-section {
            padding: 180px 0 100px;
            position: relative;
            overflow: hidden;
        }

        /* Ambient Background Blobs */
        .hero-bg-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.6;
            animation: blob 10s infinite alternate;
        }
        .blob-1 { top: -10%; right: -5%; width: 500px; height: 500px; background: #ffebeb; }
        .blob-2 { bottom: 10%; left: -10%; width: 400px; height: 400px; background: #eef2ff; }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary-light) 0%, #fff 100%);
            color: var(--primary);
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 24px;
            border: 1px solid rgba(229, 57, 53, 0.1);
            box-shadow: var(--shadow-sm);
            animation: pulseSoft 2.5s infinite;
        }

        .hero-title {
            font-size: 3.8rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 1.5rem;
            letter-spacing: -1.5px;
            color: var(--text-dark);
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-muted);
            margin-bottom: 3rem;
            max-width: 500px;
            line-height: 1.7;
            font-weight: 400;
        }

        /* --- Search Bar (Glassmorphism) --- */
        .search-container {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            padding: 10px;
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-lg);
            margin-bottom: 4rem;
            border: 1px solid var(--glass-border);
            position: relative;
            transition: var(--transition);
        }
        
        .search-container:hover { 
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
        }

        .search-input-wrapper {
            position: relative;
            height: 72px;
        }

        .search-icon {
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            color: #94A3B8;
            font-size: 1.2rem;
            z-index: 5;
            transition: var(--transition);
        }

        .form-control-hero {
            padding-left: 64px;
            border: 1px solid #E2E8F0;
            height: 100%;
            border-radius: 20px;
            background: white;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-dark);
            width: 100%;
            transition: var(--transition);
        }
        
        .form-control-hero:focus { 
            outline: none; 
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(229, 57, 53, 0.1);
            background: #fff;
        }

        .search-input-wrapper:focus-within .search-icon {
            color: var(--primary);
        }

        .btn-hero-search {
            background: linear-gradient(135deg, var(--primary) 0%, #d32f2f 100%);
            color: white;
            border: none;
            height: 72px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 10px 20px -5px rgba(229, 57, 53, 0.3);
        }

        .btn-hero-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(229, 57, 53, 0.4);
        }

        .btn-text { transition: opacity 0.2s; position: relative; z-index: 2; }
        .loader-icon { 
            display: none; 
            font-size: 1.5rem; 
            animation: spin 1s linear infinite; 
            position: absolute;
        }
        
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        .btn-hero-search.loading .btn-text { opacity: 0; }
        .btn-hero-search.loading .loader-icon { display: block; }

        /* --- Hero Image & Animation --- */
        .hero-img-wrapper { position: relative; }
        
        /* Bus Animation Line */
        .route-line {
            position: absolute; top: 50%; left: 0; width: 100%; height: 4px; 
            background: #E2E8F0; z-index: 0; border-radius: 4px; transform: translateY(-50%);
        }
        
        .route-dot {
            position: absolute; top: 50%; width: 16px; height: 16px; 
            background: white; border: 4px solid #CBD5E1; border-radius: 50%; z-index: 1; transform: translateY(-50%);
        }
        .dot-start { left: 0; border-color: var(--primary); }
        .dot-end { right: 0; border-color: var(--text-muted); }

        /* The Animated Bus */
        .bus-anim-icon {
            position: absolute;
            top: 50%;
            left: -10%;
            font-size: 2.5rem;
            color: var(--primary);
            z-index: 2;
            transform: translateY(-50%);
            filter: drop-shadow(0 4px 6px rgba(229, 57, 53, 0.3));
            animation: driveBus 8s linear infinite;
        }

        .hero-main-img {
            width: 100%;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
            transition: var(--transition);
            border: 4px solid white;
        }
        
        /* Floating Badge */
        .float-badge {
            position: absolute; 
            bottom: -20px; 
            right: 0px; 
            background: white; 
            padding: 16px 24px; 
            border-radius: 20px; 
            box-shadow: var(--shadow-lg); 
            z-index: 3; 
            display: flex; 
            align-items: center; 
            gap: 16px; 
            max-width: 260px; 
            animation: float 5s ease-in-out infinite;
            border: 1px solid rgba(255,255,255,0.8);
        }
        
        .icon-circle-sm {
            width: 44px; height: 44px; 
            display: flex; align-items: center; justify-content: center;
            border-radius: 14px;
            color: white;
            flex-shrink: 0;
        }

        /* --- Stats Section --- */
        .stats-section {
            background: white;
            border-radius: var(--radius-xl);
            padding: 60px 0;
            box-shadow: var(--shadow-md);
            margin-bottom: 100px;
            position: relative;
            z-index: 2;
        }
        
        .stat-item { text-align: center; padding: 10px; }
        .stat-number { 
            font-size: 3.5rem; 
            font-weight: 800; 
            color: var(--text-dark); 
            display: block; 
            margin-bottom: 8px; 
            line-height: 1; 
            background: linear-gradient(180deg, var(--text-dark) 0%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stat-label { font-size: 0.9rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; }

        /* --- Destination Cards --- */
        .destination-card {
            background: white;
            border-radius: var(--radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            border: none;
            height: 100%;
        }

        .destination-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
        }

        .dest-img-wrap {
            height: 240px;
            overflow: hidden;
            position: relative;
        }

        .dest-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }

        .destination-card:hover .dest-img { transform: scale(1.1); }

        .dest-overlay {
            position: absolute;
            bottom: 0; left: 0; width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.2) 60%, transparent 100%);
            padding: 60px 24px 24px;
            color: white;
        }

        .dest-name { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
        .dest-price { font-size: 1.1rem; font-weight: 500; opacity: 0.9; }
        
        .btn-card-cta {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.4);
            color: white;
            padding: 8px 24px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: var(--transition);
            margin-top: 12px;
            display: inline-block;
        }
        
        .destination-card:hover .btn-card-cta { 
            background: var(--primary); 
            border-color: var(--primary); 
            transform: translateY(-2px);
        }

        /* --- Features Section --- */
        .feature-card {
            background: white;
            padding: 40px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0,0,0,0.02);
            transition: var(--transition);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(229, 57, 53, 0.2);
        }

        /* Feature card hover effect background */
        .feature-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--primary);
            opacity: 0;
            transition: var(--transition);
        }
        .feature-card:hover::before { opacity: 1; }

        .icon-box-lg {
            width: 64px; height: 64px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 24px;
            transition: var(--transition);
        }

        .feature-card:hover .icon-box-lg {
            background: var(--primary);
            color: white;
            transform: rotateY(180deg);
        }

        /* --- App Section --- */
        .app-section {
            background: linear-gradient(135deg, #1E293B 0%, #0F172A 100%);
            border-radius: var(--radius-2xl);
            padding: 80px 60px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            margin-bottom: 100px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
        }
        
        /* Decorative circles in dark section */
        .app-circle {
            position: absolute; border-radius: 50%; border: 1px solid rgba(255,255,255,0.05);
        }
        .c1 { width: 300px; height: 300px; top: -50px; right: -50px; }
        .c2 { width: 150px; height: 150px; bottom: 50px; left: 50px; }

        .qr-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            padding: 14px 28px;
            border-radius: 16px;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            backdrop-filter: blur(4px);
        }

        .qr-btn:hover {
            background: white;
            color: var(--text-dark);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .app-phone-mockup {
            width: 260px;
            border-radius: 36px;
            border: 8px solid #334155;
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.5);
            animation: float 6s ease-in-out infinite;
            z-index: 2;
            overflow: hidden;
            position: relative;
        }

        /* --- Footer --- */
        .footer-wave {
            position: absolute; top: -50px; left: 0; width: 100%; overflow: hidden; line-height: 0; z-index: 0;
        }
        .footer-wave svg { position: relative; display: block; width: calc(100% + 1.3px); height: 50px; }
        .footer-wave .shape-fill { fill: #F8F9FC; } /* Match body bg */
        
        footer {
            position: relative;
            background: white;
            padding-top: 80px;
            padding-bottom: 40px;
            border-top: 1px solid #eee;
            z-index: 1;
        }

        .footer-heading { font-weight: 700; margin-bottom: 24px; color: var(--text-dark); font-size: 1.1rem; }
        .footer-link {
            display: block; color: var(--text-muted); text-decoration: none; margin-bottom: 14px; transition: var(--transition); font-weight: 500;
        }
        .footer-link:hover { color: var(--primary); padding-left: 8px; }

        .social-icon {
            width: 44px; height: 44px; border-radius: 50%; background: #F1F5F9;
            display: inline-flex; align-items: center; justify-content: center; color: var(--text-dark);
            margin-right: 12px; transition: var(--transition); text-decoration: none;
        }
        .social-icon:hover { background: var(--primary); color: white; transform: translateY(-5px); }

        #toast-container {
            position: fixed; bottom: 30px; right: 30px; z-index: 9999;
        }
        
        .custom-toast {
            background: white; border-left: 5px solid var(--primary);
            padding: 20px 25px; border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            margin-top: 15px; display: flex; align-items: center; gap: 15px;
            transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); min-width: 320px;
        }
        
        .custom-toast.show { transform: translateX(0); }
        .toast-icon { color: var(--primary); font-size: 1.4rem; }

        @media (max-width: 991px) {
            .app-section { flex-direction: column; text-align: center; padding: 60px 30px; }
            .app-wrapper { margin-top: 50px; }
            .hero-title { font-size: 2.8rem; }
            .stats-section { margin-bottom: 60px; }
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-bus-alt"></i> 
                <span>SmartBus</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex gap-3 mt-3 mt-lg-0 align-items-center">
                    <a href="login.html" class="btn btn-nav">Login</a>
                    <a href="register.html" class="btn btn-nav primary">Sign Up</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <!-- Background Decoration -->
        <div class="hero-bg-blob blob-1"></div>
        <div class="hero-bg-blob blob-2"></div>

        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="badge-container animate-on-scroll">
                        <div class="hero-badge">
                            <i class="fas fa-bolt"></i> New: Live GPS Tracking
                        </div>
                    </div>
                    
                    <h1 class="hero-title animate-on-scroll">Travel Smart,<br>Arrive Happy.</h1>
                    
                    <p class="hero-subtitle animate-on-scroll">
                        Find the best bus routes, book sun-free seats, and track your journey in real-time. The modern way to travel by road.
                    </p>

                    <!-- Enhanced Search Form -->
                    <div class="search-container animate-on-scroll">
                        <form id="homeSearchForm" onsubmit="handleHomeSearch(event)">
                            <div class="row g-2">
                                <div class="col-md-5 col-sm-12">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-map-marker-alt search-icon"></i>
                                        <input type="text" id="homeFrom" class="form-control form-control-hero" placeholder="From (e.g. Bangalore)" required>
                                    </div>
                                </div>
                                <div class="col-md-5 col-sm-12">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-location-arrow search-icon"></i>
                                        <input type="text" id="homeTo" class="form-control form-control-hero" placeholder="To (e.g. Kochi)" required>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-12 d-flex">
                                    <button type="submit" id="searchBtn" class="btn-hero-search">
                                        <span class="btn-text">Search</span>
                                        <i class="fas fa-circle-notch loader-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <div class="mt-4 d-flex gap-4 text-muted small animate-on-scroll">
                        <span class="d-flex align-items-center gap-2"><i class="fas fa-check-circle text-success"></i> Instant Confirmation</span>
                        <span class="d-flex align-items-center gap-2"><i class="fas fa-shield-alt text-primary"></i> Secure Payment</span>
                    </div>
                </div>

                <!-- Hero Image with Visualizer -->
                <div class="col-lg-6 hero-img-wrapper animate-on-scroll">
                    <!-- Route Line -->
                    <div class="route-line"></div>
                    <div class="route-dot dot-start"></div>
                    <div class="route-dot dot-end"></div>
                    
                    <!-- Animated Bus -->
                    <i class="fas fa-bus bus-anim-icon"></i>

                    <img src="https://picsum.photos/seed/busmodern/800/600" 
                         class="hero-main-img" alt="Bus Travel">
                    
                    <!-- Floating Badge -->
                    <div class="float-badge">
                        <div class="icon-circle-sm bg-success">
                            <i class="fas fa-sun"></i>
                        </div>
                        <div class="d-flex flex-column justify-content-center">
                            <div class="small text-muted mb-0 lh-sm">Seat Availability</div>
                            <div class="fw-bold text-dark lh-sm">Window & Shade</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results (Hidden by default - Logic kept same) -->
    <div class="container" id="resultsContainer" style="display:none;">
        <div id="searchResults">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Available Buses</h3>
                <button onclick="closeResults()" class="btn btn-sm btn-outline-secondary rounded-pill px-4">Close</button>
            </div>
            
            <!-- Result Items (Styled slightly better) -->
            <div class="result-item p-4 bg-white mb-3 rounded-3 shadow-sm animate-on-scroll" onclick="selectBus('SmartBus Volvo', 650, 'seater')">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">SmartBus Volvo <span class="badge bg-success">On Time</span></h5>
                        <p class="text-muted small mb-0">22:30 -> 05:30 • 7h 00m • Seater</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h4 mb-0 fw-bold text-primary">₹650</div>
                        <small class="text-muted d-block mb-2">22 Seats Left</small>
                        <button class="btn btn-sm btn-dark rounded-pill px-4">
                            Select Seats <i class="fas fa-arrow-right small ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="result-item p-4 bg-white mb-3 rounded-3 shadow-sm animate-on-scroll" onclick="selectBus('Express Lines Multi-Axle', 820, 'seater')">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">Express Lines Multi-Axle <span class="badge bg-warning text-dark">Delayed 15m</span></h5>
                        <p class="text-muted small mb-0">23:00 -> 06:15 • 7h 15m • Seater</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h4 mb-0 fw-bold text-primary">₹820</div>
                        <small class="text-muted d-block mb-2">5 Seats Left</small>
                        <button class="btn btn-sm btn-dark rounded-pill px-4">
                            Select Seats <i class="fas fa-arrow-right small ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="result-item p-4 bg-white mb-3 rounded-3 shadow-sm animate-on-scroll" onclick="selectBus('Sleeper Premium', 900, 'sleeper')">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">Sleeper Premium <span class="badge bg-success">On Time</span></h5>
                        <p class="text-muted small mb-0">23:45 -> 07:00 • 7h 15m • Sleeper</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="h4 mb-0 fw-bold text-primary">₹900</div>
                        <small class="text-muted d-block mb-2">12 Seats Left</small>
                        <button class="btn btn-sm btn-dark rounded-pill px-4">
                            Select Seats <i class="fas fa-arrow-right small ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Stats Section -->
    <section class="container mb-5">
        <div class="stats-section animate-on-scroll">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4 stat-item">
                        <div class="stat-number counter" data-target="10000">0</div>
                        <div class="stat-label">Happy Travelers</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <div class="stat-number counter" data-target="5000">0</div>
                        <div class="stat-label">Daily Trips</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <div class="stat-number counter" data-target="50">0</div>
                        <div class="stat-label">Cities Connected</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container py-5">
        <div class="text-center mb-5 animate-on-scroll">
            <h2 class="fw-bold display-6">Why Choose SmartBus?</h2>
            <p class="text-muted">Everything you need for a comfortable road trip.</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card animate-on-scroll">
                    <div class="icon-box-lg">
                        <i class="fas fa-sun"></i>
                    </div>
                    <h5 class="fw-bold">Avoid Sunlight</h5>
                    <p class="text-muted mb-0">Our 3D seat maps show you exactly where the sun hits. Pick a shaded seat and travel cool.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card animate-on-scroll">
                    <div class="icon-box-lg">
                        <i class="fas fa-map-marked-alt"></i>
                    </div>
                    <h5 class="fw-bold">Live Tracking</h5>
                    <p class="text-muted mb-0">Share your live location with family. Track your bus in real-time on map.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card animate-on-scroll">
                    <div class="icon-box-lg">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h5 class="fw-bold">Easy Booking</h5>
                    <p class="text-muted mb-0">Book in under 60 seconds. M-ticket support means no printing required.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Trending Destinations -->
    <section class="container pb-5">
        <h4 class="fw-bold mb-4 animate-on-scroll">Trending Destinations</h4>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="destination-card animate-on-scroll" onclick="quickRoute('Bangalore', 'Kochi')">
                    <div class="dest-img-wrap">
                        <img src="https://picsum.photos/seed/kerala/600/400" class="dest-img" alt="Kochi">
                    </div>
                    <div class="dest-overlay">
                        <div class="dest-name">Kochi</div>
                        <div class="dest-price">From ₹450</div>
                        <div class="btn-card-cta">Explore</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="destination-card animate-on-scroll" onclick="quickRoute('Mumbai', 'Goa')">
                    <div class="dest-img-wrap">
                        <img src="https://picsum.photos/seed/goa/600/400" class="dest-img" alt="Goa">
                    </div>
                    <div class="dest-overlay">
                        <div class="dest-name">Goa</div>
                        <div class="dest-price">From ₹750</div>
                        <div class="btn-card-cta">Explore</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="destination-card animate-on-scroll" onclick="quickRoute('Delhi', 'Jaipur')">
                    <div class="dest-img-wrap">
                        <img src="https://picsum.photos/seed/jaipur/600/400" class="dest-img" alt="Jaipur">
                    </div>
                    <div class="dest-overlay">
                        <div class="dest-name">Jaipur</div>
                        <div class="dest-price">From ₹800</div>
                        <div class="btn-card-cta">Explore</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- App Promo Section -->
    <div class="container">
        <section class="app-section flex-column flex-lg-row animate-on-scroll">
            <div class="app-circle c1"></div>
            <div class="app-circle c2"></div>

            <div class="flex-grow-1 mb-4 mb-lg-0" style="position: relative; z-index: 2;">
                <h2 class="fw-bold mb-3">The SmartBus App</h2>
                <p class="text-white-50 mb-5">Manage bookings on go. Get notified when your bus arrives. Experience travel like never before.</p>
                
                <div class="d-flex gap-3 flex-wrap mb-4">
                    <a href="#" class="qr-btn">
                        <i class="fab fa-apple fa-2x"></i>
                        <div class="text-start lh-1 d-flex flex-column justify-content-center">
                            <small class="d-block opacity-75" style="font-size: 0.75rem;">Download on the</small>
                            <span class="fw-bold" style="font-size: 1.1rem;">App Store</span>
                        </div>
                    </a>
                    <a href="#" class="qr-btn">
                        <i class="fab fa-google-play fa-2x"></i>
                        <div class="text-start lh-1 d-flex flex-column justify-content-center">
                            <small class="d-block opacity-75" style="font-size: 0.75rem;">Get it on</small>
                            <span class="fw-bold" style="font-size: 1.1rem;">Google Play</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="app-wrapper d-none d-lg-block">
                <div class="app-phone-mockup">
                    <img src="https://picsum.photos/seed/appui/300/600" 
                         style="width:100%; height:100%; object-fit:cover;" alt="App Mockup">
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-wave">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
        <div class="container mt-5">
            <div class="row g-4">
                <div class="col-lg-4 mb-4">
                    <a class="navbar-brand mb-3" href="#">
                        <i class="fas fa-bus-alt"></i> 
                        <span>SmartBus</span>
                    </a>
                    <p class="text-muted">Making road travel smarter, safer, and more comfortable for everyone across the country.</p>
                    <div class="d-flex">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Company</h6>
                    <a href="#" class="footer-link">About Us</a>
                    <a href="#" class="footer-link">Careers</a>
                    <a href="#" class="footer-link">Blog</a>
                    <a href="#" class="footer-link">Press</a>
                </div>
                <div class="col-lg-2 col-6">
                    <h6 class="footer-heading">Support</h6>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">Terms of Service</a>
                    <a href="#" class="footer-link">Legal</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                </div>
                <div class="col-lg-4">
                    <h6 class="footer-heading">Subscribe</h6>
                    <p class="text-muted small mb-3">Get latest updates and offers.</p>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email" style="border-radius: 12px 0 0 12px; border: 1px solid #E2E8F0;">
                        <button class="btn btn-primary" style="background: var(--primary); border:none; border-radius: 0 12px 12px 0;">Go</button>
                    </div>
                </div>
            </div>
            <div class="border-top mt-5 pt-4 text-center">
                <p class="text-muted small mb-0">&copy; 2023 SmartBus Inc. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            
            // --- 1. Navbar Scroll Effect ---
            const navbar = document.getElementById('mainNav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // --- 2. Intersection Observer for Staggered Scroll Animations ---
            const observerOptions = {
                threshold: 0.1,
                rootMargin: "0px 0px -50px 0px"
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        
                        // Trigger counter if it's a stat number
                        const counter = entry.target.querySelector('.counter');
                        if (counter && !counter.classList.contains('counted')) {
                            animateCounter(counter);
                            counter.classList.add('counted');
                        }
                    }
                });
            }, observerOptions);

            // Add stagger effect delay to all animate-on-scroll elements based on their index
            // This makes them slide in one after another smoothly
            document.querySelectorAll('.animate-on-scroll').forEach((el, index) => {
                // Base delay + index increment (max out at 0.6s to avoid too much lag)
                el.style.transitionDelay = `${Math.min(index * 0.1, 0.6)}s`; 
                observer.observe(el);
            });

            // --- 3. Number Counter Animation ---
            function animateCounter(el) {
                const target = +el.getAttribute('data-target');
                const duration = 2000; // ms
                const increment = target / (duration / 16); // 60fps
                
                let current = 0;
                
                const updateCount = () => {
                    current += increment;
                    if (current < target) {
                        el.innerText = Math.ceil(current).toLocaleString();
                        requestAnimationFrame(updateCount);
                    } else {
                        el.innerText = target.toLocaleString() + '+';
                    }
                };
                
                updateCount();
            }
        });

        // --- 4. Search Form Handling (Redirect Logic Preserved) ---
        function handleHomeSearch(e) {
            e.preventDefault();
            const btn = document.getElementById('searchBtn');
            const fromVal = document.getElementById('homeFrom').value;
            const toVal = document.getElementById('homeTo').value;

            // Simulate Loading briefly
            btn.classList.add('loading');
            
            setTimeout(() => {
                btn.classList.remove('loading');
                // REDIRECT LOGIC KEPT EXACTLY THE SAME
                const targetUrl = `search.html?from=${encodeURIComponent(fromVal)}&to=${encodeURIComponent(toVal)}`;
                window.location.href = targetUrl;
            }, 800);
        }

        function closeResults() {
            const resultsContainer = document.getElementById('resultsContainer');
            resultsContainer.style.display = 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // --- 5. Redirect to Seats Page (Logic Preserved) ---
        function selectBus(name, price, type) {
            const fromVal = document.getElementById('homeFrom').value;
            const toVal = document.getElementById('homeTo').value;

            // Logic kept the same
            const targetUrl = `seats.html?name=${encodeURIComponent(name)}&price=${price}&isSleeper=${type === 'sleeper'}&from=${encodeURIComponent(fromVal)}&to=${encodeURIComponent(toVal)}`;
            window.location.href = targetUrl;
        }

        // --- 6. Quick Route from Cards (Logic Preserved) ---
        function quickRoute(from, to) {
            document.getElementById('homeFrom').value = from;
            document.getElementById('homeTo').value = to;
            
            showToast(`Selected Route: ${from} to ${to}`, 'info');
            
            // Scroll up to search bar smoothly
            document.querySelector('.hero-section').scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center' 
            });
        }

        // --- 7. Custom Toast Notification System ---
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'custom-toast';
            
            let icon = 'fa-info-circle';
            if(type === 'success') icon = 'fa-check-circle';
            
            toast.innerHTML = `
                <i class="fas ${icon} toast-icon"></i>
                <div class="toast-body fw-bold small text-dark">${message}</div>
            `;
            
            container.appendChild(toast);
            
            // Trigger animation
            requestAnimationFrame(() => {
                toast.classList.add('show');
            });
            
            // Remove after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 500); // Wait for transition
            }, 3000);
        }
    </script>
</body>
</html>