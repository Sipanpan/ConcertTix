<?php
session_start();
require_once '../../auth/config.php';
include_once '../../view/navbar.php';

// Aktifkan error reporting untuk development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Redirect jika user tidak login
if (!isset($_SESSION['UserID'])) {
    header('Location: ../../auth/login.html');
    exit;
}

// Inisialisasi variabel
$cartItems = [];
$totalAmount = 0;
$error = null;
$success = false;
$orderId = null;

// Ambil data keranjang untuk ditampilkan
if (isset($_SESSION['UserID'])) {
    $user_id = $_SESSION['UserID'];
    
    try {
        // Get active cart
        $stmt = $pdo->prepare("SELECT CartID FROM carts WHERE UserID = ? ORDER BY CreatedDate DESC LIMIT 1");
        $stmt->execute([$user_id]);
        $cart = $stmt->fetch();

        if ($cart) {
            $cartId = $cart['CartID'];
            
            // Get cart items with concert details
            $query = "
                SELECT 
                    ci.CartItemID,
                    ci.Quantity,
                    tt.TypeName AS TicketType,
                    tt.Price,
                    tt.TicketTypeID,
                    c.Title AS ConcertTitle,
                    c.ConcertDate,
                    c.Venue,
                    c.ImageURL
                FROM cartitems ci
                JOIN tickettypes tt ON ci.TicketTypeID = tt.TicketTypeID
                JOIN concerts c ON tt.ConcertID = c.ConcertID
                WHERE ci.CartID = ?
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([$cartId]);
            $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate total amount
            foreach ($cartItems as $item) {
                $totalAmount += $item['Price'] * $item['Quantity'];
            }
        }
    } catch (Exception $e) {
        $error = "Error loading cart items: " . $e->getMessage();
        error_log("Cart load error: " . $e->getMessage());
    }
}

