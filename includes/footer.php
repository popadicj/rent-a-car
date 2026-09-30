<?php
if (!isset($conn)) {
    require_once "config/connection.php";
}

$footer_nav_sql = "SELECT * FROM navigation WHERE is_active = 1 ORDER BY sort_order ASC";
$footer_nav_stmt = $conn->query($footer_nav_sql);
$footer_links = $footer_nav_stmt ? $footer_nav_stmt->fetchAll() : [];
?>

<footer class="main-footer bg-dark text-white pt-5 pb-3">
  <div class="container">
    <div class="row g-4 pb-4 border-bottom border-secondary">
      
      <!-- O kompaniji -->
      <div class="col-lg-4 col-md-6">
        <a class="navbar-brand fw-bold text-primary fs-4 d-block mb-3" href="index.php">
          <i class="fa-solid fa-car-side me-2"></i>Rent-a-Car
        </a>
        <p class="text-secondary small mb-3">
          Vaš siguran partner za najam premium i pouzdanih vozila po najpovoljnijim uslovima. Bez skrivenih troškova, uz potpunu podršku na putu 24/7.
        </p>
        <div class="footer-socials d-flex gap-2">
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-instagram"></i></a>
          <a href="#" class="btn btn-outline-light btn-sm rounded-circle"><i class="fab fa-linkedin-in"></i></a>
        </div>
      </div>
      <div class="col-lg-2 col-md-6">
        <h6 class="text-white fw-bold mb-3 text-uppercase fs-7">Brzi Linkovi</h6>
        <ul class="list-unstyled footer-links">
          <?php foreach ($footer_links as $f_link): ?>
            <li class="mb-2">
              <a href="<?= htmlspecialchars($f_link->url) ?>" class="text-secondary text-decoration-none small">
                <i class="bi bi-chevron-right me-1 text-primary"></i> <?= htmlspecialchars($f_link->title) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-bold mb-3 text-uppercase fs-7">Kontakt</h6>
        <ul class="list-unstyled text-secondary small">
          <li class="mb-2"><i class="bi bi-geo-alt-fill me-2 text-primary"></i> Bulevar Oslobođenja 12, Beograd</li>
          <li class="mb-2"><i class="bi bi-telephone-fill me-2 text-primary"></i> +381 11 123 4567</li>
          <li class="mb-2"><i class="bi bi-envelope-fill me-2 text-primary"></i> info@rentacar.rs</li>
          <li class="mb-2"><i class="bi bi-clock-fill me-2 text-primary"></i> Radno vreme: 08:00 - 20:00h</li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6">
        <h6 class="text-white fw-bold mb-3 text-uppercase fs-7">Sigurnost i Povrenje</h6>
        <p class="text-secondary small mb-2">Sva naša vozila su osigurana Kasko osiguranjem i redovno servisirana u ovlašćenim servisima.</p>
        <div class="d-flex align-items-center gap-2 mt-3 text-muted fs-3">
          <i class="fa-brands fa-cc-visa text-light"></i>
          <i class="fa-brands fa-cc-mastercard text-light"></i>
          <i class="fa-brands fa-cc-paypal text-light"></i>
        </div>
      </div>
    </div>
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center pt-3 small text-secondary">
      <p class="mb-0">&copy; <?= date('Y') ?> Rent-a-Car Premium. Sva prava zadržana.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>