<?php
require_once __DIR__ . '/../auth/session_config.php';
// include_once '../auth/check_login_status.php'; // Mulai sesi
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
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
      .navbar-brand {
        font-size: 1.6rem;
        font-weight: 700;
        transition: all 0.3s ease;
        margin-right: 1rem;
      }
      .navbar-brand:hover {
        transform: scale(1.03);
      }
      /* Style for scrolled navbar */
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
      /* Style for non-scrolled navbar buttons */
      .navbar:not(.scrolled) .btn-outline-secondary,
      .navbar:not(.scrolled) .btn-outline-purple {
        border-color: var(--primary-purple);
        color: var(--primary-purple);
      }
      .navbar:not(.scrolled) .btn-outline-secondary:hover,
      .navbar:not(.scrolled) .btn-outline-purple:hover {
        background: var(--primary-purple);
        color: white;
      }
      .navbar:not(.scrolled) .btn-purple-gradient {
        background: var(--gradient-purple);
        color: white;
      }
      .navbar:not(.scrolled) .btn-purple-gradient:hover {
        background: linear-gradient(135deg, #7a3fef 0%, #4e1dd0 100%);
        color: white;
      }
      /* Login Button */
      .navbar .btn-outline-purple {
        border-color: rgba(255, 255, 255, 0.5);
        color: rgba(255, 255, 255, 0.8);
        background: transparent;
      }

      .navbar .btn-outline-purple:hover {
        border-color: white;
        background: var(--primary-purple);
        color: white;
        box-shadow: 0 4px 10px rgba(138, 79, 255, 0.3);
      }
      /* Sign Up Button */
      .navbar .btn-purple-gradient {
        background: white;
        color: var(--primary-purple);
        border: none;
        box-shadow: 0 4px 15px rgba(138, 79, 255, 0.3);
        transition: all 0.3s ease;
      }
      /* Search input focus effect */
      .navbar .form-control:focus {
        border-color: var(--primary-purple);
        box-shadow: 0 0 0 0.2rem rgba(138, 79, 255, 0.1);
      }
      .navbar .btn-purple-gradient:hover {
        background: white;
        color: var(--primary-purple);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(138, 79, 255, 0.4);
      }
      /* Style for scrolled navbar buttons */
      .navbar.scrolled .btn-outline-secondary,
      .navbar.scrolled .btn-outline-purple {
        border-color: rgba(255, 255, 255, 0.5);
        color: rgba(255, 255, 255, 0.8);
      }
      .navbar.scrolled .btn-outline-secondary:hover,
      .navbar.scrolled .btn-outline-purple:hover {
        border-color: var(--primary-orange);
        background: var(--primary-orange);
        color: white;
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
      .navbar.scrolled .btn-purple-gradient {
        background: var(--primary-orange);
        color: white;
      }

      .navbar.scrolled .btn-purple-gradient:hover {
        background: #ff5500;
        color: white;
        box-shadow: 0 4px 15px rgba(255, 102, 0, 0.3);
      }
      /* Original Search Bar */
      .navbar-search .input-group {
        border-radius: 0.5rem;
      }
      .navbar-search .form-control {
        border-left: none;
      }
      /* Original Button Styles */

      .navbar-search .input-group-text {
        background: white;
        border-right: none;
        color: #6c757d;
      }
      .navbar .btn,
      .navbar .input-group-text,
      .navbar .form-control {
        transition: all 0.3s ease;
      }
      .navbar .form-control::placeholder {
        transition: all 0.3s ease;
      }
      /* Navbar Button Styles */
      .navbar .btn {
        transition: all 0.3s ease;
        border-radius: 50px;
        font-weight: 500;
        padding: 0.375rem 0.75rem;
      }
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
      /* Warna saat navbar scrolled */
      .navbar.scrolled .navbar-toggler-icon {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
      }

      /* Responsive adjustments */
      @media (max-width: 992px) {
        .navbar-brand span.ms-3 {
          display: none !important;
        }
        .navbar-collapse {
          padding-top: 1rem;
        }
        .justify-content-center {
          justify-content: flex-start !important;
          margin-left: 1rem;
        }
        .navbar-toggler.order-first {
          order: 0 !important;
        }
        .navbar-brand.order-lg-1 {
          order: 1 !important;
          margin-right: auto;
        }
        .justify-content-center.order-lg-2 {
          order: 3 !important;
          flex-basis: 100%;
          margin-top: 0.5rem;
        }
        .ms-lg-3.order-lg-3 {
          order: 2 !important;
          margin-left: auto;
        }
        .navbar .container {
          flex-wrap: wrap;
          justify-content: space-between;
        }
        .navbar-brand {
          order: 1;
          margin-right: auto;
        }
        .navbar-toggler {
          order: 0;
        }
        .navbar-collapse {
          order: 2;
          flex-basis: 100%;
        }
      }

      @media (max-width: 768px) {
        .navbar .container {
          flex-wrap: nowrap;
        }
        .navbar-toggler.me-2 {
          margin-right: 0.5rem !important;
        }
        .navbar-brand {
          font-size: 1.4rem !important;
          margin-right: 0;
        }
        .d-flex.flex-grow-1 {
          flex-grow: 0 !important;
          margin-left: auto;
        }
        .bigbanner {
          height: 300px;
        }

        .sliderinfo {
          padding: 15px;
          max-width: 90%;
        }

        .sliderinfolimit .name {
          font-size: 22px;
        }

        .sliderinfolimit .meta .quality {
          font-size: 13px;
        }

        .home-genres {
          margin-top: 0;
          border-radius: 0;
          padding: 10px;
        }
        .home-genres .genre-listx {
          justify-content: flex-start;
        }
        .genre-listx {
          white-space: normal;
          text-align: center;
          margin-bottom: 10px;
        }

        .alman {
          margin-top: 10px;
        }

        .hero h1 {
          font-size: 2rem;
        }

        .hero p {
          font-size: 1rem;
        }

        .concert-card {
          width: 192px;
          height: 380px;
        }

        .card-img {
          height: 180px;
        }

        .card-body {
          height: calc(100% - 180px);
        }

        .section-title-gradient {
          font-size: 1.5rem;
        }

        .all-concerts-wrapper {
          grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
          gap: 10px;
          row-gap: 20px;
        }

        .all-concerts-wrapper .concert-card {
          width: 192px;
          height: 380px;
        }

        .all-concerts-wrapper .card-img {
          height: 180px;
        }

        .all-concerts-wrapper .card-body {
          height: calc(100% - 180px);
        }

        .scroll-button {
          display: none !important;
        }
      }

      /* Tambahkan di CSS Anda */
      #all-concerts-section {
        scroll-margin-top: 80px; /* Sesuaikan dengan tinggi navbar */
      }


    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white sticky-top shadow-sm py-1" id="mainNavbar">
      <div class="container">
        <!-- Menu Button -->
        <button class="navbar-toggler order-first me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Logo -->
        <a href="/Concert/public/" class="navbar-brand fw-bold order-lg-1 mx-auto mx-lg-0 me-lg-4" href="#" style="font-size: 1.6rem"> <span style="color: #8a4fff">Concert</span><span style="color: #ff6600">Tix</span> </a>
        <a href="/Concert/public/" class="navbar-brand fw-bold order-lg-1 mx-auto mx-lg-0 me-lg-4" href="#" style="font-size: 1.6rem">
          <span class="ms-3 d-none d-lg-inline" style="font-size: 1.4rem; color: #7a3fef">Home Page</span>
        </a>
        <!-- Search Form -->
        <div class="order-lg-2 flex-grow-1 mx-2 mx-lg-0">
          <!-- Di navbar -->
          <form class="w-100 position-relative" role="search" id="searchForm" action="events/search.php" method="get">
            <div class="input-group" style="border-radius: 50px; border: 2px solid #8a4fff; overflow: hidden; transition: all 0.3s ease">
              <input class="form-control border-0 py-2 shadow-none" type="search" placeholder="Search Concert..." aria-label="Search" id="searchInput" style="box-shadow: none !important" />
              <button type="submit" class="btn btn-purple-gradient d-none d-md-block" style="border-radius: 0 50px 50px 0; padding: 0.5rem 1rem">
                <span>
                  <i class="bi bi-search text-purple"> Search</i>
                </span>
              </button>
            </div>
          </form>
        </div>
        <!-- Right Side Buttons -->
        <div class="d-flex align-items-center gap-2 ms-lg-3 order-lg-3">
          <a href="/Concert/user/cart/" class="btn btn-sm btn-outline-secondary d-none d-md-flex align-items-center" style="border-radius: 50px; padding: 0.5rem 0.9rem">
            <i class="bi bi-cart2"></i>
            <span class="ms-1 d-none d-lg-inline">Cart</span>
          </a>
          <a href="/Concert/user/profile.php" class="btn btn-sm btn-outline-secondary d-none d-md-flex align-items-center" style="border-radius: 50px; padding: 0.5rem 0.9rem">
            <i class="bi bi-person"></i>
            <span class="ms-1 d-none d-lg-inline">Profile</span>
          </a>

          <?php if (isset($_SESSION['UserID'])): ?>
            <!-- Tampilkan jika user sudah login -->
            <div id="navbarUserArea">
              <span id="navbarUserName" class="text-dark me-2">Hi, <?= htmlspecialchars($_SESSION['name'] ?? 'User') ?></span>
              <a href="/Concert/auth/logout.php" class="btn btn-sm btn-danger d-inline-flex align-items-center" style="border-radius: 50px; padding: 0.5rem 1rem">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
              </a>
            </div>
          <?php else: ?>
            <!-- Tampilkan jika user belum login -->
            <a href="/Concert/auth/login.html" id="navbarLoginBtn" class="btn btn-sm btn-purple-gradient d-none d-md-inline-flex align-items-center social-float" style="border-radius: 50px; padding: 0.5rem 1rem">
              <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </a>
            <a href="/Concert/auth/signup.html" id="navbarSignupBtn" class="btn btn-sm btn-purple-gradient d-none d-md-inline-flex align-items-center pulse-animation float-animation" style="border-radius: 50px; padding: 0.5rem 1rem">
              <i class="bi bi-person-plus me-1"></i> Sign Up
            </a>
          <?php endif; ?>
        </div>
      </div>
    </nav>

    <!-- Offcanvas Menu -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
      <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title fw-bold" id="sidebarMenuLabel"><span style="color: #8a4fff">Concert</span><span style="color: #ff6600">Tix</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>
      <div class="offcanvas-body">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a href="/Concert/public/" class="nav-link active d-flex align-items-center py-2"> <i class="bi bi-house-door me-3 fs-5" style="color: #8a4fff"></i> Home Page </a>
          </li>
          <li class="nav-item">
            <a href="/Concert/user/profile.php" class="nav-link d-flex align-items-center py-2"> <i class="bi bi-person me-3 fs-5" style="color: #8a4fff"></i> Profile </a>
          </li>
          <li class="nav-item">
            <a href="/Concert/user/cart/" class="nav-link d-flex align-items-center py-2"> <i class="bi bi-cart2 me-3 fs-5" style="color: #8a4fff"></i> Cart </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-2" href="/Concert/public/#all-concerts-section" data-bs-dismiss="offcanvas"> <i class="bi bi-ticket-perforated me-3 fs-5" style="color: #8a4fff"></i> Concert </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-2" href="/Concert/public/events/search.php"> <i class="bi bi-music-note-list me-3 fs-5" style="color: #8a4fff"></i> Genre </a>
          </li>
          <li class="nav-item">
            <a class="nav-link d-flex align-items-center py-2" href="/Concert/public/about.php"> <i class="bi bi-info-circle me-3 fs-5" style="color: #8a4fff"></i> About Us </a>
          </li>
        </ul>

       <hr class="my-3 mx-3" />
        <div class="px-3">
          <?php if (isset($_SESSION['UserID'])): ?>
            <!-- Tampilkan logout jika user sudah login -->
            <a href="/Concert/auth/logout.php" class="btn btn-danger w-100 d-flex align-items-center justify-content-center" style="border-radius: 50px; padding: 0.5rem 1rem">
              <i class="bi bi-box-arrow-right me-2"></i> Logout
            </a>
          <?php else: ?>
            <!-- Tampilkan login/signup jika user belum login -->
            <a href="/Concert/auth/login.html" class="btn btn-outline-purple w-100 mb-2 d-flex align-items-center justify-content-center" style="border-radius: 50px; padding: 0.5rem 1rem"> 
              <i class="bi bi-box-arrow-in-right me-2"></i> Login 
            </a>
            <a href="/Concert/auth/signup.html" class="btn btn-purple-gradient w-100 d-flex align-items-center justify-content-center pulse-animation float-animation" style="border-radius: 50px; padding: 0.5rem 1rem">
              <i class="bi bi-person-plus me-2"></i> Sign Up
            </a>
          <?php endif; ?>
        </div>
        <hr class="my-3 mx-3" />
        <div class="px-3">
          <h6 class="text-muted mb-3">Follow Us</h6>
          <div class="social-links d-flex justify-content-center gap-4">
            <a href="https://www.facebook.com/groups/3288439241475430/" class="text-decoration-none" style="color: #8a4fff">
              <i class="bi bi-facebook fs-4"></i>
            </a>
            <a href="https://www.instagram.com/xtoph.y?utm_source=ig_web_button_share_sheet&igsh=ZDNlZDc0MzIxNw==" class="text-decoration-none" style="color: #8a4fff">
              <i class="bi bi-instagram fs-4"></i>
            </a>
            <a href="https://x.com/home" class="text-decoration-none" style="color: #8a4fff">
              <i class="bi bi-twitter-x fs-4"></i>
            </a>
            <a href="https://www.youtube.com/@xant_san" class="text-decoration-none" style="color: #8a4fff">
              <i class="bi bi-youtube fs-4"></i>
            </a>
          </div>
        </div>
      </div>
    </div>

    <script>
        // tambahkan logout
      document.addEventListener("DOMContentLoaded", function () {
        // ... kode JavaScript yang sudah ada (misalnya untuk navbar scrolled, search form, offcanvas) ...

        checkLoginStatusAndRenderNavbar(); // Panggil fungsi baru ini

        // Event listener untuk tombol logout
        const logoutBtn = document.getElementById("navbarLogoutBtn");
        if (logoutBtn) {
          logoutBtn.addEventListener("click", function () {
            window.location.href = "/Concert/auth/logout.php"; // Arahkan ke skrip logout PHP
          });
        }
        function updateNavbarDisplay() {
          const isLoggedIn = <?= isset($_SESSION['UserID']) ? 'true' : 'false' ?>;
          const loginBtn = document.getElementById("navbarLoginBtn");
          const signupBtn = document.getElementById("navbarSignupBtn");
          const userArea = document.getElementById("navbarUserArea");
          
          if (isLoggedIn) {
            if (loginBtn) loginBtn.style.display = "none";
            if (signupBtn) signupBtn.style.display = "none";
            if (userArea) userArea.style.display = "flex";
          } else {
            if (loginBtn) loginBtn.style.display = "inline-flex";
            if (signupBtn) signupBtn.style.display = "inline-flex";
            if (userArea) userArea.style.display = "none";
          }
        }

        // Panggil fungsi saat halaman dimuat
        updateNavbarDisplay();
        // Fungsi untuk memeriksa status login dan merender navbar
        async function checkLoginStatusAndRenderNavbar() {
          try {
            const response = await fetch("../auth/check_login_status.php"); // Sesuaikan path jika perlu
            const data = await response.json();

            const loginBtn = document.getElementById("navbarLoginBtn");
            const signupBtn = document.getElementById("navbarSignupBtn");
            const userArea = document.getElementById("navbarUserArea");
            const userNameSpan = document.getElementById("navbarUserName");
            const profileLink = document.querySelector('a[href="/Concert/user/profile.php"]'); // Ambil link profil

            // --- Untuk Navbar Utama ---
            if (data.loggedIn) {
              // Sembunyikan Login & Sign Up
              if (loginBtn) loginBtn.style.display = "none"; // <-- Tambahkan ini
              if (signupBtn) signupBtn.style.display = "none"; // <-- Tambahkan ini

              // Tampilkan area user (nama & logout)
              if (userArea) userArea.classList.remove("d-none");
              if (userNameSpan) {
                userNameSpan.textContent = `Hi, ${data.userName}`;

                // Ubah warna teks username di navbar jika navbar di-scroll
                const mainNavbar = document.getElementById("mainNavbar");
                if (mainNavbar) {
                  const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                      if (mutation.attributeName === "class") {
                        if (mainNavbar.classList.contains("scrolled")) {
                          userNameSpan.style.color = "white"; // Ganti warna saat scrolled
                        } else {
                          userNameSpan.style.color = "var(--text-dark)"; // Warna default saat tidak scrolled
                        }
                      }
                    });
                  });
                  observer.observe(mainNavbar, { attributes: true });
                  // Set warna awal
                  if (mainNavbar.classList.contains("scrolled")) {
                    userNameSpan.style.color = "white";
                  } else {
                    userNameSpan.style.color = "var(--text-dark)"; // Sesuaikan dengan warna teks default navbar (ungu/hitam)
                  }
                }

                // Perbarui link profile untuk menampilkan nama pengguna di d-lg-inline
                if (profileLink) {
                  const profileSpan = profileLink.querySelector("span.d-none.d-lg-inline");
                  if (profileSpan) {
                    profileSpan.textContent = data.userName;
                  }
                }
              } else {
                // Tampilkan Login & Sign Up
                if (loginBtn) loginBtn.style.display = "inline-flex"; // <-- Tambahkan ini
                if (signupBtn) signupBtn.style.display = "inline-flex"; // <-- Tambahkan ini

                // Sembunyikan area user
                if (userArea) userArea.classList.add("d-none");

                // Reset link profile ke 'Profile'
                if (profileLink) {
                  const profileSpan = profileLink.querySelector("span.d-none.d-lg-inline");
                  if (profileSpan) {
                    profileSpan.textContent = "Profile";
                  }
                }
              }

              // --- Untuk Offcanvas Menu (jika ada di halaman ini) ---
              const offcanvasLoginBtn = document.querySelector('#sidebarMenu a[href="/Concert/auth/login.html"]');
              const offcanvasSignupBtn = document.querySelector('#sidebarMenu a[href="/Concert/auth/signup.html"]');
              const offcanvasLogoutBtn = document.createElement("a"); // Buat tombol logout baru untuk offcanvas
              const offcanvasProfileLink = document.querySelector('#sidebarMenu a[href="/Concert/user/profile.php"]');

              if (offcanvasLoginBtn && offcanvasSignupBtn) {
                // Pastikan elemen ada di offcanvas
                if (data.loggedIn) {
                  offcanvasLoginBtn.style.display = "none";
                  offcanvasSignupBtn.style.display = "none";

                  // Tambahkan tombol logout ke offcanvas
                  offcanvasLogoutBtn.href = "/Concert/auth/logout.php";
                  offcanvasLogoutBtn.className = "btn btn-danger w-100 mt-2 d-flex align-items-center justify-content-center";
                  offcanvasLogoutBtn.style.borderRadius = "50px";
                  offcanvasLogoutBtn.style.padding = "0.5rem 1rem";
                  offcanvasLogoutBtn.innerHTML = '<i class="bi bi-box-arrow-right me-2"></i> Logout';
                  // Cari div px-3 tempat tombol login/signup berada dan tambahkan tombol logout
                  const px3Div = offcanvasLoginBtn.closest(".px-3");
                  if (px3Div) {
                    px3Div.appendChild(offcanvasLogoutBtn);
                  }
                  if (offcanvasProfileLink) {
                    const profileSpanOffcanvas = offcanvasProfileLink.querySelector("span"); // Di offcanvas, span biasanya tidak memiliki kelas d-lg-inline
                    if (profileSpanOffcanvas) {
                      // Karena offcanvas memiliki struktur icon + text langsung, kita mungkin perlu sedikit penyesuaian
                      // Asumsi text adalah child node langsung dari 'a' atau di dalam span tanpa kelas khusus
                      //  profileSpanOffcanvas.textContent = ` ${data.userName}`; // Tambahkan nama setelah ikon
                      //  offcanvasProfileLink.innerHTML = `<i class="bi bi-person me-3 fs-5" style="color: #8a4fff"></i> ${data.userName}`;
                      const profileIcon = offcanvasProfileLink.querySelector("i");
                      if (profileIcon) {
                        profileIcon.nextSibling.textContent = ` ${data.userName}`; // Set teks setelah ikon
                      } else {
                        offcanvasProfileLink.textContent = `Profile (${data.userName})`; // Fallback jika tidak ada ikon
                      }
                    }
                  }
                } else {
                  offcanvasLoginBtn.style.display = "inline-flex";
                  offcanvasSignupBtn.style.display = "inline-flex";
                  // Hapus tombol logout jika ada dari sesi sebelumnya
                  if (offcanvasLogoutBtn.parentNode) {
                    offcanvasLogoutBtn.parentNode.removeChild(offcanvasLogoutBtn);
                  }
                  if (offcanvasProfileLink) {
                    const profileIcon = offcanvasProfileLink.querySelector("i");
                    if (profileIcon) {
                      profileIcon.nextSibling.textContent = " Profile"; // Reset teks setelah ikon
                    } else {
                      offcanvasProfileLink.textContent = "Profile";
                    }
                  }
                }
              }
            }
          } catch (error) {
            console.error("Error checking login status:", error);
            // Fallback: biarkan tombol login/signup terlihat jika ada error
            const loginBtn = document.getElementById("navbarLoginBtn");
            const signupBtn = document.getElementById("navbarSignupBtn");
            if (loginBtn) loginBtn.classList.remove("d-none");
            if (signupBtn) signupBtn.classList.remove("d-none");
          }
        }
      });

      // Make navbar change on scroll
      window.addEventListener("scroll", function () {
        const navbar = document.querySelector(".navbar");
        if (window.scrollY > 50) {
          navbar.classList.add("scrolled");
        } else {
          navbar.classList.remove("scrolled");
        }
      });

      // Navbar toggler
        const navbarToggler = document.querySelector(".navbar-toggler");
        const navbarCollapse = document.querySelector(".navbar-collapse");
        if (navbarToggler && navbarCollapse) {
          navbarToggler.addEventListener("click", function () {
            navbarCollapse.classList.toggle("show");
          });
        }

        // Handle search form submission
      document.getElementById("searchForm")?.addEventListener("submit", function (e) {
        e.preventDefault();
        const query = document.getElementById("searchInput").value.trim();
        if (query) {
          window.location.href = `/Concert/public/events/search.php?q=${encodeURIComponent(query)}`;
        }
      });

      // Fungsi untuk update cart count di navbar
  async function updateCartCount() {
    try {
      const response = await fetch('/Concert/auth/get_cart_count.php');
      const data = await response.json();
      
      if (data.success) {
        const cartBadges = document.querySelectorAll('.cart-badge');
        cartBadges.forEach(badge => {
          badge.textContent = data.count;
          badge.style.display = data.count > 0 ? 'inline-block' : 'none';
        });
      }
    } catch (error) {
      console.error('Error updating cart count:', error);
    }
  }
    </script>
</body>
</html>