<?php
session_start();
require_once '../../auth/config.php';
include_once '../../view/navbar.php';

// Periksa apakah user sudah login
if (!isset($_SESSION['UserID'])) {
    header('Location: ../../auth/login.html');
    exit;
}

// Ambil orderId dari session
$orderId = isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : null;

// Jika tidak ada orderId, mungkin redirect ke halaman lain atau tampilkan pesan
if (!$orderId) {
    die("No order found. Please complete a purchase first.");
}

$userId = $_SESSION['UserID'];
$order = null;
$orderItems = [];

try {
    // Ambil data order
    $stmt = $pdo->prepare("
        SELECT o.OrderID, o.OrderDate, o.TotalAmount, o.PaymentStatus 
        FROM orders o
        WHERE o.OrderID = ? AND o.UserID = ?
    ");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        die("Order not found.");
    }

    // Ambil items dari order
    $stmt = $pdo->prepare("
        SELECT oi.*, tt.TypeName, tt.Price, c.Title AS ConcertTitle, c.ConcertDate, c.Venue, c.ImageURL
        FROM orderitems oi
        JOIN tickettypes tt ON oi.TicketTypeID = tt.TicketTypeID
        JOIN concerts c ON tt.ConcertID = c.ConcertID
        WHERE oi.OrderID = ?
    ");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Format tanggal
function formatDate($date) {
    return date('d F Y', strtotime($date));
}

// Format waktu
function formatTime($time) {
    return date('H:i', strtotime($time));
}

// Format rupiah
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Fungsi untuk generate unique ticket code
function generateUniqueTicketCode($orderItemId, $index) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $resultCode = '';
    $length = 12;
    for ($i = 0; $i < $length; $i++) {
        $resultCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return 'CT-' . $orderItemId . '-' . $index . '-' . substr($resultCode, 0, 4) . '-' . substr($resultCode, 4, 4) . '-' . substr($resultCode, 8, 4);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../../assets/images/logo_1.png" type="image/x-icon">
  <title>Your Tickets - ConcertTix</title>

  <!-- Bootstrap CSS & Icons -->
  <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
  <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></noscript>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- jsPDF for PDF generation -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  
 <style>
    /* VARIABLES */
    :root {
      --primary-purple: #8a4fff;
      --primary-orange: #ff6600;
      --gradient-purple: linear-gradient(135deg, #8a4fff 0%, #5e2de0 100%);
      --text-dark: #333;
      --text-light: #f8f9fa;
      --background-light: #f5f0ff;
      --background-white: #ffffff;
      --border-light: rgba(138, 79, 255, 0.2);
      --shadow-soft: 0 10px 30px rgba(138, 79, 255, 0.1);
      --shadow-medium: 0 5px 15px rgba(138, 79, 255, 0.2);
      --navbar-padding-x: 1.5rem;
      --platinum-color: linear-gradient(135deg, #00c6fb 0%, #005bea 50%, #8a4fff 100%);
      --vip-color: #ffc107;
      --regular-color: #20c997;
    }
    
    /* BASE STYLES */
    body {
      background: linear-gradient(to bottom, var(--background-light) 0%, var(--background-white) 100%);
      overflow-x: hidden;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: var(--text-dark);
    }
    
    .container {
      max-width: 1400px;
    }
        .container1 {
      max-width: 100%; 
      padding-left: 15px; 
      padding-right: 15px;
    }
    
    /* Navbar Styles */
    .navbar {
      transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
      box-shadow: 0 2px 15px rgba(138, 79, 255, 0.1);
      padding: 0.5rem 0;
    }
    
    .navbar .container {
      max-width: 100%;
      padding-left: 15px;
      padding-right: 15px;
    }
    
    .navbar.scrolled {
      padding: 0.5rem 0 !important;
      background: rgba(138, 79, 255, 0.98) !important;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25) !important;
      backdrop-filter: blur(10px);
    }
    
    .navbar.scrolled .navbar-brand span:first-child {
      color: white !important;
    }
    
    .navbar.scrolled .navbar-brand span:last-child {
      color: var(--primary-orange) !important;
    }
    
    /* Navbar brand */
    .navbar-brand {
      font-size: 1.6rem;
      font-weight: 700;
      transition: all 0.3s ease;
      margin-right: 1rem;
    }
    
    .navbar-brand:hover {
      transform: scale(1.03);
    }
    
    /* Search input styles */
    .navbar:not(.scrolled) .input-group {
      border: 2px solid var(--primary-purple);
      border-radius: 1.5rem;
      overflow: hidden;
    }
    
    .navbar:not(.scrolled) .form-control {
      border-left: none;
      border-color: var(--primary-purple);
      background-color: white;
    }
    
    /* Button styles */
    .btn-outline-purple {
      color: var(--primary-purple);
      border-color: var(--primary-purple);
      transition: all 0.3s ease;
    }
    
    .btn-outline-purple:hover {
      background: var(--primary-purple);
      color: white;
    }
    
    .btn-purple-gradient {
      background: var(--gradient-purple);
      color: white;
      border: none;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }
    
    .btn-purple-gradient:hover {
      background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(138, 79, 255, 0.3);
    }
    
    /* Hover effect for gradient button */
    .btn-purple-gradient::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.473), transparent);
      transition: 0.5s;
    }
    
    .btn-purple-gradient:hover::before {
      left: 100%;
    }
    
    /* Style for scrolled navbar buttons */
    .navbar.scrolled .btn-outline-secondary,
    .navbar.scrolled .btn-outline-purple {
      border-color: rgba(255, 255, 255, 0.5);
      color: rgba(255, 255, 255, 0.8);
    }
    
    .navbar.scrolled .btn-outline-secondary:hover,
    .navbar.scrolled .btn-outline-purple:hover {
      border-color:  var(--primary-orange);
      background: var(--primary-orange);
      color: white;
    }
    
    .navbar.scrolled .btn-purple-gradient {
      background: var(--primary-orange);
      color: white;
    }
    
    .navbar.scrolled .btn-purple-gradient:hover {
      background: #ff5500;
      color: white;
      box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
    }
    
    /* Ikon search di navbar */
    .navbar:not(.scrolled) .input-group-text {
      background-color: white;
      color: var(--primary-purple);
      border-right: none;
      border-color: var(--primary-purple);
    }
    
    .navbar.scrolled .input-group-text {
      color: rgb(228, 221, 221) !important;
      background-color: rgba(255, 255, 255, 0.1) !important;
      border-color: rgba(255, 255, 255, 0.5) !important;
    }
    
    /* Placeholder text */
    .navbar:not(.scrolled) .form-control::placeholder {
      color: #6c757d;
    }
    
    .navbar.scrolled .form-control::placeholder {
      color: rgba(255, 255, 255, 0.8) !important;
    }
    
    .navbar.scrolled .form-control {
      border-color: rgba(255, 255, 255, 0.5);
      color: white;
      background-color: rgba(255, 255, 255, 0.1);
    }
    
    .navbar.scrolled .input-group:hover .input-group-text {
      background-color: rgba(255, 255, 255, 0.2) !important;
      color: white !important;
    }
    
    /* Hover state untuk search di navbar putih */
    .navbar:not(.scrolled) .input-group:hover .input-group-text {
      background-color: var(--primary-purple);
      color: white;
    }
    
    .navbar:not(.scrolled) .input-group:hover .form-control {
      border-color: var(--primary-purple);
      color: #333;
    }
    
    .navbar:not(.scrolled) .form-control {
      border-color: var(--primary-purple);
      color: #333;
    }
    
    /* Navbar toggler */
    .navbar-toggler {
      border: none;
      padding: 0.5rem;
      font-size: 1.25rem;
      display: block !important;
    }
    
    .navbar-toggler:focus {
      box-shadow: none;
      outline: none;
    }
    
    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(138, 79, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    .navbar.scrolled .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }
    
    /* Offcanvas menu styles */
    .offcanvas-start {
      width: 250px;
      background: linear-gradient(to bottom, #f5f0ff 0%, #ffffff 100%);
    }
    
    .nav-link {
      padding: 0.5rem 1rem;
      border-radius: 0.25rem;
      margin-bottom: 0.25rem;
      transition: all 0.3s ease;
    }
    
    .nav-link:hover {
      background-color: rgba(138, 79, 255, 0.1);
      color: var(--primary-purple);
    }
    
    .offcanvas-body .nav-link {
      position: relative;
      overflow: hidden;
    }
    
    .offcanvas-body .nav-link::before {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 0;
      height: 2px;
      background: var(--primary-purple);
      transition: width 0.3s ease;
    }
    
    .offcanvas-body .nav-link:hover::before {
      width: 100%;
    }
    
    /* Checkout Summary Styles */
    .checkout-summary {
      background: white;
      border-radius: 16px;
      padding: 2rem;
      margin-bottom: 2rem;
      box-shadow: var(--shadow-soft);
      border: 1px solid var(--border-light);
    }
    
    .summary-header {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    
    .summary-header i {
      font-size: 1.5rem;
      color: var(--primary-purple);
      margin-right: 1rem;
    }
    
    .summary-concert {
      margin-bottom: 2rem;
      padding-bottom: 1.5rem;
      border-bottom: 1px dashed var(--border-light);
    }
    
    .summary-concert:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .summary-concert-name {
      font-size: 1.25rem;
      font-weight: 600;
      color: var(--primary-purple);
      margin-bottom: 1rem;
    }
    
    .summary-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
    }
    
    .summary-item {
      padding: 1.25rem;
      border-radius: 12px;
      background: rgba(138, 79, 255, 0.05);
      border-left: 4px solid var(--primary-purple);
    }
    
    .summary-item strong {
      display: block;
      color: var(--primary-purple);
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
      text-transform: uppercase;
    }
    
    .summary-total {
      margin-top: 2rem;
      padding-top: 1.5rem;
      border-top: 1px dashed var(--border-light);
      text-align: right;
    }
    
    .total-price {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-orange);
    }

    /* Ticket Grid Styles */
    .tickets-container {
      margin: 3rem auto;
    }

    .tickets-header {
      text-align: center;
      margin-bottom: 2.5rem;
    }

    .tickets-header h1 {
      font-weight: 700;
      color: var(--primary-purple);
      margin-bottom: 1rem;
    }

    .tickets-header p {
      font-size: 1.1rem;
      color: #666;
      max-width: 700px;
      margin: 0 auto;
    }

    .tickets-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 2rem;
      margin-bottom: 3rem;
    }

    /* Ticket Styles */
    .ticket-container {
      background: var(--background-white);
      border-radius: 16px;
      box-shadow: var(--shadow-medium);
      overflow: hidden;
      position: relative;
      transition: all 0.3s ease;
    }

    .ticket-container:hover {
      transform: translateY(-5px);
      box-shadow: 0 25px 50px rgba(138, 79, 255, 0.2);
    }

    .ticket-header {
      padding: 1.5rem;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    /* Different header colors based on ticket class */
    .ticket-header.platinum {
       background: var(--platinum-color);
      color: var(--text-light);
      position: relative;
      overflow: hidden;
    }

    .ticket-header.vip {
      background: linear-gradient(135deg, var(--vip-color) 0%, #ff9500 100%);
      color: #333;
    }

    .ticket-header.regular {
      background: linear-gradient(135deg, var(--regular-color) 0%, #13855c 100%);
      color: var(--text-light);
    }

    .ticket-header::before {
      content: '';
      position: absolute;
      top: -50%;
      left: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
      transform: rotate(30deg);
      opacity: 0.3;
    }

    .ticket-header h2 {
      margin-bottom: 0.5rem;
      font-weight: 700;
      font-size: 1.5rem;
      position: relative;
    }

    .ticket-header .ticket-class {
      font-size: 0.9rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      padding: 0.25rem 0.75rem;
      border-radius: 50px;
      display: inline-block;
      margin-bottom: 0.5rem;
    }

    .ticket-header.platinum .ticket-class {
      background-color: rgba(0, 0, 0, 0.1);
    }

    .ticket-header.vip .ticket-class {
      background-color: rgba(0, 0, 0, 0.1);
    }

    .ticket-header.regular .ticket-class {
      background-color: rgba(255, 255, 255, 0.2);
    }

    .ticket-body {
      padding: 1.5rem;
      position: relative;
    }

    .ticket-info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }

    .ticket-info-item {
      background-color: rgba(138, 79, 255, 0.05);
      padding: 0.75rem;
      border-radius: 8px;
      border-left: 3px solid var(--primary-purple);
    }

    .ticket-info-item strong {
      display: block;
      color: var(--primary-purple);
      margin-bottom: 0.25rem;
      font-size: 0.8rem;
      text-transform: uppercase;
    }

    .ticket-code-section {
      text-align: center;
      margin: 1.5rem 0;
      padding: 1rem;
      background-color: var(--background-light);
      border-radius: 8px;
      border: 1px dashed rgba(138, 79, 255, 0.3);
    }

    .ticket-code-section h4 {
      color: var(--primary-purple);
      margin-bottom: 0.75rem;
      font-weight: 600;
    }

    .uniqueTicketCode {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--primary-orange);
      letter-spacing: 1px;
      padding: 0.5rem 1rem;
      border: 2px dashed var(--primary-orange);
      border-radius: 8px;
      display: inline-block;
      background-color: var(--background-white);
    }

    .qr-code-container {
      text-align: center;
      margin-bottom: 1.5rem;
    }

    .qr-code-container img {
      max-width: 150px;
      border: 1px solid var(--border-light);
      padding: 0.5rem;
      border-radius: 8px;
      background-color: var(--background-white);
    }

    .ticket-actions {
      text-align: center;
      margin-top: 1.5rem;
    }

    .btn-ticket-action {
      background: var(--gradient-purple);
      color: var(--text-light);
      border: none;
      padding: 0.5rem 1.25rem;
      border-radius: 50px;
      font-weight: 600;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 120px;
    }

    .btn-ticket-action:hover {
      background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
      color: var(--text-light);
      transform: translateY(-3px);
    }

    .btn-ticket-action i {
      margin-right: 6px;
    }

    .action-buttons {
      display: flex;
      justify-content: space-between;
      margin-top: 2rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    
 /* Footer Styles */
    .footer-link {
      transition: var(--transition);
      display: inline-block;
    }
    
    .footer-link:hover {
      color: var(--primary-purple) !important;
      transform: translateX(5px);
    }
    
    /* Social icons */
    .social-icon {
      transition: var(--transition);
      cursor: pointer;
    }
    
    .social-icon:hover {
      transform: scale(1.2) translateY(-3px);
      color: var(--primary-purple) !important;
    }
    
    /* Wave Background - Fixed Version */
    .wave-bg-footer {
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none"><path fill="%238a4fff" fill-opacity="0.05" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
      background-repeat: no-repeat;
      background-position: bottom center;
      background-size: cover;
      height: 100%;
    }
    
    /* Wave Background - Fixed Version */
    .wave-bg-footer {
      background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none"><path fill="%238a4fff" fill-opacity="0.05" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
      background-repeat: no-repeat;
      background-position: bottom center;
      background-size: cover;
      height: 100%;
    }
    
    .social-icon {
      transition: var(--transition);
      cursor: pointer;
    }
    
    .social-icon:hover {
      transform: scale(1.2) translateY(-3px);
      color: var(--primary-purple) !important;
    }

    /* Floating elements */
    .floating-element {
      position: absolute;
      color: #8a4fff;
      font-size: 1.5rem;
      animation: float 6s infinite ease-in-out;
      opacity: 0.6;
      z-index: -1;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(10deg); }
    }

    /* Animations buttton in navbar and ofensif */
    @keyframes floatLink {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-2px); }
    }

    @keyframes subtleShake {
      0%, 100% { transform: translateX(0) rotate(0deg); }
      25% { transform: translateX(-2px) rotate(-0.5deg); }
      50% { transform: translateX(2px) rotate(0.5deg); }
      75% { transform: translateX(-1px) rotate(-0.3deg); }
    }

    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }

    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }

    /* Apply animations */
    .menu-item-float {
      animation: floatLink 4s infinite ease-in-out;
    }

    .subtle-shake {
      animation: subtleShake 5s infinite ease-in-out;
    }

    .pulse-animation {
      animation: pulse 2s infinite ease-in-out;
    }

    .float-animation {
      animation: float 3s infinite ease-in-out;
    }

    .social-float {
      animation: float 4s infinite ease-in-out;
    }

    /* Delay animations for staggered effect */
    .menu-item-float:nth-child(1) { animation-delay: 0.1s; }
    .menu-item-float:nth-child(2) { animation-delay: 0.2s; }
    .menu-item-float:nth-child(3) { animation-delay: 0.3s; }
    .menu-item-float:nth-child(4) { animation-delay: 0.4s; }

    .social-float:nth-child(1) { animation-delay: 0.1s; }
    .social-float:nth-child(2) { animation-delay: 0.2s; }
    .social-float:nth-child(3) { animation-delay: 0.3s; }
    .social-float:nth-child(4) { animation-delay: 0.4s; }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .tickets-grid {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        flex-direction: column;
      }
      
      .action-buttons a, 
      .action-buttons button {
        width: 100%;
      }
    }

    /* Print-specific styles */
    @media print {
      body * {
        visibility: hidden;
      }
      .ticket-container, .ticket-container * {
        visibility: visible;
      }
      .ticket-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        box-shadow: none;
        border-radius: 0;
      }
      .ticket-actions {
        display: none !important;
      }
      .no-print {
        display: none !important;
      }
    }
  </style>
