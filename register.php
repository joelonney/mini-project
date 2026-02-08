<?php
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure hashing

    $sql = "INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, 'USER')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $phone, $password);

    if ($stmt->execute()) {
        header("Location: login.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SmartBus</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #E53935;
            --primary-glow: rgba(229, 57, 53, 0.4);
            --primary-hover: #c62828;
            --text-dark: #1B1B1E;
            --text-muted: #8c98a4;
            --white-glass: rgba(255, 255, 255, 0.85);
            --radius: 20px;
            --bg-color: #0f172a;
            --input-height: 58px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* --- Cinematic Background Animation --- */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            /* High quality travel image */
            background-image: url('https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?q=80&w=2574&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            animation: zoomEffect 25s infinite alternate ease-in-out;
        }

        /* Dark Overlay to ensure text readability */
        .bg-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.6));
            z-index: -1;
            backdrop-filter: blur(4px);
        }

        @keyframes zoomEffect {
            0% { transform: scale(1); }
            100% { transform: scale(1.15); }
        }

        /* --- Brand Logo Header --- */
        .navbar-brand {
            font-weight: 800;
            font-size: 1.8rem;
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
            transition: 0.3s;
        }
        
        .navbar-brand:hover {
            transform: scale(1.05);
            color: white;
            text-shadow: 0 0 20px rgba(255,255,255,0.4);
        }

        .navbar-brand i { color: var(--primary); }

        /* --- Auth Card Container --- */
        .auth-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px 20px;
        }

        .auth-card {
            background: var(--white-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 50px 40px;
            border-radius: 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.4);
            max-width: 500px;
            margin: 0 auto;
            width: 100%;
            
            /* Entry Animation */
            opacity: 0;
            transform: translateY(40px);
            animation: slideUpFade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
        }

        @keyframes slideUpFade {
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Input Group Styling --- */
        .input-wrapper {
            position: relative;
            margin-bottom: 24px;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #adb5bd;
            border-right: 1px solid transparent;
            border-radius: 14px 0 0 14px;
            background: rgba(255,255,255,0.5);
            transition: all 0.3s ease;
            z-index: 5;
            font-size: 1.1rem;
        }

        .form-control-custom {
            width: 100%;
            padding: 0 20px 0 54px;
            font-size: 1rem;
            border: 2px solid transparent; /* Prepare for border transition */
            border-radius: 14px;
            background: rgba(255,255,255,0.5);
            transition: all 0.3s ease;
            color: var(--text-dark);
            height: var(--input-height);
            font-weight: 500;
        }

        /* Smooth Focus State */
        .form-control-custom:focus {
            background: white;
            border-color: rgba(229, 57, 53, 0.2);
            box-shadow: 0 0 0 6px rgba(229, 57, 53, 0.1);
            outline: none;
        }

        .form-control-custom:focus ~ i {
            color: var(--primary);
            background: white;
        }

        /* --- Phone Number Group (Enhanced) --- */
        .phone-input-group {
            display: flex;
            align-items: center;
            border: 2px solid transparent;
            border-radius: 14px;
            background: rgba(255,255,255,0.5);
            overflow: hidden;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .phone-input-group:focus-within {
            background: white;
            border-color: rgba(229, 57, 53, 0.2);
            box-shadow: 0 0 0 6px rgba(229, 57, 53, 0.1);
        }

        .country-select-wrapper {
            position: relative;
            min-width: 120px;
            height: 100%;
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.5);
            transition: 0.3s;
        }
        
        /* Change bg on focus of parent */
        .phone-input-group:focus-within .country-select-wrapper {
            background: white;
        }

        .country-select {
            appearance: none;
            -webkit-appearance: none;
            border: none;
            background: transparent;
            padding: 0 35px 0 15px;
            height: var(--input-height);
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
            cursor: pointer;
            outline: none;
            width: 100%;
            z-index: 2;
        }

        .country-select-wrapper::after {
            content: '\f078';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.7rem;
            color: var(--text-muted);
            pointer-events: none;
            transition: 0.3s;
        }

        .phone-input-group:focus-within .country-select-wrapper::after {
            color: var(--primary);
        }

        .separator-line {
            width: 1px;
            height: 24px;
            background-color: #e0e0e0;
            margin: 0 5px;
            flex-shrink: 0;
        }

        .phone-number-input {
            flex-grow: 1;
            border: none;
            background: transparent;
            padding: 0 15px;
            height: var(--input-height);
            font-size: 1rem;
            color: var(--text-dark);
            outline: none;
            font-weight: 500;
        }

        /* --- Button Styling --- */
        .btn-main {
            background-color: var(--primary);
            color: white;
            font-weight: 700;
            padding: 0;
            border: none;
            border-radius: 50px;
            width: 100%;
            font-size: 1.05rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 20px -5px rgba(229, 57, 53, 0.4);
            height: var(--input-height);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-main:hover {
            background-color: var(--primary-hover);
            transform: translateY(-4px);
            box-shadow: 0 15px 30px -5px rgba(229, 57, 53, 0.5);
            color: white;
        }

        .btn-main:active {
            transform: translateY(-1px);
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s ease-in-out infinite;
            margin-right: 10px;
        }
        
        .btn-main.loading .spinner { display: block; }
        .btn-main.loading span { opacity: 0.8; }
        
        @keyframes spin { to { transform: rotate(360deg); } }

        /* --- Social Divider --- */
        .social-divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: var(--text-muted);
            margin: 30px 0;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .social-divider::before, .social-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .social-divider::before { margin-right: 15px; }
        .social-divider::after { margin-left: 15px; }

        /* --- Social Buttons --- */
        .btn-social {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 14px;
            border-radius: 50px;
            border: 2px solid rgba(0,0,0,0.05);
            background: white;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .btn-social:hover {
            background: #f8f9fa;
            border-color: rgba(0,0,0,0.1);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .btn-social i { font-size: 1.2rem; }

        /* --- Links --- */
        .auth-links {
            margin-top: 30px;
            text-align: center;
            font-size: 0.95rem;
            color: var(--text-muted);
        }
        
        .auth-links a {
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 700;
            border-bottom: 2px solid transparent;
            transition: 0.3s;
        }
        
        .auth-links a:hover {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        /* Responsive Tweaks */
        @media (max-width: 576px) {
            .auth-card {
                padding: 30px 20px;
                border-radius: 20px;
            }
            .hero-title { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Animated Background Layer -->
    <div class="bg-animated"></div>
    <div class="bg-overlay"></div>

    <div class="container auth-container">
        
        <!-- Logo -->
        <div class="text-center mb-4">
            <a href="index.html" class="navbar-brand justify-content-center">
                <i class="fas fa-bus-alt"></i> Smart<span>Bus</span>
            </a>
        </div>

        <div class="auth-card">
            <div class="text-center mb-4">
                <h2 class="fw-bold mb-1" style="font-size: 2rem;">Create Account</h2>
                <p class="text-muted small">Start your journey with smarter travel.</p>
            </div>

            <form action="register.php" method="POST">
                <!-- Name Input -->
                <div class="input-wrapper">
                    <input type="text" name="name" class="form-control form-control-custom" placeholder="Full Name" required>
                    <i class="fas fa-user"></i>
                </div>

                <!-- Email Input -->
                <div class="input-wrapper">
                    <input type="email" name="email" class="form-control form-control-custom" placeholder="Email Address" required>
                    <i class="fas fa-envelope"></i>
                </div>

                <!-- Phone Number Group -->
                <div class="phone-input-group">
                    <div class="country-select-wrapper">
                        <select class="country-select" id="countryCode">
                            <option value="+1" selected>US +1</option>
                            <option value="+44">UK +44</option>
                            <option value="+91">IN +91</option>
                            <option value="+61">AU +61</option>
                            <option value="+81">JP +81</option>
                            <option value="+49">DE +49</option>
                        </select>
                    </div>
                    <div class="separator-line"></div>
                    <input type="tel" name="phone" class="phone-number-input" placeholder="Phone Number" required>
                </div>

                <!-- Password Input -->
                <div class="input-wrapper">
                    <input type="password" name="password" class="form-control form-control-custom" placeholder="Password" required>
                    <i class="fas fa-lock"></i>
                </div>

                <!-- Terms Checkbox -->
                <div class="form-check mb-4 small">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label text-muted" for="terms" style="font-weight: 500;">
                        I agree to the <a href="#" class="fw-bold" style="color:var(--primary);">Terms & Conditions</a>
                    </label>
                </div>

                <!-- Submit Button with Loading State -->
                <button type="submit" class="btn-main" id="registerBtn">
                    <div class="spinner"></div>
                    <span>Create Account</span>
                </button>
            </form>

            <!-- Social Login -->
            <div class="social-divider">Or continue with</div>

            <button class="btn-social">
                <i class="fab fa-google" style="color: #DB4437;"></i>
                <span>Google</span>
            </button>
            
            <button class="btn-social">
                <i class="fab fa-apple"></i>
                <span>Apple</span>
            </button>

            <div class="auth-links">
                Already have an account? <a href="login.html">Login here</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Simulating Form Submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('registerBtn');
            const originalText = btn.querySelector('span').innerText;
            
            // Add Loading State
            btn.classList.add('loading');
            btn.querySelector('span').innerText = 'Creating Account...';
            btn.style.pointerEvents = 'none'; // Prevent double click

            // Simulate API Call delay
            setTimeout(() => {
                // Reset button state (for demo purposes)
                btn.classList.remove('loading');
                btn.querySelector('span').innerText = 'Success!';
                btn.style.backgroundColor = '#2e7d32'; // Green for success
                btn.style.pointerEvents = 'all';

                // Optional: Reset form
                // this.reset(); 
                
                console.log("Registration Complete");
            }, 2000);
        });
    </script>
</body>
</html>