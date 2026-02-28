🚌 SmartBus - Web-Based Bus Ticket Booking System

## 🚀 Quick Start (Windows)
1. **Database**: Create a database named `bus_booking_system` in phpMyAdmin.
2. **Run**: Double-click `run_server.bat`.
   - It will automatically populate the database.
   - It will start the server and open the app in your browser.

---

SmartBus is a modern, web-based application designed to digitize the inter-city bus booking process. It allows passengers to search for buses, analyze seat sunlight exposure (Smart Seat Selection), and book tickets with QR code integration. It also features a robust Admin Dashboard for fleet and route management.

🌟 Key Features
👤 User Module (Passenger)
Smart Search: Filter buses by Source, Destination, and Date.☀️ Sun Analysis Feature: Unique feature that calculates the sun's position for the journey time to recommend "Shaded" vs "Sunny" seats.Interactive Seat Layout: Visual seat selection for Sleeper and Seater buses.QR-Based E-Tickets: Generates a downloadable ticket with a QR code for easy check-in.Mobile-Responsive UI: Built with Bootstrap 5 for a seamless experience on phones and desktops.🛠 Admin ModuleDashboard: Quick overview of fleet status and booking stats.Fleet Management: Add, Edit, or Delete buses (AC, Non-AC, Sleeper).Route Management: Define sources, destinations, and distances.Booking Oversight: View all passenger reservations.💻 Technology StackFrontend: HTML5, CSS3, JavaScript (ES6+), Bootstrap 5, FontAwesome.Backend: PHP (Procedural).Database: MySQL.Server: Apache (via XAMPP/WAMP).Libraries:suncalc.js (For Sunlight Analysis).qrcode.js (For Ticket QR generation).📂 Project StructurePlaintext/smartbus
│
├── /admin                # Admin Panel Files
│   ├── dashboard.php     # Admin Home
│   ├── manage_bus.php    # Bus CRUD Operations
│   └── manage_route.php  # Route CRUD Operations
│
├── /assets               # Static Assets
│   ├── css/              # Stylesheets
│   ├── js/               # Custom JavaScripts
│   └── img/              # Images
│
├── /database             # SQL Files
│   ├── schema.sql        # Database Creation
│   ├── tables.sql        # Table Structures
│   └── sample_data.sql   # Dummy Data for testing
│
├── /includes             # Shared PHP Files
│   └── db.php            # Database Connection Config
│
├── index.php             # Landing Page
├── login.php             # User/Admin Login
├── register.php          # User Registration
├── search.php            # Search Results Page
├── seats.php             # Seat Selection Interface
├── payment.php           # Payment Gateway Simulation
├── ticket.php            # Final Ticket Generation
└── README.md             # Project Documentation
⚙️ Installation & Setup GuidePrerequisitesInstall XAMPP (or WAMP/MAMP).A Web Browser (Chrome/Firefox/Edge).Step 1: Configure DatabaseOpen XAMPP Control Panel and start Apache and MySQL.Go to http://localhost/phpmyadmin.Click New and create a database named bus_booking_system.Click Import tab.Browse and select the files from the /database folder in this order:tables.sqlsample_data.sqlStep 2: Setup Project FilesCopy the smartbus folder.Paste it into your XAMPP installation directory: C:\xampp\htdocs\ (Windows) or /Applications/XAMPP/htdocs/ (Mac).Step 3: Configure ConnectionOpen includes/db.php.Ensure the credentials match your MySQL setup (default for XAMPP):PHP$servername = "localhost";
$username = "root";
$password = ""; // Leave empty for XAMPP
$dbname = "bus_booking_system";
Step 4: Run the ApplicationUser Portal: Open browser and go to http://localhost/smartbus/index.phpAdmin Portal: Login with Admin credentials at http://localhost/smartbus/login.php🧪 Sample Login CredentialsUse these credentials (from sample_data.sql) to test the system:RoleEmailPasswordAdminadmin@gmail.comadmin123Userrahul@gmail.comrahul123📸 Usage WorkflowRegister/Login: Create an account to start booking.Search: Enter "Bangalore" to "Kochi" (or other sample routes).Select Bus: Choose from the available fleet.Pick Seat: Use the layout to pick a seat. Tip: Change the time to see Sun Analysis in action.Pay: Complete the payment (Simulation).Ticket: Download or Print your QR-enabled ticket.👥 📜 LicenseThis project is developed for academic purposes.