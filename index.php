<?php 
// 1. Include the database connection and start session
include 'includes/db.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartBus - Travel Simplified</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
            font-family: 'Plus Jakarta Sans', sans-serif;
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
            text-decoration: none;
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

        /* --- Search Bar --- */
        .search-container {
            background: var(--glass-bg);
            backdrop-filter: blur(24px);
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

        /* --- Hero Image --- */
        .hero-img-wrapper { position: relative; }
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
            animation: float 5s ease-in-out infinite;
        }

        /* --- Features & Stats --- */
        .stats-section {
            background: white;
            border-radius: var(--radius-xl);
            padding: 60px 0;
            box-shadow: var(--shadow-md);
            margin-bottom: 100px;
        }
        
        .stat-number { 
            font-size: 3.5rem; 
            font-weight: 800; 
            color: var(--text-dark); 
            display: block; 
            line-height: 1; 
            background: linear-gradient(180deg, var(--text-dark) 0%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .feature-card {
            background: white;
            padding: 40px;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            height: 100%;
        }
        .feature-card:hover { transform: translateY(-8px); box-shadow: var(--shadow-lg); }

        .icon-box-lg {
            width: 64px; height: 64px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 18px;
            display: flex;
            align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 24px;
        }

        /* --- Footer --- */
        footer { background: white; padding: 80px 0 40px; border-top: 1px solid #eee; }
        .footer-heading { font-weight: 700; margin-bottom: 24px; }
        .footer-link { display: block; color: var(--text-muted); text-decoration: none; margin-bottom: 14px; }
        .footer-link:hover { color: var(--primary); }

        #toast-container { position: fixed; bottom: 30px; right: 30px; z-index: 9999; }
        .custom-toast {
            background: white; border-left: 5px solid var(--primary);
            padding: 20px 25px; border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            display: flex; align-items: center; gap: 15px;
            transform: translateX(120%); transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .custom-toast.show { transform: translateX(0); }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-bus-alt"></i> 
                <span>SmartBus</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <div class="d-flex gap-3 mt-3 mt-lg-0 align-items-center">
                    <?php if(isset($_SESSION['user_name'])): ?>
                        <span class="me-2 fw-bold">Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <a href="admin/dashboard.php" class="btn btn-nav">Dashboard</a>
                        <a href="includes/logout.php" class="btn btn-nav primary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-nav">Login</a>
                        <a href="register.php" class="btn btn-nav primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <section class="hero-section">
        <div class="hero-bg-blob blob-1"></div>
        <div class="hero-bg-blob blob-2"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="hero-badge">
                        <i class="fas fa-bolt"></i> New: Live GPS Tracking
                    </div>
                    <h1 class="hero-title">Travel Smart,<br>Arrive Happy.</h1>
                    <p class="hero-subtitle">
                        Find the best bus routes, book sun-free seats, and track your journey in real-time. The modern way to travel by road.
                    </p>

                    <div class="search-container">
                        <form id="homeSearchForm" onsubmit="handleHomeSearch(event)">
                            <div class="row g-2">
                                <div class="col-md-5">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-map-marker-alt search-icon"></i>
                                        <input type="text" id="homeFrom" class="form-control form-control-hero" placeholder="From (e.g. Kochi)" required>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="search-input-wrapper">
                                        <i class="fas fa-location-arrow search-icon"></i>
                                        <input type="text" id="homeTo" class="form-control form-control-hero" placeholder="To (e.g. Bangalore)" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" id="searchBtn" class="btn-hero-search">
                                        <span class="btn-text">Search</span>
                                        <i class="fas fa-circle-notch loader-icon"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6 hero-img-wrapper">
                    <div class="route-line"></div>
                    <div class="route-dot dot-start"></div>
                    <div class="route-dot dot-end"></div>
                    <i class="fas fa-bus bus-anim-icon"></i>
                    <img src="https://picsum.photos/seed/busmodern/800/600" class="hero-main-img" alt="Bus Travel">
                    <div class="float-badge">
                        <div class="icon-circle-sm bg-success"><i class="fas fa-sun"></i></div>
                        <div>
                            <div class="small text-muted mb-0 lh-sm">Smart Seats</div>
                            <div class="fw-bold text-dark lh-sm">Avoid Sunlight</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container mb-5">
        <div class="stats-section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4 stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Happy Travelers</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <div class="stat-number">5,000+</div>
                        <div class="stat-label">Daily Trips</div>
                    </div>
                    <div class="col-md-4 stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Cities Connected</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold display-6">Why Choose SmartBus?</h2>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-box-lg"><i class="fas fa-sun"></i></div>
                    <h5 class="fw-bold">Avoid Sunlight</h5>
                    <p class="text-muted mb-0">Our 3D seat maps show you exactly where the sun hits. Pick a shaded seat and travel cool.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-box-lg"><i class="fas fa-map-marked-alt"></i></div>
                    <h5 class="fw-bold">Live Tracking</h5>
                    <p class="text-muted mb-0">Share your live location with family. Track your bus in real-time on map.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="icon-box-lg"><i class="fas fa-ticket-alt"></i></div>
                    <h5 class="fw-bold">Easy Booking</h5>
                    <p class="text-muted mb-0">Book in under 60 seconds. QR-ticket support means no printing required.</p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <a class="navbar-brand mb-3" href="#"><i class="fas fa-bus-alt"></i> <span>SmartBus</span></a>
                    <p class="text-muted">Making road travel smarter, safer, and more comfortable for everyone.</p>
                </div>
                <div class="col-lg-2">
                    <h6 class="footer-heading">Support</h6>
                    <a href="#" class="footer-link">Help Center</a>
                    <a href="#" class="footer-link">Privacy Policy</a>
                </div>
                <div class="col-lg-4">
                    <h6 class="footer-heading">Subscribe</h6>
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Email">
                        <button class="btn btn-primary" style="background:var(--primary); border:none;">Go</button>
                    </div>
                </div>
            </div>
            <div class="border-top mt-5 pt-4 text-center">
                <p class="text-muted small">&copy; 2026 SmartBus Inc. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function handleHomeSearch(e) {
            e.preventDefault();
            const btn = document.getElementById('searchBtn');
            const fromVal = document.getElementById('homeFrom').value;
            const toVal = document.getElementById('homeTo').value;

            btn.classList.add('loading');
            
            setTimeout(() => {
                btn.classList.remove('loading');
                // IMPORTANT: Redirecting to .php instead of .html
                window.location.href = `search.php?from=${encodeURIComponent(fromVal)}&to=${encodeURIComponent(toVal)}`;
            }, 800);
        }
    </script>
</body>
</html>