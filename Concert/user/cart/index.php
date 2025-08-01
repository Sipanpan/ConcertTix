<?php
 include_once '../../view/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="shortcut icon" href="../../assets/images/logo_1.png" type="image/x-icon" />
    <title>Cart - ConcertTix</title>

    <!-- Bootstrap CSS & Icons -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'" />
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" /></noscript>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Toast Notification CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" />

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

     <style>
      /* VARIABLES */
      :root {
        --primary-purple: #8a4fff;
        --primary-orange: #ff6600;
        --gradient-purple: linear-gradient(135deg, #8a4fff 0%, #5e2de0 100%);
        --gradient-orange: linear-gradient(135deg, #ff6600 0%, #ff3300 100%);
        --gradient-purple-horizontal: linear-gradient(90deg, #8a4fff, #5e2de0);
        --gradient-orange-horizontal: linear-gradient(90deg, #ff6600, #ff3300);
        --gradient-purple-orange: linear-gradient(90deg, #8a4fff, #ff6600);
        --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.15);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.2);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
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
        min-height: 100vh;
        color: var(--text-dark);
      }

      /* Wider container */
      .container {
        max-width: 100%;
        padding-left: 15px;
        padding-right: 15px;
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

      /* Cart Page Styles */
      .cart-item-card {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
        border: 1px solid rgba(138, 79, 255, 0.1);
        background: white;
      }

      .cart-item-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
      }

      .cart-item-img {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
      }

      .cart-item-img:hover {
        transform: scale(1.05);
      }

      .quantity-control {
        width: 120px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(138, 79, 255, 0.2);
      }

      .quantity-control .btn {
        width: 36px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        border: none;
        color: var(--primary-purple);
        font-weight: bold;
        transition: var(--transition);
      }

      .quantity-control .btn:hover {
        background-color: rgba(138, 79, 255, 0.1);
        color: var(--primary-purple);
      }

      .quantity-control .btn:active {
        background-color: rgba(138, 79, 255, 0.2);
      }

      .quantity-control input {
        text-align: center;
        border: none;
        border-left: 1px solid rgba(138, 79, 255, 0.2);
        border-right: 1px solid rgba(138, 79, 255, 0.2);
        font-weight: 600;
        background: white;
        color: #333;
        padding: 0.375rem;
      }

      .quantity-control input:focus {
        box-shadow: none;
        border-color: rgba(138, 79, 255, 0.4);
      }

      /* Beli button */
      .btn-beli {
        background: var(--gradient-purple);
        color: white;
        border: none;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
        border-radius: 8px !important;
        padding: 0.75rem;
        font-weight: 600;
        width: 100%;
        margin-top: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
      }

      .btn-beli:hover {
        background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
      }

      .btn-beli:disabled {
        background: #e9ecef;
        color: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
      }

      .summary-card {
        border-radius: 12px;
        background: white;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(138, 79, 255, 0.1);
        transition: var(--transition);
        padding: 1.5rem;
      }

      .summary-card h5 {
        font-weight: 700;
        color: var(--primary-purple);
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(138, 79, 255, 0.1);
      }

      .summary-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
      }

      .summary-total {
        font-weight: 700;
        font-size: 1.1rem;
        margin: 1.5rem 0;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(138, 79, 255, 0.1);
      }

      .summary-card:hover {
        box-shadow: var(--shadow-md);
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
      .btn-orange-gradient {
        background: var(--gradient-orange);
        color: white;
        border: none;
        transition: var(--transition);
        box-shadow: var(--shadow-sm);
      }

      .btn-orange-gradient:hover {
        background: linear-gradient(135deg, #e65c00 0%, #e63300 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
      }

      .btn-outline-purple {
        color: #8a4fff;
        border-color: #8a4fff;
        transition: all 0.3s ease;
      }

      .btn-outline-purple:hover {
        background: #8a4fff;
        color: white;
      }

      .section-title-gradient {
        background: var(--gradient-purple-orange);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        position: relative;
        font-size: 2rem;
        font-weight: 700;
      }

      .section-title-underline {
        position: absolute;
        bottom: -8px;
        left: 0;
        width: 60%;
        height: 4px;
        background: var(--gradient-purple-orange);
        border-radius: 2px;
      }

      /* Empty Cart Styles */
      .empty-cart-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 1.5rem 2rem;
        background-color: white;
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        border: 1px solid rgba(138, 79, 255, 0.1);
        margin: 0rem 0;
        transition: var(--transition);
      }

      .empty-cart-container:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-lg);
      }

      .empty-cart-icon {
        font-size: 5rem;
        margin-bottom: 1.5rem;
        background: var(--gradient-purple-orange);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: bounce 2s infinite;
      }

      @keyframes bounce {
        0%,
        20%,
        50%,
        80%,
        100% {
          transform: translateY(0);
        }
        40% {
          transform: translateY(-20px);
        }
        60% {
          transform: translateY(-10px);
        }
      }

      .empty-cart-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: #333;
      }

      .empty-cart-message {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 2rem;
        max-width: 500px;
      }

      /* Delete button animation */
      .btn-delete {
        transition: var(--transition);
        border-radius: 50px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .btn-delete:hover {
        transform: scale(1.1);
        color: #dc3545 !important;
        background: rgba(220, 53, 69, 0.1);
      }

      /* Checkbox styles */
      .form-check-input {
        width: 1.2em;
        height: 1.2em;
        margin-top: 0.25em;
      }

      .form-check-input:checked {
        background-color: var(--primary-purple);
        border-color: var(--primary-purple);
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

      /* Loading spinner */
      .spinner-border {
        width: 1.5rem;
        height: 1.5rem;
        border-width: 0.2em;
      }

      /* Payment methods */
      .payment-method {
        transition: var(--transition);
        filter: grayscale(30%);
        opacity: 0.8;
      }

      .payment-method:hover {
        filter: grayscale(0%);
        transform: scale(1.05);
        opacity: 1;
      }

      /* Price styling */
      .price {
        font-weight: 600;
        color: var(--primary-purple);
      }

      /* Continue shopping link */
      .continue-shopping {
        transition: var(--transition);
        color: var(--primary-purple);
      }

      .continue-shopping:hover {
        color: var(--primary-orange);
        transform: translateX(-3px);
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

      @media (max-width: 576px) {
        .container {
          padding-left: 10px;
          padding-right: 10px;
        }

        .cart-item-img {
          width: 60px;
          height: 60px;
        }

        .empty-cart-title {
          font-size: 1.3rem;
        }

        .empty-cart-message {
          font-size: 1rem;
        }

        .quantity-control {
          width: 80px;
        }

        .quantity-control .btn {
          width: 25px;
        }

        .summary-card {
          padding: 1rem;
        }

        .btn-checkout {
          padding: 0.5rem;
        }
      }

      /* Animation for cart items */
      @keyframes fadeIn {
        from {
          opacity: 0;
          transform: translateY(20px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      .cart-item-animate {
        animation: fadeIn 0.5s ease-out forwards;
      }

      /* Badge styling */
      .cart-badge {
        font-size: 0.6rem;
        padding: 0.25rem 0.4rem;
        vertical-align: middle;
      }
    </style>
  </head>
  <body>
    <!-- Cart Content -->
    <div class="container my-5">
      <!-- Nama page dan button back -->
      <div class="row mb-4">
        <div class="col-12">
          <h2 class="fw-bold mb-0 position-relative d-inline-block">
            <span class="section-title-gradient">Cart</span>
            <span class="section-title-underline"></span>
          </h2>
          <div class="mt-3">
            <a href="../../public/" class="text-decoration-none continue-shopping"> <i class="bi bi-arrow-left me-2"></i>Continue searching Concerts </a>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Empty Cart State (initially hidden) -->
        <div class="col-lg-8">
          <div class="empty-cart-container" id="emptyCartState" style="display: none">
            <i class="bi bi-cart-x empty-cart-icon"></i>
            <h3 class="empty-cart-title">Oh no, your basket is still empty</h3>
            <p class="empty-cart-message">Come on, fill your cart with the concert tickets you want!</p>
            <a href="../../public/" class="btn btn-purple-gradient px-4 py-2"> <i class="bi bi-search me-2"></i>Start Searching Tickets </a>
          </div>
          <!-- Cart Items (initially empty) -->
          <div id="cartItemsContainer">
            <!-- Cart items will be dynamically inserted here -->
          </div>
        </div>

        <!-- Order Summary -->
        <div class="col-lg-4 mt-4 mt-lg-0">
          <div class="card summary-card" id="orderSummary">
            <h5>Order Summary</h5>
            <div class="summary-item">
              <span>Sub Total:</span>
              <span class="price" id="subtotal">Rp 0</span>
            </div>
            <div class="summary-item">
              <span>Service Fee:</span>
              <span class="price" id="serviceFee">Rp 0</span>
            </div>
            <div class="summary-item summary-total">
              <span>Total:</span>
              <span class="price" id="total">Rp 0</span>
            </div>
            <a href="../../user/payment/index.php" class="btn btn-beli" id="beliBtn" disabled> <i class="bi bi-credit-card me-2"></i> Proceed to Payment </a>
            <div class="text-center mt-3">
              <small class="text-muted">By completing your purchase you agree to our <a href="../../public/tems_of_service.html" class="text-purple">Terms of Service</a></small>
            </div>
          </div>
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

    <!-- Toast Notification JS -->
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- Custom Scripts -->
<script>
  // Fungsi untuk memuat item cart
  async function loadCartItems() {
    try {
      const response = await fetch('../../auth/get_cart_items.php');
      const data = await response.json();
      
      if (data.success) {
        if (data.items && data.items.length > 0) {
          renderCartItems(data.items);
        } else {
          showEmptyCart();
        }
      } else {
        showEmptyCart();
        console.error(data.message);
      }
    } catch (error) {
      console.error('Error loading cart:', error);
      showEmptyCart();
    }
  }

  // Fungsi untuk menampilkan cart kosong
  function showEmptyCart() {
    document.getElementById("emptyCartState").style.display = "flex";
    document.getElementById("cartItemsContainer").innerHTML = "";
    document.getElementById("beliBtn").disabled = true;
    updateCartTotal();
  }

  // Fungsi untuk render item cart
  function renderCartItems(items) {
    const container = document.getElementById("cartItemsContainer");
    let html = "";

    items.forEach((item) => {
      const totalPrice = item.Price * item.Quantity;
      // PERBAIKAN 1: Sesuaikan path gambar
      const imageUrl = item.ImageURL.replace('../', '../../');
      
      html += `
        <div class="card mb-3 cart-item-card" data-cart-item-id="${item.CartItemID}" data-price-per-item="${item.Price}">
          <div class="card-body">
            <div class="d-flex">
              <input class="form-check-input me-3 mt-2 select-item" type="checkbox" checked>
              <img src="${imageUrl}" class="me-3 cart-item-img" alt="Concert" style="width: 100px; height: 100px; object-fit: cover;">
              <div class="flex-grow-1">
                <h6 class="fw-bold mb-1">${item.ConcertTitle}</h6>
                <p class="mb-0 text-muted">${item.ConcertDate} - ${item.Venue}</p>
                <small class="text-muted">${item.TicketType}</small>
        
                <div class="d-flex justify-content-between align-items-center mt-3">
                  <div class="input-group input-group-sm quantity-control">

                    <input type="number" class="text-center" value="${item.Quantity}" min="1">

                  </div>
        
                  <span class="fw-bold text-end price">Rp ${totalPrice.toLocaleString("id-ID")}</span>
        
                  <button class="btn btn-sm btn-outline-danger btn-delete" title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    });

    html += `
      <div class="d-flex justify-content-between mt-4">
        <button class="btn btn-outline-danger" id="clearCartBtn">
          <i class="bi bi-trash me-2"></i>Clear Cart
        </button>
      </div>`;

    container.innerHTML = html;
    document.getElementById("emptyCartState").style.display = "none";

    // Tambahkan event listeners
    addEventListeners();
    updateCartTotal();
  }

  // Fungsi untuk menambahkan event listeners
  function addEventListeners() {
    // Plus button
    document.querySelectorAll('.btn-plus').forEach(button => {
      button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        input.value = parseInt(input.value) + 1;
        updateCartItemQuantity(this);
      });
    });

    // Minus button
    document.querySelectorAll('.btn-minus').forEach(button => {
      button.addEventListener('click', function() {
        const input = this.parentElement.querySelector('input');
        if (parseInt(input.value) > 1) {
          input.value = parseInt(input.value) - 1;
          updateCartItemQuantity(this);
        }
      });
    });

    // Delete button
    document.querySelectorAll('.btn-delete').forEach(button => {
      button.addEventListener('click', function() {
        const card = this.closest('.card');
        const cartItemId = card.dataset.cartItemId;
        deleteCartItem(cartItemId, card);
      });
    });

    // Clear cart button
    document.getElementById('clearCartBtn')?.addEventListener('click', function() {
      if (confirm('Are you sure you want to clear your cart?')) {
        clearCart();
      }
    });

    // Checkbox select item
    document.querySelectorAll('.select-item').forEach(checkbox => {
      checkbox.addEventListener('change', updateCartTotal);
    });
  }

  // Fungsi untuk update quantity item di cart
  function updateCartItemQuantity(element) {
    const card = element.closest('.card');
    const cartItemId = card.dataset.cartItemId;
    const input = card.querySelector('input');
    const quantity = parseInt(input.value);

    updateCartItem(cartItemId, quantity);
  }

  // Fungsi untuk update quantity item di cart
  async function updateCartItem(cartItemId, quantity) {
    try {
      const response = await fetch('../../auth/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
          action: 'update',
          cart_item_id: parseInt(cartItemId),  // Pastikan integer
          quantity: parseInt(quantity)         // Pastikan integer
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        const card = document.querySelector(`.card[data-cart-item-id="${cartItemId}"]`);
        if (card) {
          const pricePerItem = parseFloat(card.dataset.pricePerItem);
          const totalPrice = pricePerItem * quantity;
          card.querySelector('.price').textContent = `Rp ${totalPrice.toLocaleString("id-ID")}`;
        }
        updateCartTotal();
      } else {
        alert('Error: ' + result.message);
      }
    } catch (error) {
      alert('Error: ' + error.message);
    }
  }

  // Fungsi untuk hapus item dari cart
  async function deleteCartItem(cartItemId, cardElement) {
    try {
      const response = await fetch('../../auth/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
          action: 'delete',
          cart_item_id: parseInt(cartItemId)  // Pastikan integer
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        cardElement.remove();
        updateCartTotal();
        if (document.querySelectorAll('.cart-item-card').length === 0) {
          showEmptyCart();
        }
      } else {
        alert('Error: ' + result.message);
      }
    } catch (error) {
      alert('Error: ' + error.message);
    }
  }
  
  // Fungsi untuk clear cart
  async function clearCart() {
    try {
      const response = await fetch('../../auth/update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ 
          action: 'clear'
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        document.getElementById("cartItemsContainer").innerHTML = "";
        showEmptyCart();
      } else {
        alert('Error: ' + result.message);
      }
    } catch (error) {
      alert('Error: ' + error.message);
    }
  }

  // Fungsi untuk update total harga
  function updateCartTotal() {
    let subtotal = 0;
    let selectedItems = 0;

    document.querySelectorAll('.cart-item-card').forEach(card => {
      const checkbox = card.querySelector('.select-item');
      if (checkbox.checked) {
        const priceText = card.querySelector('.price').textContent;
        const price = parseInt(priceText.replace(/[^\d]/g, ''));
        subtotal += price;
        selectedItems++;
      }
    });

    const serviceFee = Math.round(subtotal * 0.06); // 6% service fee
    const total = subtotal + serviceFee;

    document.getElementById("subtotal").textContent = `Rp ${subtotal.toLocaleString("id-ID")}`;
    document.getElementById("serviceFee").textContent = `Rp ${serviceFee.toLocaleString("id-ID")}`;
    document.getElementById("total").textContent = `Rp ${total.toLocaleString("id-ID")}`;

    // Enable/disable beli button based on selected items
    document.getElementById("beliBtn").disabled = selectedItems === 0;
  }

  // Panggil saat halaman dimuat
  document.addEventListener('DOMContentLoaded', function() {
    loadCartItems();
  });
</script>
  </body>
</html>