// Handle form submission for payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    try {
        // Mulai transaksi
        $pdo->beginTransaction();
        
        // 1. Dapatkan cart aktif
        $stmt = $pdo->prepare("SELECT CartID FROM carts WHERE UserID = ? ORDER BY CreatedDate DESC LIMIT 1");
        $stmt->execute([$_SESSION['UserID']]);
        $cart = $stmt->fetch();
        
        if (!$cart) {
            throw new Exception("No active cart found. Please add items to your cart first.");
        }
        
        $cartId = $cart['CartID'];
        
        // 2. Buat order baru
        $stmt = $pdo->prepare("INSERT INTO orders (UserID, OrderDate, TotalAmount, PaymentStatus) 
                               VALUES (?, NOW(), ?, 'paid')");
        $stmt->execute([$_SESSION['UserID'], $totalAmount]);
        $orderId = $pdo->lastInsertId();
        
        // 3. Pindahkan item dari cartitems ke orderitems
        $cartItemsQuery = $pdo->prepare("SELECT ci.*, tt.Price 
                                        FROM cartitems ci
                                        JOIN tickettypes tt ON ci.TicketTypeID = tt.TicketTypeID 
                                        WHERE ci.CartID = ?");
        $cartItemsQuery->execute([$cartId]);
        $cartItemsForOrder = $cartItemsQuery->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($cartItemsForOrder)) {
            throw new Exception("Your cart is empty. Please add items before confirming payment.");
        }
        
        foreach ($cartItemsForOrder as $item) {
            // Insert ke orderitems
            $stmt = $pdo->prepare("INSERT INTO orderitems (OrderID, TicketTypeID, Quantity, Subtotal) 
                                   VALUES (?, ?, ?, ?)");
            $ticketTypeId = $item['TicketTypeID'];
            $quantity = $item['Quantity'];
            $subtotal = $item['Price'] * $quantity;
            
            $stmt->execute([$orderId, $ticketTypeId, $quantity, $subtotal]);
            
            // Kurangi stok tiket
            $updateStmt = $pdo->prepare("UPDATE tickettypes 
                                        SET QuantityAvailable = QuantityAvailable - ? 
                                        WHERE TicketTypeID = ?");
            $updateStmt->execute([$quantity, $ticketTypeId]);
            
            // Verifikasi pengurangan stok
            if ($updateStmt->rowCount() === 0) {
                throw new Exception("Failed to update stock for ticket type: $ticketTypeId");
            }
        }
        
        // 4. Hapus cartitems
        $deleteStmt = $pdo->prepare("DELETE FROM cartitems WHERE CartID = ?");
        $deleteStmt->execute([$cartId]);
        
        // 5. Hapus cart
        $deleteCartStmt = $pdo->prepare("DELETE FROM carts WHERE CartID = ?");
        $deleteCartStmt->execute([$cartId]);
        
        // Commit transaksi
        $pdo->commit();
        
        // Set session untuk order ID
        $_SESSION['last_order_id'] = $orderId;
        $success = true;
        
        // Log sukses
        error_log("Order created successfully. Order ID: $orderId, User ID: {$_SESSION['UserID']}");
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error creating order: " . $e->getMessage();
        error_log("Payment confirmation error: " . $e->getMessage());
    }
}

// Format total amount as Rupiah
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="../../assets/images/logo_1.png" type="image/x-icon" />
    <title>Payment Confirmation - ConcertTix</title>

    <!-- Bootstrap CSS & Icons -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" /></noscript>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />
    
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
      }

      /* BASE STYLES */
      body {
        background: linear-gradient(to bottom, var(--background-light) 0%, var(--background-white) 100%);
        overflow-x: hidden;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: var(--text-dark);
        min-height: 100vh;
      }

      .container {
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
      }

      /* Button styles for offcanvas menu */
      .btn-outline-purple {
        color: #8a4fff;
        border-color: #8a4fff;
        transition: all 0.3s ease;
      }

      .btn-outline-purple:hover {
        background: #8a4fff;
        color: white;
      }

      .btn-purple-gradient {
        background: linear-gradient(135deg, #8a4fff 0%, #5e2de0 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
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
        content: "";
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

      /* */
      .btn-pswt {
        background: linear-gradient(135deg, #8a4fff 0%, #5e2de0 100%);
        color: white;
        border: none;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
      }

      .btn-pswt:hover {
        background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(138, 79, 255, 0.3);
      }

      /* Hover effect for gradient button */
      .btn-pswt::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.473), transparent);
        transition: 0.5s;
      }

      .btn-pswt:hover::before {
        left: 100%;
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
        content: "";
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

      /* Animations */
      @keyframes floatLink {
        0%,
        100% {
          transform: translateY(0);
        }
        50% {
          transform: translateY(-2px);
        }
      }

      @keyframes subtleShake {
        0%,
        100% {
          transform: translateX(0) rotate(0deg);
        }
        25% {
          transform: translateX(-2px) rotate(-0.5deg);
        }
        50% {
          transform: translateX(2px) rotate(0.5deg);
        }
        75% {
          transform: translateX(-1px) rotate(-0.3deg);
        }
      }

      @keyframes pulse {
        0% {
          transform: scale(1);
        }
        50% {
          transform: scale(1.05);
        }
        100% {
          transform: scale(1);
        }
      }

      @keyframes float {
        0%,
        100% {
          transform: translateY(0);
        }
        50% {
          transform: translateY(-5px);
        }
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
      .menu-item-float:nth-child(1) {
        animation-delay: 0.1s;
      }
      .menu-item-float:nth-child(2) {
        animation-delay: 0.2s;
      }
      .menu-item-float:nth-child(3) {
        animation-delay: 0.3s;
      }
      .menu-item-float:nth-child(4) {
        animation-delay: 0.4s;
      }

      .social-float:nth-child(1) {
        animation-delay: 0.1s;
      }
      .social-float:nth-child(2) {
        animation-delay: 0.2s;
      }
      .social-float:nth-child(3) {
        animation-delay: 0.3s;
      }
      .social-float:nth-child(4) {
        animation-delay: 0.4s;
      }

      /* Payment Confirmation Styles */
      .payment-container {
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(138, 79, 255, 0.1);
        overflow: hidden;
        transform: translateY(20px);
        opacity: 0;
        transition: all 0.6s ease-out;
      }

      .payment-container.visible {
        transform: translateY(0);
        opacity: 1;
      }

      .payment-header {
        background: var(--gradient-purple);
        color: white;
        padding: 1.5rem;
        text-align: center;
      }

      .payment-body {
        padding: 2rem;
      }

      .payment-method-card {
        border: 1px solid rgba(138, 79, 255, 0.2);
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        background: white;
      }

      .payment-method-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(138, 79, 255, 0.1);
      }

      .payment-method-icon {
        width: 60px;
        height: 60px;
        background: rgba(138, 79, 255, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--primary-purple);
        margin-right: 1rem;
      }

      .payment-details {
        flex: 1;
      }

      .payment-amount {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-purple);
        margin: 1rem 0;
      }

      .countdown-timer {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary-orange);
      }

      .qr-code-container {
        text-align: center;
        margin: 1.5rem 0;
      }

      .qr-code {
        max-width: 200px;
        margin: 0 auto;
        border: 1px solid #eee;
        padding: 1rem;
        border-radius: 8px;
        background: white;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      }

      .qr-instructions {
        background: rgba(138, 79, 255, 0.05);
        border-radius: 8px;
        padding: 1rem;
        margin: 1rem 0;
      }

      .qr-instructions ol {
        padding-left: 1.5rem;
        margin-bottom: 0;
      }

      .qr-instructions li {
        margin-bottom: 0.5rem;
      }

      .bank-details {
        background: rgba(138, 79, 255, 0.05);
        border-radius: 8px;
        padding: 1rem;
        margin: 1rem 0;
      }

      .bank-details p {
        margin-bottom: 0.5rem;
      }

      .bank-details strong {
        color: var(--primary-purple);
      }

      /* QRIS specific styles */
      .qris-payment-container {
        display: flex;
        flex-direction: column;
        align-items: center;
      }

      .qris-logo {
        width: 100px;
        margin-bottom: 1rem;
      }

      /* Responsive adjustments */
      @media (max-width: 768px) {
        .payment-container {
          margin: 1rem;
        }

        .payment-method-card {
          flex-direction: column;
          text-align: center;
        }

        .payment-method-icon {
          margin-right: 0;
          margin-bottom: 1rem;
        }

        .qris-payment-container {
          text-align: center;
        }
      }

      /* Footer styles */
      footer {
        background: linear-gradient(to bottom, #f9f5ff 0%, #ffffff 100%);
        padding: 2rem 0;
        margin-top: 3rem;
        border-top: 1px solid var(--border-light);
      }

      .footer-link {
        transition: all 0.3s ease;
        display: inline-block;
        color: var(--text-dark);
        text-decoration: none;
      }

      .footer-link:hover {
        color: var(--primary-purple) !important;
        transform: translateX(5px);
      }
      /* Wave Background - Fixed Version */
      .wave-bg-footer {
        background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none"><path fill="%238a4fff" fill-opacity="0.05" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
        background-repeat: no-repeat;
        background-position: bottom center;
        background-size: cover;
        height: 100%;
      }
      /* Social icons */
      .social-icon {
        transition: all 0.3s ease;
        cursor: pointer;
      }

      .social-icon:hover {
        transform: scale(1.2) translateY(-3px);
        color: var(--primary-purple) !important;
      }

      /* Animation classes */
      .animate__animated {
        animation-duration: 1s;
      }

      .animate__fadeIn {
        animation-name: fadeIn;
      }

      @keyframes fadeIn {
        from {
          opacity: 0;
        }
        to {
          opacity: 1;
        }
      }
        .payment-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .payment-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 25px;
            text-align: center;
        }
        
        .payment-header h2 {
            font-weight: 700;
            margin: 0;
        }
        
        .payment-body {
            padding: 30px;
        }
        
        .countdown-timer {
            font-size: 2.5rem;
            font-weight: 700;
            color: #ff6b6b;
            letter-spacing: 2px;
            background: #fff6f6;
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #ffcccc;
        }
        
        .order-summary {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .concert-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .concert-item:last-child {
            border-bottom: none;
        }
        
        .concert-item img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }
        
        .concert-info {
            flex: 1;
        }
        
        .concert-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .concert-details {
            color: #666;
            font-size: 0.9rem;
        }
        
        .price-tag {
            font-weight: 700;
            color: #2575fc;
        }
        
        .payment-method-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            border: 1px solid #eaeaea;
        }
        
        .payment-method-icon {
            width: 60px;
            height: 60px;
            background: #f0f7ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .payment-method-icon i {
            font-size: 28px;
            color: #2575fc;
        }
        
        .btn-purple-gradient {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-purple-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(37, 117, 252, 0.4);
        }
        
        .qr-code-container {
            max-width: 250px;
            margin: 0 auto;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .qr-code {
            width: 100%;
            height: auto;
        }
        
        .total-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2575fc;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 1;
        }
        
        .step {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 700;
            color: #6c757d;
        }
        
        .step.active .step-number {
            background: #2575fc;
            color: white;
        }
        
        .step-label {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .step.active .step-label {
            color: #2575fc;
            font-weight: 600;
        }
        
        .payment-details h5 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .bank-details p {
            margin-bottom: 8px;
        }
        
        .payment-amount {
            font-weight: 700;
            color: #2575fc;
            font-size: 1.2rem;
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .spinner {
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
    </style>
</head>
<body>
    <!-- Processing Overlay (hidden by default) -->
    <div class="processing-overlay" id="processingOverlay" style="display: none;">
        <div class="spinner-border text-purple" style="width: 3rem; height: 3rem;"></div>
        <h3 class="mt-3">Processing your payment...</h3>
        <p>Please wait, this may take a few seconds</p>
    </div>

    <!-- Payment Confirmation Content -->
    <div class="container py-5">
        <div class="step-indicator">
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-label">Cart</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Payment</div>
            </div>
            <div class="step active">
                <div class="step-number">3</div>
                <div class="step-label">Confirmation</div>
            </div>
        </div>
        
        <div class="payment-container visible">
            <div class="payment-header">
                <h2><i class="bi bi-check-circle-fill me-2"></i> Payment Confirmation</h2>
            </div>

            <div class="payment-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <h4>Payment Failed</h4>
                        <p><?php echo $error; ?></p>
                        <p class="mt-2 small">Please try again or contact support if the problem persists.</p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a class="btn btn-outline-secondary mx-1 mb-2" href="../../public/">
                            <i class="bi bi-house-door me-2"></i> Back To Home
                        </a>
                        <a class="btn btn-purple-gradient mx-1 mb-2" href="../../user/cart/">
                            <i class="bi bi-cart me-2"></i> View Cart
                        </a>
                    </div>
                <?php elseif ($success): ?>
                    <div class="alert alert-success text-center">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <h4>Payment Confirmed Successfully!</h4>
                        <p>Your order #<?php echo $orderId; ?> has been processed.</p>
                        <p>Redirecting to your tickets...</p>
                        <div class="spinner-border text-success mt-3" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <script>
                        setTimeout(function() {
                            window.location.href = '../../user/tickets/index.php';
                        }, 3000);
                    </script>
                <?php else: ?>
                    <div class="text-center mb-4">
                        <h4 class="mb-3">Complete Your Payment</h4>
                        <p class="text-muted">Please complete your payment within</p>
                        <div class="countdown-timer mb-3">23:59:59</div>
                        <div class="alert alert-info"><i class="bi bi-info-circle-fill me-2"></i> Your tickets will be issued automatically after payment confirmation</div>
                    </div>
                    
                    <div class="payment-method-card" id="payment-method-details">
                        <!-- Payment method details will be inserted here by JavaScript -->
                    </div>

                    <form method="POST" action="" id="paymentForm">
                        <input type="hidden" name="payment_method" value="<?php echo htmlspecialchars($method); ?>">
                        <div class="text-center mt-4">
                            <button type="submit" name="confirm_payment" class="btn btn-purple-gradient mx-1 mb-2" id="confirm-payment-btn">
                                <i class="bi bi-check-circle me-2"></i> Confirm Payment
                            </button>
                            <a class="btn btn-outline-secondary mx-1 mb-2" href="../../public/">
                                <i class="bi bi-house-door me-2"></i> Back To Home
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light py-5 position-relative overflow-hidden">
        <!-- Wave Background -->
        <div class="position-absolute bottom-0 start-0 w-100 h-100" style="z-index: 0; overflow: hidden">
            <div class="position-absolute bottom-0 start-0 w-100 h-100 wave-bg-footer"></div>
        </div>

        <div class="container position-relative" style="z-index: 1">
            <div class="row g-4">
                <!-- Left - Brand Info -->
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">
                        <h3 class="fw-bold mb-3" style="background: linear-gradient(90deg, #8a4fff, #ff6600); -webkit-background-clip: text; background-clip: text; color: transparent">Concert<span style="color: #ff6600">Tix</span></h3>
                        <p class="text-muted mb-4">The easiest way to buy tickets for your favorite concerts and events worldwide.</p>
                        <div class="d-flex gap-3 social-icons">
                            <a
                                href="https://www.facebook.com/groups/3288439241475430/"
                                class="social-icon d-flex align-items-center justify-content-center rounded-circle"
                                style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease"
                            >
                                <i class="bi bi-facebook text-purple"></i>
                            </a>
                            <a
                                href="https://www.instagram.com/xtoph.y?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw=="
                                class="social-icon d-flex align-items-center justify-content-center rounded-circle"
                                style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease"
                            >
                                <i class="bi bi-instagram text-purple"></i>
                            </a>
                            <a href="https://x.com/home" class="social-icon d-flex align-items-center justify-content-center rounded-circle" style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease">
                                <i class="bi bi-twitter-x text-purple"></i>
                            </a>
                            <a
                                href="https://www.youtube.com/@xant_san"
                                class="social-icon d-flex align-items-center justify-content-center rounded-circle"
                                style="width: 40px; height: 40px; background: rgba(138, 79, 255, 0.1); transition: all 0.3s ease"
                            >
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
                            <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff">Company</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="../../public/about.php" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> About Us
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="../../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Careers
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="https://api.whatsapp.com/send?phone=6285156473714&text=Hi+Sir+Admin+ConcertTix" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Contact Us
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="../../public/privacy_policy.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Privacy Policy
                                    </a>
                                </li>
                                <li>
                                    <a href="../../public/tems_of_service.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Terms of Service
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Support -->
                        <div class="col-sm-6">
                            <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff">Support</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <a href="../../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Help Center
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="../../public/faqs.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> FAQs
                                    </a>
                                </li>
                                <li class="mb-2">
                                    <a href="../../public/refund_policy.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Refund Policy
                                    </a>
                                </li>
                                <li>
                                    <a href="../../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease">
                                        <i class="bi bi-chevron-right me-2" style="color: #8a4fff"></i> Report an Issue
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Right - Newsletter -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff">Stay Updated</h6>
                    <p class="text-muted mb-3">Subscribe to our newsletter for concert updates and exclusive offers.</p>

                    <form class="mb-4">
                        <div class="input-group mb-3">
                            <input type="email" class="form-control" placeholder="Your email" style="border-right: 0; border-color: #8a4fff" />
                            <button class="btn btn-purple-gradient" type="submit" style="border-left: 0">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </div>
                    </form>

                    <h6 class="fw-bold mb-3 text-uppercase" style="color: #8a4fff">Download Our App</h6>
                    <div class="d-flex gap-2 app-download">
                        <a href="../../lost/404_page.html" class="hover-grow" style="transition: all 0.3s ease">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Download_on_the_App_Store_Badge.svg/1280px-Download_on_the_App_Store_Badge.svg.png" alt="App Store" class="img-fluid rounded" style="height: 40px" />
                        </a>
                        <a href="../../lost/404_page.html" class="hover-grow" style="transition: all 0.3s ease">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/78/Google_Play_Store_badge_EN.svg/1280px-Google_Play_Store_badge_EN.svg.png" alt="Google Play" class="img-fluid rounded" style="height: 40px" />
                        </a>
                    </div>
                </div>
            </div>

            <hr class="my-4" style="border-color: rgba(138, 79, 255, 0.1)" />

            <div class="row">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <p class="mb-0 text-muted small">© 2025 ConcertTix. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="d-flex justify-content-center justify-content-md-end gap-3">
                        <a href="../../public/privacy_policy.html" class="text-muted small" style="text-decoration: none">Privacy Policy</a>
                        <a href="../../public/tems_of_service.html" class="text-muted small" style="text-decoration: none">Terms of Service</a>
                        <a href="../../lost/404_page.html" class="text-muted small" style="text-decoration: none">Sitemap</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Get payment method from URL
            const urlParams = new URLSearchParams(window.location.search);
            const method = urlParams.get("method");
            const totalAmount = "<?php echo $totalAmount; ?>";
            const form = document.getElementById('paymentForm');
            const processingOverlay = document.getElementById('processingOverlay');

            // Format amount as Rupiah
            function formatRupiah(amount) {
                const numericAmount = parseInt(amount) || 0;
                return 'Rp ' + numericAmount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            const formattedAmount = formatRupiah(totalAmount);

            // Payment method information
            const paymentInfo = {
                BCA: {
                    name: "BCA",
                    icon: "bi-bank",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Bank Transfer - BCA</h5>
                            <p class="text-muted">Please transfer to the following account:</p>
                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> BCA</p>
                                <p><strong>Account Number:</strong> 1234567890</p>
                                <p><strong>Account Name:</strong> PT ConcertTix Indonesia</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the transfer description.</p>
                        </div>
                    </div>
                `,
                },
                BRI: {
                    name: "BRI",
                    icon: "bi-bank",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Bank Transfer - BRI</h5>
                            <p class="text-muted">Please transfer to the following account:</p>
                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> BRI</p>
                                <p><strong>Account Number:</strong> 0987654321</p>
                                <p><strong>Account Name:</strong> PT ConcertTix Indonesia</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the transfer description.</p>
                        </div>
                    </div>
                `,
                },
                Mandiri: {
                    name: "Mandiri",
                    icon: "bi-bank",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Bank Transfer - Mandiri</h5>
                            <p class="text-muted">Please transfer to the following account:</p>
                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> Mandiri</p>
                                <p><strong>Account Number:</strong> 1122334455</p>
                                <p><strong>Account Name:</strong> PT ConcertTix Indonesia</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the transfer description.</p>
                        </div>
                    </div>
                `,
                },
                BNI: {
                    name: "BNI",
                    icon: "bi-bank",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Bank Transfer - BNI</h5>
                            <p class="text-muted">Please transfer to the following account:</p>
                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> BNI</p>
                                <p><strong>Account Number:</strong> 5566778899</p>
                                <p><strong>Account Name:</strong> PT ConcertTix Indonesia</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the transfer description.</p>
                        </div>
                    </div>
                `,
                },
                Dana: {
                    name: "Dana",
                    icon: "bi-wallet",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-wallet"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Dana E-Wallet</h5>
                            <p class="text-muted">Please pay via Dana to:</p>
                            <div class="bank-details">
                                <p><strong>Dana Number:</strong> 0812-3456-7890</p>
                                <p><strong>Account Name:</strong> ConcertTix Official</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the payment note.</p>
                        </div>
                    </div>
                `,
                },
                OVO: {
                    name: "OVO",
                    icon: "bi-phone",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="payment-details">
                            <h5>OVO E-Wallet</h5>
                            <p class="text-muted">Please pay via OVO to:</p>
                            <div class="bank-details">
                                <p><strong>OVO Number:</strong> 0812-3456-7890</p>
                                <p><strong>Account Name:</strong> ConcertTix Official</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the payment note.</p>
                        </div>
                    </div>
                `,
                },
                GoPay: {
                    name: "GoPay",
                    icon: "bi-google",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-google"></i>
                        </div>
                        <div class="payment-details">
                            <h5>GoPay E-Wallet</h5>
                            <p class="text-muted">Please pay via GoPay to:</p>
                            <div class="bank-details">
                                <p><strong>GoPay Number:</strong> 0812-3456-7890</p>
                                <p><strong>Account Name:</strong> ConcertTix Official</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the payment note.</p>
                        </div>
                    </div>
                `,
                },
                QRIS: {
                    name: "QRIS",
                    icon: "bi-qr-code",
                    details: `
                    <div class="qris-payment-container">
                        <div class="d-flex align-items-center mb-4 w-100">
                            <div class="payment-method-icon">
                                <i class="bi bi-qr-code"></i>
                            </div>
                            <div class="payment-details text-start">
                                <h5>QRIS Payment</h5>
                                <p class="text-muted">Scan QR code below to complete your payment</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                        </div>
                        
                        <div class="qr-code-container">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=https://concerttix.example.com/pay/CTX-2025-XXXX" 
                                alt="QR Code" class="qr-code img-fluid">
                            <p class="text-muted small mt-2">Scan with your mobile banking/e-wallet app</p>
                        </div>
                        
                        <div class="qr-instructions mt-3">
                            <h6 class="mb-2">How to pay with QRIS:</h6>
                            <ol class="text-start">
                                <li>Open your mobile banking or e-wallet app</li>
                                <li>Select QRIS payment option</li>
                                <li>Scan the QR code above</li>
                                <li>Confirm the payment amount</li>
                                <li>Complete the transaction</li>
                            </ol>
                        </div>
                    </div>
                `,
                },
                Danamon: {
                    name: "Danamon",
                    icon: "bi-bank",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Bank Transfer - Danamon</h5>
                            <p class="text-muted">Please transfer to the following account:</p>
                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> Danamon</p>
                                <p><strong>Account Number:</strong> 6677889900</p>
                                <p><strong>Account Name:</strong> PT ConcertTix Indonesia</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the transfer description.</p>
                        </div>
                    </div>
                `,
                },
                Permata: {
                    name: "Permata",
                    icon: "bi-bank",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-bank"></i>
                        </div>
                        <div class="payment-details">
                            <h5>Bank Transfer - Permata</h5>
                            <p class="text-muted">Please transfer to the following account:</p>
                            <div class="bank-details">
                                <p><strong>Bank Name:</strong> Permata</p>
                                <p><strong>Account Number:</strong> 1122334455</p>
                                <p><strong>Account Name:</strong> PT ConcertTix Indonesia</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the transfer description.</p>
                        </div>
                    </div>
                `,
                },
                ShopeePay: {
                    name: "ShopeePay",
                    icon: "bi-phone",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="payment-details">
                            <h5>ShopeePay E-Wallet</h5>
                            <p class="text-muted">Please pay via ShopeePay to:</p>
                            <div class="bank-details">
                                <p><strong>ShopeePay Number:</strong> 0812-3456-7890</p>
                                <p><strong>Account Name:</strong> ConcertTix Official</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the payment note.</p>
                        </div>
                    </div>
                `,
                },
                LinkAja: {
                    name: "LinkAja",
                    icon: "bi-phone",
                    details: `
                    <div class="d-flex align-items-center">
                        <div class="payment-method-icon">
                            <i class="bi bi-phone"></i>
                        </div>
                        <div class="payment-details">
                            <h5>LinkAja E-Wallet</h5>
                            <p class="text-muted">Please pay via LinkAja to:</p>
                            <div class="bank-details">
                                <p><strong>LinkAja Number:</strong> 0812-3456-7890</p>
                                <p><strong>Account Name:</strong> ConcertTix Official</p>
                                <p><strong>Amount:</strong> <span class="payment-amount">${formattedAmount}</span></p>
                            </div>
                            <p class="text-muted small">Please include your order number (CTX-2025-XXXX) in the payment note.</p>
                        </div>
                    </div>
                `,
                },
            };

            // Display payment method details
            const paymentContainer = document.getElementById("payment-method-details");
            if (method && paymentInfo[method]) {
                paymentContainer.innerHTML = paymentInfo[method].details;
            } else {
                paymentContainer.innerHTML = `
                <div class="alert alert-warning w-100">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Payment method not specified or invalid.
                </div>
                `;
            }

            // Countdown timer
            function updateCountdown() {
                const countdownElement = document.querySelector(".countdown-timer");
                if (!countdownElement) return;

                // Set expiration time (current time + 1 hours)
                const now = new Date();
                const expiration = new Date(now.getTime() + 1 * 60 * 60 * 1000);

                function tick() {
                    const now = new Date();
                    const diff = expiration - now;

                    if (diff <= 0) {
                        countdownElement.textContent = "00:00:00";
                        countdownElement.style.color = "red";
                        return;
                    }

                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    countdownElement.textContent = `${hours.toString().padStart(2, "0")}:${minutes.toString().padStart(2, "0")}:${seconds.toString().padStart(2, "0")}`;
                }

                tick();
                setInterval(tick, 1000);
            }

            updateCountdown();

            // Handle payment confirmation button click
            const confirmBtn = document.getElementById("confirm-payment-btn");
            if (confirmBtn) {
                form.addEventListener("submit", function(e) {
                    // Tampilkan loading state
                    processingOverlay.style.display = 'flex';
                    
                    // Biarkan form submit secara normal
                    // Overlay akan tetap muncul sampai halaman reload
                });
            }
        });
    </script>
</body>
</html>