</head>
<body>

  <!-- Floating decorative elements -->
  <div class="floating-element" style="top: 20%; left: 5%; animation-delay: 0s;">♪</div>
  <div class="floating-element" style="top: 70%; left: 90%; animation-delay: 1s; color: var(--primary-orange);">♫</div>
  <div class="floating-element" style="top: 40%; left: 85%; animation-delay: 2s;">♩</div>

  <!-- Tickets Content -->
  <div class="container tickets-container">
    <div class="tickets-header">
      <h1>Your Concert Tickets</h1>
      <p>Thank you for your purchase! Below are all your tickets.</p>
    </div>

    <!-- Checkout Summary Section -->
    <div class="checkout-summary">
      <div class="summary-header">
        <i class="bi bi-receipt"></i>
        <h3>Order Summary</h3>
      </div>
      
      <div class="summary-item">
        <strong>Order Number</strong>
        <span><?php echo $order['OrderID']; ?></span>
      </div>
      <div class="summary-item">
        <strong>Purchase Date</strong>
        <span><?php echo formatDate($order['OrderDate']); ?></span>
      </div>
      <div class="summary-item">
        <strong>Payment Status</strong>
        <span class="badge bg-success"><?php echo $order['PaymentStatus']; ?></span>
      </div>
      
      <?php
      // Kelompokkan item per konser
      $concerts = [];
      foreach ($orderItems as $item) {
          $concertId = $item['ConcertTitle']; // Kita gunakan title sebagai kunci, asumsi unik
          if (!isset($concerts[$concertId])) {
              $concerts[$concertId] = [
                  'title' => $item['ConcertTitle'],
                  'date' => $item['ConcertDate'],
                  'venue' => $item['Venue'],
                  'items' => []
              ];
          }
          $concerts[$concertId]['items'][] = $item;
      }
      ?>
      
      <?php foreach ($concerts as $concert): ?>
        <!-- Concert Summary -->
        <div class="summary-concert">
          <div class="summary-concert-name"><?php echo $concert['title']; ?></div>
          <div class="summary-grid">
            <div class="summary-item">
              <strong>Tickets Purchased</strong>
              <span>
                <?php 
                $ticketCounts = [];
                foreach ($concert['items'] as $item) {
                    $type = $item['TypeName'];
                    $qty = $item['Quantity'];
                    if (!isset($ticketCounts[$type])) {
                        $ticketCounts[$type] = 0;
                    }
                    $ticketCounts[$type] += $qty;
                }
                $ticketStrings = [];
                foreach ($ticketCounts as $type => $count) {
                    $ticketStrings[] = "$count $type";
                }
                echo implode(', ', $ticketStrings);
                ?>
              </span>
            </div>
            <div class="summary-item">
              <strong>Date & Time</strong>
              <span><?php echo formatDate($concert['date']) . ', ' . formatTime($concert['date']); ?></span>
            </div>
            <div class="summary-item">
              <strong>Venue</strong>
              <span><?php echo $concert['venue']; ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
      
      <div class="summary-total">
        <div class="total-price">Total Paid: <?php echo formatRupiah($order['TotalAmount']); ?></div>
      </div>
    </div>

    <div class="tickets-grid" id="ticketsGrid">
      <?php foreach ($orderItems as $index => $item): ?>
        <?php for ($i = 0; $i < $item['Quantity']; $i++): ?>
          <div class="ticket-container" id="ticket-<?php echo $item['OrderItemID'] . '-' . $i; ?>">
            <div class="ticket-header <?php echo strtolower($item['TypeName']); ?>">
              <div class="ticket-class"><?php echo strtoupper($item['TypeName']); ?></div>
              <h2><?php echo $item['ConcertTitle']; ?></h2>
              <p>E-Ticket</p>
            </div>
            <div class="ticket-body">
              <div class="ticket-info-grid">
                <div class="ticket-info-item">
                  <strong>Date & Time</strong>
                  <span><?php echo formatDate($item['ConcertDate']) . ', ' . formatTime($item['ConcertDate']); ?></span>
                </div>
                <div class="ticket-info-item">
                  <strong>Venue</strong>
                  <span><?php echo $item['Venue']; ?></span>
                </div>
                <div class="ticket-info-item">
                  <strong>Seat</strong>
                  <span>General Admission</span>
                </div>
                <div class="ticket-info-item">
                  <strong>Price</strong>
                  <span><?php echo formatRupiah($item['Price']); ?></span>
                </div>
              </div>

              <div class="ticket-code-section">
                <h4>Unique Ticket Code</h4>
                <div class="uniqueTicketCode"><?php echo generateUniqueTicketCode($item['OrderItemID'], $i); ?></div>
              </div>

              <div class="qr-code-container">
                <p class="mb-2">Scan this QR code at the entrance</p>
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode(generateUniqueTicketCode($item['OrderItemID'], $i) . ' | ' . $item['ConcertTitle']); ?>" alt="QR Code">
              </div>

              <div class="ticket-actions">
                <a href="#" class="btn-ticket-action download-ticket" data-ticket-id="<?php echo $item['OrderItemID'] . '-' . $i; ?>"><i class="bi bi-download"></i> Download Ticket</a>
              </div>
            </div>
          </div>
        <?php endfor; ?>
      <?php endforeach; ?>
    </div>

    <div class="action-buttons">
      <a href="../../public/" class="btn btn-outline-purple">
        <i class="bi bi-house-door me-2"></i> Back to Home
      </a>
      <button class="btn btn-purple-gradient" id="downloadAllButton">
        <i class="bi bi-download me-2"></i> Download All Tickets
      </button>
    </div>
  </div>

