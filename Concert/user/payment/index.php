<?php
require_once '../../auth/session_config.php';
include_once '../../view/navbar.php';

if (!isset($_SESSION['UserID'])) {
    header('Location: ../../auth/login.html');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="shortcut icon" href="../assets/images/logo_1.png" type="image/x-icon">
  <title>Payment - ConcertTix</title>
   <!-- Bootstrap CSS & Icons -->
   <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
   <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></noscript>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">


  <style>
    /* VARIABLES */
    :root {
      --primary-purple: #8a4fff;
      --primary-orange: #ff6600;
      --gradient-purple: linear-gradient(135deg, #8a4fff 0%, #5e2de0 100%);
      --gradient-purple-orange: linear-gradient(90deg, #8a4fff 0%, #ff6600 100%);
      --text-dark: #333;
      --text-light: #f8f9fa;
      --background-light: #f5f0ff;
      --background-white: #ffffff;
      --border-light: rgba(138, 79, 255, 0.2);
      --shadow-soft: 0 10px 30px rgba(138, 79, 255, 0.1);
      --shadow-medium: 0 5px 15px rgba(138, 79, 255, 0.2);
    }
    
    body {
      background: linear-gradient(to bottom, var(--background-light) 0%, var(--background-white) 100%);
      overflow-x: hidden;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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

    /* Payment Page Styles */
    .payment-container {
      max-width: 1400px;
      margin: 0 auto;
      padding: 2rem 0;
    }
    
    .payment-card {
      background: #fff;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      margin-bottom: 1.5rem;
    }
    
    .payment-card:hover {
      box-shadow: 0 8px 25px rgba(138, 79, 255, 0.1);
      transform: translateY(-2px);
    }
    
    .section-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 1.5rem;
      position: relative;
      color: var(--primary-purple);
    }
    
    .section-title::after {
      content: '';
      position: absolute;
      bottom: -8px;
      left: 0;
      width: 60px;
      height: 3px;
      background: var(--gradient-purple-orange);
      border-radius: 3px;
    }
    
    .concert-item {
      display: flex;
      align-items: flex-start;
      gap: 15px;
      margin-bottom: 20px;
      padding: 15px;
      border-radius: 8px;
      transition: all 0.3s ease;
      background-color: rgba(138, 79, 255, 0.03);
    }
    
    .concert-item:hover {
      background-color: rgba(138, 79, 255, 0.08);
      transform: translateX(5px);
    }
    
    .concert-item img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 6px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .concert-info {
      flex: 1;
    }
    
    .concert-info p {
      margin: 0;
      font-weight: 600;
      color: #333;
    }
    
    .concert-info small {
      color: #6c757d;
    }
    
    .price-tag {
      font-weight: 700;
      color: var(--primary-purple);
    }
    
    .payment-methods-container {
      margin-top: 2rem;
    }
    
    .payment-method-tabs {
      display: flex;
      gap: 10px;
      margin-bottom: 1.5rem;
      border-bottom: none;
    }
    
    .payment-method-tab {
      padding: 0.5rem 1.5rem;
      border-radius: 50px;
      background: #f8f9fa;
      border: 1px solid #dee2e6;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      white-space: nowrap;
    }
        .payment-method-tab:hover {
      border-color: var(--primary-purple);
    }
    
    .payment-method-tab.active {
      background: var(--gradient-purple);
      color: white;
      border-color: var(--primary-purple);
      box-shadow: 0 4px 10px rgba(138, 79, 255, 0.2);
    }

    /* Payment Options Scroll */
    .payment-options-container {
      display: none; /* Awalnya disembunyikan */
    }

        .payment-options-scroll {
      overflow-x: auto;
      display: flex;
      gap: 15px;
      padding-bottom: 10px;
      scrollbar-width: thin;
      scrollbar-color: #8a4fff #f1f1f1;
    }
    
    .payment-options-scroll::-webkit-scrollbar {
      height: 6px;
    }
    
    .payment-options-scroll::-webkit-scrollbar-thumb {
      background-color: #8a4fff;
      border-radius: 10px;
    }
    
    .payment-options-scroll::-webkit-scrollbar-track {
      background-color: #f1f1f1;
      border-radius: 10px;
    }
    
    .payment-option {
      min-width: 120px;
      text-align: center;
      border: 1px solid #dee2e6;
      border-radius: 10px;
      padding: 15px 10px;
      background: #f8f9fa;
      cursor: pointer;
      transition: all 0.3s ease;
      flex: 0 0 auto;
    }
        .payment-options {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
    }
    .payment-option:hover {
      border-color: var(--primary-purple);
      background-color: rgba(138, 79, 255, 0.05);
      transform: translateY(-3px);
    }
    
    .payment-option.active {
      border: 2px solid var(--primary-purple);
      background-color: rgba(138, 79, 255, 0.1);
      box-shadow: 0 4px 10px rgba(138, 79, 255, 0.2);
    }
    
    .payment-option img {
      width: 50px;
      height: 50px;
      object-fit: contain;
      margin-bottom: 8px;
    }
    
    .promo-box {
      border: 1px solid #ced4da;
      border-radius: 10px;
      padding: 20px;
      background: #f8f9fa;
      transition: all 0.3s ease;
    }
    
    .promo-box:hover {
      border-color: var(--primary-purple);
      box-shadow: 0 4px 10px rgba(138, 79, 255, 0.1);
    }
    
    .promo-input {
      border-radius: 50px;
      padding: 10px 20px;
      border: 1px solid #ced4da;
      transition: all 0.3s ease;
    }
    
    .promo-input:focus {
      border-color: var(--primary-purple);
      box-shadow: 0 0 0 0.2rem rgba(138, 79, 255, 0.1);
    }
    
    .btn-apply {
      background: var(--gradient-purple);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-apply:hover {
      background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(138, 79, 255, 0.3);
    }
    
    .btn-pay {
      background: var(--gradient-purple);
      color: white;
      border: none;
      border-radius: 50px;
      padding: 12px;
      font-weight: 600;
      font-size: 1.1rem;
      transition: all 0.3s ease;
      width: 100%;
      box-shadow: 0 4px 15px rgba(138, 79, 255, 0.2);
    }
    
    .btn-pay:hover:not(:disabled) {
      background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(138, 79, 255, 0.3);
    }

    .btn-pay:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    .total-price {
      font-size: 1.5rem;
      font-weight: 700;
      color: var(--primary-purple);
    }
    
    .price-breakdown {
      border-top: 1px dashed #dee2e6;
      padding-top: 15px;
    }
    
    .price-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
    }
    
    .price-label {
      color: #6c757d;
    }
    
    .price-value {
      font-weight: 600;
    }
    
    .payment-placeholder {
      background-color: #f8f9fa;
      border-radius: 8px;
      border: 1px dashed #dee2e6;
      padding: 2rem;
      text-align: center;
      margin: 1rem 0;
    }
    
    .payment-placeholder i {
      font-size: 2rem;
      color: #8a4fff;
      margin-bottom: 1rem;
    }
    
    .payment-placeholder p {
      color: #6c757d;
      margin-bottom: 0;
    }
    
    .info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 10px;
    }
    
    .info-item i {
      margin-right: 10px;
      color: var(--primary-purple);
    }
    
    /* Animations */
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

        /* Footer styles */
    footer {
      background: linear-gradient(to bottom, #f9f5ff 0%, #ffffff 100%);
      padding: 2rem 0;
      margin-top: 3rem;
      border-top: 1px solid rgba(138, 79, 255, 0.1);
    }
    
    .footer-link {
      transition: all 0.3s ease;
      display: inline-block;
    }
    
    .footer-link:hover {
      color: var(--primary-purple) !important;
      transform: translateX(5px);
    }
    .wave-bg-footer {
  background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" preserveAspectRatio="none"><path fill="%238a4fff" fill-opacity="0.05" d="M0,192L48,197.3C96,203,192,213,288,229.3C384,245,480,267,576,250.7C672,235,768,181,864,181.3C960,181,1056,235,1152,234.7C1248,235,1344,181,1392,154.7L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
  background-repeat: no-repeat;
  background-position: bottom center;
  background-size: cover;
  height: 100%;
}
  </style>
</head>
<body>

 <!-- Payment Content -->
  <div class="payment-container container my-5">
    <div class="row g-4">
      <div class="col-lg-8">
        <!-- Order Summary -->
        <div class="payment-card">
          <h4 class="section-title">Order Summary</h4>
          
          <div id="cart-items-container">
            <div class="text-center my-5">
              <div class="spinner-border text-purple" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
              <p>Loading your cart items...</p>
            </div>
          </div>
        </div>

        <!-- Payment Methods -->
        <div class="payment-card">
          <h4 class="section-title">Payment Methods</h4>
          
          <div class="payment-method-tabs">
            <div class="payment-method-tab active" onclick="showPaymentMethod('bank')">Bank Transfer</div>
            <div class="payment-method-tab" onclick="showPaymentMethod('ewallet')">E-Wallet</div>
            <div class="payment-method-tab" onclick="showPaymentMethod('qris')">QRIS</div>
          </div>
          
          <div id="payment-placeholder" class="payment-placeholder">
            <i class="bi bi-credit-card"></i>
            <p>Select payment method to see the options</p>
          </div>
          
          <!-- Bank Options -->
          <div id="bank-options" class="payment-options-container">
            <div class="payment-options-scroll">
              <div class="payment-option" onclick="selectPaymentMethod('BCA')">
                <img src="https://i.pinimg.com/736x/29/61/0b/29610b7dbf7e4ea5070626923a12cba8.jpg" alt="BCA" width="50" height="50">
                <div>BCA</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('BRI')">
                <img src="https://i.pinimg.com/736x/6b/13/7a/6b137a6a174c2f054904a3be35fe249f.jpg" alt="BRI" width="50" height="50">
                <div>BRI</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('Mandiri')">
                <img src="https://i.pinimg.com/736x/26/b3/4a/26b34ac4c3890d30ebc7a7ba9a829999.jpg" alt="Mandiri" width="50" height="50">
                <div>Mandiri</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('BNI')">
                <img src="https://i.pinimg.com/736x/36/38/43/36384348ef9d7bfff66da6da9e975d56.jpg" alt="BNI" width="50" height="50">
                <div>BNI</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('Danamon')">
                <img src="https://i.pinimg.com/736x/75/06/f7/7506f718d6aff3f6db2c52ee53083475.jpg" alt="Danamon" width="50" height="50">
                <div>Danamon</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('Permata')">
                <img src="https://i.pinimg.com/736x/76/73/d2/7673d2bfe339774be8f679443feca1b3.jpg" alt="Permata" width="50" height="50">
                <div>Permata</div>
              </div>
            </div>
          </div>
          
          <!-- E-Wallet Options -->
          <div id="ewallet-options" class="payment-options-container">
            <div class="payment-options-scroll">
              <div class="payment-option" onclick="selectPaymentMethod('Dana')">
                <img src="https://i.pinimg.com/736x/f2/7d/e0/f27de0e4a01ba9dfe8607ac03a4f7aae.jpg" alt="Dana" width="50" height="50">
                <div>Dana</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('OVO')">
                <img src="https://i.pinimg.com/736x/73/85/c9/7385c9ad635785453e4ab577e02ff80e.jpg" alt="OVO" width="50" height="50">
                <div>OVO</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('GoPay')">
                <img src="https://i.pinimg.com/736x/fe/ce/b2/feceb2ca508603b06c2f7ba18a5d018d.jp" alt="GoPay" width="50" height="50">
                <div>GoPay</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('ShopeePay')">
                <img src="https://i.pinimg.com/736x/d0/19/16/d019163d861908ed0046391ebfa42ce1.jpg" alt="ShopeePay" width="50" height="50">
                <div>ShopeePay</div>
              </div>
              <div class="payment-option" onclick="selectPaymentMethod('LinkAja')">
                <img src="https://i.pinimg.com/736x/43/27/4e/43274e40524c1ffc73e847b3b5f16ceb.jpg" alt="LinkAja" width="50" height="50">
                <div>LinkAja</div>
              </div>
            </div>
          </div>
          
          <!-- QRIS Options -->
          <div id="qris-options" class="payment-options-container">
            <div class="payment-options-scroll">
              <div class="payment-option" onclick="selectPaymentMethod('qris')">
                <img src="https://i.pinimg.com/736x/69/5e/3a/695e3a709eccbe055c311aac6b25729d.jpg" alt="QRIS" width="50" height="50">
                <div>QRIS</div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Pay Button -->
        <div class="mt-4">
          <button id="pay-button" class="btn btn-pay" disabled onclick="processPayment()">
            <i class="bi bi-credit-card me-2"></i> Buy Now
          </button>
        </div>
      </div>
      
      <!-- Right Column -->
      <div class="col-lg-4">
        <!-- Promo Code -->
        <div class="payment-card">
          <h6 class="fw-bold mb-3">Use promos to save more!</h6>
          <div class="promo-box">
            <div class="input-group">
              <input type="text" id="promo-code" class="form-control promo-input" placeholder="Enter promo code">
              <button class="btn btn-apply" onclick="applyPromo()">Apply</button>
            </div>
          </div>
        </div>
        
        <!-- Price Summary -->
        <div class="payment-card">
          <div class="price-breakdown">
            <div class="price-item">
              <span class="price-label">Total Price</span>
              <span class="price-value" id="total-price">Rp 0</span>
            </div>
            <div class="price-item">
              <span class="price-label">Service Fee</span>
              <span class="price-value" id="service-fee">Rp 5,000</span>
            </div>
            <div class="price-item">
              <span class="price-label">Discount</span>
              <span class="price-value text-success" id="discount">- Rp 0</span>
            </div>
            <hr>
            <div class="price-item mt-3">
              <span class="price-label fw-bold">Total payment</span>
              <span class="total-price" id="grand-total">Rp 0</span>
            </div>
          </div>
        </div>
    
        <!-- Important Info -->
        <div class="payment-card">
          <h6 class="fw-bold mb-3">Important Information</h6>
          <ul class="list-unstyled">
            <li class="mb-3 info-item">
              <i class="bi bi-check-circle-fill text-primary"></i>
              <small>Tickets will be sent to your email after successful payment</small>
            </li>
              <li class="mb-3 info-item">
              <i class="bi bi-person-check-fill text-success"></i>
              <small>Ticket can only be used by one person</small>
            </li>
            <li class="mb-3 info-item">
              <i class="bi bi-clock-fill text-warning"></i>
              <small>Payment must be completed within 1 hour</small>
            </li>
            <li class="mb-3 info-item">
              <i class="bi bi-shield-fill-check text-primary"></i>
              <small>Secure and encrypted transactions</small>
            </li>
            <li class="mb-3 info-item">
              <i class="bi bi-shield-x text-warning"></i>
              <small>After paying you cannot return the ticket or get a refund. Make sure you don't buy the wrong one</small>
            </li>
          </ul>
        </div>
      </div>
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
                  <a href="../public/about.php" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
                    <i class="bi bi-chevron-right me-2" style="color: #8a4fff;"></i> About Us
                  </a>
                </li>
                <li class="mb-2">
                  <a href="../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
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
                  <a href="../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
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
                  <a href="../lost/404_page.html" class="footer-link text-muted d-flex align-items-center" style="text-decoration: none; transition: all 0.3s ease;">
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
            <a href="../lost/404_page.html" class="hover-grow" style="transition: all 0.3s ease;">
              <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Download_on_the_App_Store_Badge.svg/1280px-Download_on_the_App_Store_Badge.svg.png" alt="App Store" class="img-fluid rounded" style="height: 40px;">
            </a>
            <a href="../lost/404_page.html" class="hover-grow" style="transition: all 0.3s ease;">
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
            <a href="../lost/404_page.html" class="text-muted small" style="text-decoration: none;">Sitemap</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Global variables
    let selectedPaymentMethod = null;
    let cartItems = [];
    let totalPrice = 0;
    let serviceFee = 5000;
    let discount = 0;

    // Format currency to Rupiah
    function formatRupiah(amount) {
      return 'Rp ' + amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Load cart items from server
    async function loadCartItems() {
      try {
        const response = await fetch('../../auth/get_cart_items.php');
        const data = await response.json();
        
        if (data.success && data.items && data.items.length > 0) {
          cartItems = data.items;
          renderCartItems(data.items);
          calculateTotalPrice(data.items);
          enablePayButton();
        } else {
          showEmptyCart();
        }
      } catch (error) {
        console.error('Error loading cart items:', error);
        showErrorMessage('Failed to load cart items. Please try again.');
      }
    }

    // Render cart items in the UI
// Render cart items in the UI
// Render cart items in the UI
function renderCartItems(items) {
    const container = document.getElementById('cart-items-container');
    let html = '';
    
    items.forEach(item => {
        // Format tanggal konser
        const concertDate = item.ConcertDate;
        let formattedDate = 'Tanggal tidak tersedia';
        
        try {
            if (concertDate) {
                const dateObj = new Date(concertDate);
                if (!isNaN(dateObj)) {
                    formattedDate = dateObj.toLocaleDateString('id-ID', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }
        } catch (e) {
            console.error('Error memformat tanggal:', e);
        }

        // Penanganan khusus untuk path gambar - SAMA SEPERTI DI CART PAGE
        let imageUrl = item.ImageURL || '';
        const placeholderUrl = 'https://via.placeholder.com/150?text=Gambar+Tidak+Tersedia';
        
        // Perbaiki path relatif
        if (imageUrl.startsWith('../')) {
            imageUrl = imageUrl.replace('../', '../../');
        }
        
        // Gunakan placeholder jika URL kosong
        if (!imageUrl) {
            imageUrl = placeholderUrl;
        }
        
        const itemTotal = item.Price * item.Quantity;
        html += `
            <div class="concert-item">
                <img src="${imageUrl}" class="me-3 cart-item-img" alt="Concert" style="width: 100px; height: 100px; object-fit: cover;" onerror="this.src='${placeholderUrl}'">
                <div class="concert-info w-100">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="fw-bold mb-1">${item.ConcertTitle || 'Konser tidak diketahui'}</p>
                            <small class="text-muted">${item.TicketType} x ${item.Quantity}</small>
                            <br>
                            <small class="text-muted">${item.Venue || 'Tempat tidak diketahui'} • ${formattedDate}</small>
                        </div>
                        <div class="text-end">
                            <span class="price-tag">${formatRupiah(itemTotal)}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

    // Calculate total price
    function calculateTotalPrice(items) {
      totalPrice = items.reduce((sum, item) => sum + (item.Price * item.Quantity), 0);
      updatePriceSummary();
    }

    // Update price summary display
    function updatePriceSummary() {
      const grandTotal = totalPrice + serviceFee - discount;
      
      document.getElementById('total-price').textContent = formatRupiah(totalPrice);
      document.getElementById('service-fee').textContent = formatRupiah(serviceFee);
      document.getElementById('discount').textContent = '- ' + formatRupiah(discount);
      document.getElementById('grand-total').textContent = formatRupiah(grandTotal);
    }

    // Show empty cart message
    function showEmptyCart() {
      const container = document.getElementById('cart-items-container');
      container.innerHTML = `
        <div class="text-center my-5">
          <i class="bi bi-cart-x" style="font-size: 3rem; color: #8a4fff;"></i>
          <h5 class="mt-3">Your cart is empty</h5>
          <p class="text-muted">Browse concerts and add tickets to your cart</p>
          <a href="../../public/" class="btn btn-purple-gradient">Browse Concerts</a>
        </div>
      `;
      disablePayButton();
    }

    // Show error message
    function showErrorMessage(message) {
      const container = document.getElementById('cart-items-container');
      container.innerHTML = `
        <div class="alert alert-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>
          ${message}
        </div>
      `;
      disablePayButton();
    }

    // Enable pay button
    function enablePayButton() {
      const payButton = document.getElementById('pay-button');
      payButton.disabled = false;
      payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i> Buy Now';
    }

    // Disable pay button
    function disablePayButton() {
      const payButton = document.getElementById('pay-button');
      payButton.disabled = true;
      payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i> Cart is Empty';
    }

    // Show payment method options
    function showPaymentMethod(method) {
      // Hide placeholder
      document.getElementById('payment-placeholder').style.display = 'none';
      
      // Hide all payment options
      document.querySelectorAll('.payment-options-container').forEach(el => {
        el.style.display = 'none';
      });
      
      // Show selected method options
      document.getElementById(`${method}-options`).style.display = 'block';
      
      // Update active tab
      document.querySelectorAll('.payment-method-tab').forEach(tab => {
        tab.classList.remove('active');
      });
      event.target.classList.add('active');
      
      // Reset selected method
      selectedPaymentMethod = null;
      document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('active');
      });
      
      // Update pay button state
      updatePayButtonState();
    }

    // Select payment method
    function selectPaymentMethod(method) {
      selectedPaymentMethod = method;
      
      // Remove active class from all options
      document.querySelectorAll('.payment-option').forEach(option => {
        option.classList.remove('active');
      });
      
      // Add active class to selected option
      event.currentTarget.classList.add('active');
      
      // Update pay button state
      updatePayButtonState();
    }

    // Update pay button state based on conditions
    function updatePayButtonState() {
      const payButton = document.getElementById('pay-button');
      const hasItems = cartItems && cartItems.length > 0;
      const hasPaymentMethod = selectedPaymentMethod !== null;
      
      if (hasItems && hasPaymentMethod) {
        payButton.disabled = false;
        payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i> Buy Now';
      } else if (!hasItems) {
        payButton.disabled = true;
        payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i> Cart is Empty';
      } else if (!hasPaymentMethod) {
        payButton.disabled = true;
        payButton.innerHTML = '<i class="bi bi-credit-card me-2"></i> Select Payment Method';
      }
    }

    // Apply promo code
    function applyPromo() {
      const promoCode = document.getElementById('promo-code').value.trim();
      if (!promoCode) {
        alert('Please enter a promo code');
        return;
      }
      
      // Simple promo validation (you can enhance this)
      const validPromos = {
        'SAVE10': 0.1,
        'WELCOME': 5000,
        'STUDENT': 0.15
      };
      
      if (validPromos[promoCode.toUpperCase()]) {
        const promoValue = validPromos[promoCode.toUpperCase()];
        
        if (promoValue < 1) {
          // Percentage discount
          discount = Math.floor(totalPrice * promoValue);
        } else {
          // Fixed amount discount
          discount = promoValue;
        }
        
        updatePriceSummary();
        alert('Promo code applied successfully!');
        document.getElementById('promo-code').disabled = true;
        document.querySelector('.btn-apply').disabled = true;
        document.querySelector('.btn-apply').textContent = 'Applied';
      } else {
        alert('Invalid promo code');
      }
    }

    // Process payment
     function processPayment() {
    if (!selectedPaymentMethod) {
      alert('Please select a payment method first!');
      return;
    }
    
    if (!cartItems || cartItems.length === 0) {
      alert('Your cart is empty!');
      return;
    }
    
    // Validasi tambahan: pastikan ada item yang kuantitasnya > 0
    const validItems = cartItems.filter(item => item.Quantity > 0);
    if (validItems.length === 0) {
      alert('Your cart contains invalid items!');
      return;
    }
    
    // Tampilkan loading state
    const payButton = document.getElementById('pay-button');
    const originalContent = payButton.innerHTML;
    payButton.disabled = true;
    payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
    
    // Simulate processing delay
    setTimeout(() => {
      // Redirect ke halaman konfirmasi
      const methodName = selectedPaymentMethod.toUpperCase();
      window.location.href = `confirmation.php?method=${encodeURIComponent(methodName)}`;
    }, 1500);
  }


    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
      loadCartItems();
      
      // Setup offcanvas functionality
      const offcanvasElement = document.getElementById('sidebarMenu');
      if (offcanvasElement && !bootstrap.Offcanvas.getInstance(offcanvasElement)) {
        new bootstrap.Offcanvas(offcanvasElement);
      }
    });

    // Handle search form submission
    document.getElementById('searchForm')?.addEventListener('submit', function(e) {
      e.preventDefault();
      const query = document.getElementById('searchInput').value.trim();
      if (query) {
        window.location.href = `../../public/events/search.php?q=${encodeURIComponent(query)}`;
      }
    });
  </script>
</body>
</html>

