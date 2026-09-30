<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/connection.php";

// Preuzimanje trenutnog naziva skripte radi dinamičkog postavljanja active klase
$current_page = basename($_SERVER['PHP_SELF']);

// Učitavanje stavki navigacije iz baze
$nav_sql = "SELECT * FROM navigation WHERE is_active = 1 ORDER BY sort_order ASC";
$nav_stmt = $conn->query($nav_sql);
$nav_items = $nav_stmt ? $nav_stmt->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Najpovoljnije iznajmljivanje vozila. Rezervišite auto brzo i lako.">
    <meta name="author" content="Rent a Car">
    <link rel="icon" type="image/png" href="assets/img/favicon.png">
    <title>Rent-a-Car Premium</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top z-3 shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">
      <i class="fa-solid fa-car-side me-2"></i>Rent a Car Premium
    </a>
    
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="offcanvas offcanvas-end custom-offcanvas" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
      <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
        <h5 class="offcanvas-title text-primary fw-bold" id="offcanvasNavbarLabel">
          <i class="fa-solid fa-car-side me-2"></i>Rent-a-Car
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
      </div>

      <div class="offcanvas-body d-flex flex-column flex-lg-row justify-content-start justify-content-lg-between">
        <ul class="navbar-nav me-lg-auto mb-3 mb-lg-0">
          <?php foreach ($nav_items as $item): ?>
              <?php $isActive = ($current_page === $item->url) ? 'active' : ''; ?>
              <li class="nav-item">
                <a class="nav-link <?= $isActive ?>" href="<?= htmlspecialchars($item->url) ?>">
                  <?= htmlspecialchars($item->title) ?>
                </a>
              </li>
          <?php endforeach; ?>
        </ul>
        
        <div class="d-flex align-items-lg-center pt-2 pt-lg-0">
          <?php if (isset($_SESSION['korisnik_id'])): ?>
            <div class="dropdown dropup-mobile w-100 w-lg-auto">
              <button class="btn btn-outline-light dropdown-toggle w-100 text-start text-lg-center" type="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user me-1"></i> <?= htmlspecialchars($_SESSION['ime_prezime']) ?>
              </button>
              <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="userMenu">
                <li><a class="dropdown-item" href="profile.php"><i class="fa-solid fa-bookmark me-2"></i>Moje rezervacije</a></li>
                
                <?php if ($_SESSION['uloga_id'] == 1 || $_SESSION['uloga_id'] == 2): ?>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-primary fw-bold" href="admin.php"><i class="fa-solid fa-gauge me-2"></i>Admin Panel</a></li>
                <?php endif; ?>

                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket me-2"></i>Odjavi se</a></li>
              </ul>
            </div>
          <?php else: ?>
            <div class="d-grid d-lg-flex gap-2 w-100">
              <a href="login.php" class="btn btn-outline-light me-lg-2">Prijava</a>
              <a href="register.php" class="btn btn-primary">Registracija</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</nav>