<!-- Footer -->
  <footer class="bg-light py-5 position-relative overflow-hidden">
    <!-- Wave Background -->
    <div class="position-absolute bottom-0 start-0 w-100 h-100" style="z-index: 0; overflow: hidden;">
      <div class="position-absolute bottom-0 start-0 w-100 h-100 wave-bg-footer"></div>
    </div>

    <div class="container position-relative" style="z-index: 1;">
      <div class="row g-4">
        <!-- Left - Brand Info -->
        <div class="col-lg-4 mb-4">
          <div class="footer-brand">
            <h3 class="fw-bold mb-3" style="background: linear-gradient(90deg, #8a4fff, #ff6600); -webkit-background-clip: text; background-clip: text; color: transparent;">
              Concert<span style="color: #ff6600;">Tix</span>
            </h3>
            <p class="text-muted mb-4">The easiest way to buy tickets for your favorite concerts and events worldwide.</p>
            <div class="d-flex gap-3 social-icons">
              <a href="https://www.facebook.com/groups/3288439241475430/" class="social-icon d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease;">
                <i class="bi bi-facebook text-purple"></i>
              </a>
              <a href="https://www.instagram.com/xtoph.y?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="social-icon d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease;">
                <i class="bi bi-instagram text-purple"></i>
              </a>
              <a href="https://x.com/home" class="social-icon d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease;">
                <i class="bi bi-twitter-x text-purple"></i>
              </a>
              <a href="https://www.youtube.com/@xant_san" class="social-icon d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease;">
                <i class="bi bi-youtube text-purple"></i>
              </a>
            </div>
          </div>
        </div>
        
        <!-- Middle - Links -->
        <div class="col-md-6 col-lg-4 mb-4">
          <div class="row g-4">
            <!-- Company -->
            <div class="col-sm-6">
              <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff;">Company</h6>
              <ul class="list-unstyled">
                <li class="mb-2">
                  <a href="../../public/about.php" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> About Us
                  </a>
                </li>
                <li class="mb-2">
                  <a href="../../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Careers
                  </a>
                </li>
                <li class="mb-2">
                  <a href="https://api.whatsapp.com/send?phone=6285156473714&text=Hi+Sir+Admin+ConcertTix" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Contact Us
                  </a>
                </li>
                <li class="mb-2">
                  <a href="../../public/privacy_policy.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Privacy Policy
                  </a>
                </li>
                <li>
                  <a href="../../public/tems_of_service.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Terms of Service
                  </a>
                </li>
              </ul>
            </div>
            
            <!-- Support -->
            <div class="col-sm-6">
              <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff;">Support</h6>
              <ul class="list-unstyled">
                <li class="mb-2">
                  <a href="../../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Help Center
                  </a>
                </li>
                <li class="mb-2">
                  <a href="../../public/faqs.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> FAQs
                  </a>
                </li>
                <li class="mb-2">
                  <a href="../../public/refund_policy.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Refund Policy
                  </a>
                </li>
                <li>
                  <a href="../../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> Report an Issue
                  </a>
                </li>
              </ul>
            </div>
          </div>
        </div>
        
        <!-- Right - Newsletter -->
        <div class="col-md-6 col-lg-4 mb-4">
          <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff;">Stay Updated</h6>
          <p class="text-muted mb-3">Subscribe to our newsletter for concert updates and exclusive offers.</p>
          
          <form class="mb-4">
            <div class="input-group mb-3">
              <input type="email" class="form-control" placeholder="Your email" style="border-right: 0; border-color: #8a4fff;">
              <button class="btn btn-purple-gradient" type="submit" style="border-left: 0;">
                <i class="bi bi-send-fill"></i>
              </button>
            </div>
          </form>
          
          <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff;">Download Our App</h6>
          <div class="d-flex gap-2 app-download">
            <a href="../../lost/404_page.html" class="hover-grow" style="transition: all 0.3s ease;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Download_on_the_App_Store_Badge.svg/1280px-Download_on_the_App_Store_Badge.svg.png" alt="App Store" class="img-fluid rounded" style="height: 40px;">
            </a>
            <a href="../../lost/404_page.html" class="hover-grow" style="transition: all 0.3s ease;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/78/Google_Play_Store_badge_EN.svg/1280px-Google_Play_Store_badge_EN.svg.png" alt="Google Play" class="img-fluid rounded" style="height: 40px;">
            </a>
          </div>
        </div>
      </div>
      
      <hr class="my-4" style="border-color: rgba(138, 79, 255, 0.1);">
      
      <div class="row">
        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
          <p class="mb-0 text-muted small">© 2025 ConcertTix. All rights reserved.</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <div class="d-flex justify-content-center justify-content-md-end gap-3">
            <a href="../../public/privacy_policy.html" class="text-muted small" style="text-decoration: none;">Privacy Policy</a>
            <a href="../../public/tems_of_service.html" class="text-muted small" style="text-decoration: none;">Terms of Service</a>
            <a href="../../lost/404_page.html" class="text-muted small" style="text-decoration: none;">Sitemap</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Custom Scripts -->
  <script>
    // Fungsi untuk generate single PDF
    async function generateSinglePDF(ticketId) {
      const { jsPDF } = window.jspdf;
      const ticketContainer = document.querySelector(`#ticket-${ticketId}`);
      
      // Create a clone of the ticket container for PDF generation
      const clone = ticketContainer.cloneNode(true);
      clone.style.width = '700px';
      clone.style.margin = '0';
      clone.style.boxShadow = 'none';
      
      // Remove the actions buttons from the clone
      const actions = clone.querySelector('.ticket-actions');
      if (actions) actions.remove();
      
      // Add a print date to the clone
      const printDate = document.createElement('div');
      printDate.style.textAlign = 'center';
      printDate.style.marginTop = '20px';
      printDate.style.fontSize = '0.9rem';
      printDate.style.color = '#666';
      printDate.textContent = `Printed on: ${new Date().toLocaleString()}`;
      clone.querySelector('.ticket-body').appendChild(printDate);
      
      // Append the clone to the body temporarily
      document.body.appendChild(clone);
      
      // Generate PDF
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a5'
      });
      
      // Use html2canvas to capture the ticket
      const canvas = await html2canvas(clone, {
        scale: 2,
        logging: false,
        useCORS: true,
        allowTaint: true
      });
      
      // Remove the clone from the body
      document.body.removeChild(clone);
      
      // Add the image to PDF
      const imgData = canvas.toDataURL('image/png');
      const imgWidth = 148; // A5 width in mm
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      
      pdf.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
      
      // Save the PDF
      pdf.save(`ConcertTix_${ticketId}_${document.querySelector(`#ticket-${ticketId} .uniqueTicketCode`).textContent}.pdf`);
    }

    // Fungsi untuk generate all tickets as one PDF
    async function generateAllPDFs() {
      const { jsPDF } = window.jspdf;
      const pdf = new jsPDF({
        orientation: 'portrait',
        unit: 'mm',
        format: 'a4'
      });
      
      // Get all ticket containers
      const ticketContainers = document.querySelectorAll('.ticket-container');
      
      // Process each ticket one by one
      for (let i = 0; i < ticketContainers.length; i++) {
        const ticketContainer = ticketContainers[i];
        const ticketId = ticketContainer.id.split('-')[1];
        
        // Create a clone of the ticket container for PDF generation
        const clone = ticketContainer.cloneNode(true);
        clone.style.width = '700px';
        clone.style.margin = '0';
        clone.style.boxShadow = 'none';
        
        // Remove the actions buttons from the clone
        const actions = clone.querySelector('.ticket-actions');
        if (actions) actions.remove();
        
        // Add a print date to the clone
        const printDate = document.createElement('div');
        printDate.style.textAlign = 'center';
        printDate.style.marginTop = '20px';
        printDate.style.fontSize = '0.9rem';
        printDate.style.color = '#666';
        printDate.textContent = `Printed on: ${new Date().toLocaleString()}`;
        clone.querySelector('.ticket-body').appendChild(printDate);
        
        // Append the clone to the body temporarily
        document.body.appendChild(clone);
        
        // Use html2canvas to capture the ticket
        const canvas = await html2canvas(clone, {
          scale: 2,
          logging: false,
          useCORS: true,
          allowTaint: true
        });
        
        // Remove the clone from the body
        document.body.removeChild(clone);
        
        // Add the image to PDF
        const imgData = canvas.toDataURL('image/png');
        const imgWidth = 180; // A4 width in mm (smaller for margins)
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        
        // Add new page for each ticket after the first one
        if (i > 0) {
          pdf.addPage();
        }
        
        // Center the ticket on the page
        const xPos = (210 - imgWidth) / 2; // A4 width is 210mm
        const yPos = (297 - imgHeight) / 2; // A4 height is 297mm
        
        pdf.addImage(imgData, 'PNG', xPos, yPos, imgWidth, imgHeight);
      }
      
      // Save the PDF
      pdf.save(`ConcertTix_All_Tickets_${new Date().toISOString().slice(0,10)}.pdf`);
    }

    // Initialize when DOM is loaded
    window.addEventListener('DOMContentLoaded', () => {
      // Add event listeners to all download buttons
      document.querySelectorAll('.download-ticket').forEach(button => {
        button.addEventListener('click', function(e) {
          e.preventDefault();
          const ticketId = this.getAttribute('data-ticket-id');
          generateSinglePDF(ticketId);
        });
      });
      
      // Add event listener for download all button
      const downloadAllButton = document.getElementById('downloadAllButton');
      if (downloadAllButton) {
        downloadAllButton.addEventListener('click', (e) => {
          e.preventDefault();
          generateAllPDFs();
        });
      }
    });
  </script>
</body>
</html>