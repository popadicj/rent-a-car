<?php
session_start();
require_once "config/connection.php";

$review_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (isset($_SESSION['korisnik_id'])) {
        $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
        $comment = trim($_POST['comment']);
        $vehicle_id = filter_var($_POST['vehicle_id'], FILTER_VALIDATE_INT);
        $user_id = $_SESSION['korisnik_id'];

        if ($rating >= 1 && $rating <= 5 && !empty($comment) && $vehicle_id) {
            $insert_sql = "INSERT INTO reviews (user_id, vehicle_id, rating, comment) VALUES (?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($insert_sql);
            if ($stmt_ins->execute([$user_id, $vehicle_id, $rating, $comment])) {
                $review_msg = "<div class='alert alert-success alert-dismissible fade show container mt-3'>Uspešno ste ostavili recenziju! Hvala vam.<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            }
        }
    }
}

$user_id = $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

$user_vehicles = [];

if ($user_id) {
    $stmt = $conn->prepare("
        SELECT DISTINCT v.id, v.brand, v.model 
        FROM vehicles v
        JOIN reservations r ON v.id = r.vehicle_id
        WHERE r.user_id = :user_id
        ORDER BY v.brand ASC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $user_vehicles = $stmt->fetchAll();
}

$reviews_sql = "SELECT r.*, CONCAT(u.first_name, ' ', u.last_name) AS ime_prezime, v.brand, v.model 
                FROM reviews r 
                JOIN users u ON r.user_id = u.id 
                JOIN vehicles v ON r.vehicle_id = v.id 
                ORDER BY r.created_at DESC LIMIT 6";
$reviews_stmt = $conn->query($reviews_sql);
$reviews = $reviews_stmt ? $reviews_stmt->fetchAll() : [];

$sql = "SELECT v.*, f.name AS fuel_name, t.name AS transmission_name
        FROM vehicles v
        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
        LEFT JOIN transmissions t ON v.transmission_id = t.id
        WHERE v.is_popular = 1
        LIMIT 6";
$vozila = $conn->query($sql)->fetchAll();

$faqs_stmt = $conn->query("SELECT * FROM faqs WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 6");
$faqs = $faqs_stmt ? $faqs_stmt->fetchAll() : [];

require_once "includes/header.php";
?>

<!-- HERO SEKCIJA -->
<section class="hero-section text-white text-center pt-5 pb-5 position-relative">
    <div class="container pt-5 pb-4">
        <span class="hero-badge mb-3 d-inline-block">RENT A CAR PREMIUM </span>
        <h1 class="display-4 fw-extrabold mb-3">Vozite sa stilom i stavom</h1>
        <p class="lead mb-4 text-light-50 max-w-600 mx-auto">Izaberite iz naše flote vrhunski održavanih vozila po najpovoljnijim uslovima.</p>
        <a href="vehicles.php" class="btn btn-hero mb-3">Istraži celu flotu <i class="bi bi-arrow-right ms-2"></i></a>
    </div>
</section>

<!-- LEBDEĆA SEKCIJA -->
<section class="floating-trust-wrapper">
    <div class="container">
        <div class="floating-trust-card bg-white rounded-4 shadow-lg p-4 border">
            <div class="row g-4 text-center align-items-center">
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <i class="bi bi-shield-check fs-2 text-primary"></i>
                        <div class="text-start">
                            <h6 class="mb-0 fw-bold">Kasko Osiguranje</h6>
                            <small class="text-muted">Potpuna pokrivenost</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <i class="bi bi-wallet2 fs-2 text-primary"></i>
                        <div class="text-start">
                            <h6 class="mb-0 fw-bold">Bez Skrivenih Cena</h6>
                            <small class="text-muted">Transparentan obračun</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <i class="bi bi-geo-alt fs-2 text-primary"></i>
                        <div class="text-start">
                            <h6 class="mb-0 fw-bold">Dostava Vozila</h6>
                            <small class="text-muted">Aerodrom / vaša adresa</small>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="d-flex align-items-center justify-content-center gap-3">
                        <i class="bi bi-headset fs-2 text-primary"></i>
                        <div class="text-start">
                            <h6 class="mb-0 fw-bold">24/7 Podrška</h6>
                            <small class="text-muted">Uvek na raspolaganju</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- IZDVOJENA VOZILA -->
<main class="container my-5 pt-4 pb-5">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <div>
            <h2 class="section-title mb-1">Izdvojeni Modeli</h2>
            <p class="text-muted small mb-0">Najpopularniji izbor naših klijenata ove sezone</p>
        </div>
        <a href="vehicles.php" class="see-all-link fw-bold">Sva vozila <i class="bi bi-arrow-right"></i></a>
    </div>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($vozila as $v): ?>
            <div class="col">
                <div class="car-card h-100">
                    <div class="car-card-img-wrap">
                        <img src="assets/img/<?= htmlspecialchars($v->image) ?>" alt="<?= htmlspecialchars($v->brand) ?>">
                        <span class="car-year-badge"><?= htmlspecialchars($v->year) ?>.</span>
                    </div>
                    <div class="car-card-body d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="car-title mb-2"><?= htmlspecialchars($v->brand) ?> <?= htmlspecialchars($v->model) ?></h5>

                            <div class="car-tags mb-3 d-flex gap-2">
                                <span class="badge bg-light text-dark fw-normal p-2 border">
                                    <i class="bi bi-fuel-pump-fill text-primary me-1"></i> 
                                    <?= htmlspecialchars($v->fuel_name ?? 'N/A') ?>
                                </span>
                                <span class="badge bg-light text-dark fw-normal p-2 border">
                                    <i class="bi bi-gear-wide-connected text-primary me-1"></i> 
                                    <?= htmlspecialchars($v->transmission_name ?? 'Automatik') ?>
                                </span>
                            </div>
                        </div>

                        <div class="car-card-footer mt-3">
                            <div class="price-box">
                                <span class="price-label">Cena / dan</span>
                                <div class="price-amount"><?= number_format($v->price_per_day, 0, ',', '.') ?> <span>€</span></div>
                            </div>
                            <a href="vehicle-details.php?id=<?= $v->id ?>" class="btn car-btn">Rezervišite</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<!-- RECENZIJE SEKCIJA SA SLIDER-OM -->
<section class="py-5 bg-light border-top border-bottom">
    <div class="container py-3">
        <!-- Zamenjen d-flex sa responsive row mrežom -->
        <div class="row align-items-center mb-4 pb-2 border-bottom g-3">
            
            <!-- 1. Naslov i podnaslov -->
            <div class="col-12 col-md-6">
                <h2 class="h3 fw-bold mb-1 text-dark">Šta kažu vozači o nama</h2>
                <p class="text-muted small mb-0">Utisci i ocene naših klijenata</p>
            </div>
            
            <!-- 2. Akcije: Dugme/Prijava + Strelice -->
            <div class="col-12 col-md-6 d-flex align-items-center justify-content-between justify-content-md-end gap-3">
                <?php if (isset($_SESSION['korisnik_id'])): ?>
                    <button type="button" class="btn btn-primary fw-bold fs-7 px-3 py-2" data-bs-toggle="modal" data-bs-target="#reviewModal">
                        <i class="bi bi-pencil-square me-2"></i>Ostavi recenziju
                    </button>
                <?php else: ?>
                    <small class="text-muted">
                        <a href="login.php" class="text-primary fw-bold">Prijavite se</a> da biste ostavili utisak.
                    </small>
                <?php endif; ?>

                <!-- Strelice za slider -->
                <div class="d-flex gap-2 ms-md-2">
                    <button class="btn btn-outline-dark rounded-circle review-prev p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="btn btn-outline-dark rounded-circle review-next p-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
        <div class="swiper reviews-swiper">
            <div class="swiper-wrapper py-2">
                <?php if (!empty($reviews)): ?>
                    <?php foreach ($reviews as $rev): ?>
                        <div class="swiper-slide">
                            <div class="card h-100 border shadow-sm p-3 rounded-3 bg-white">
                                <div class="card-body p-2 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="text-warning small">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star-fill <?= $i <= $rev->rating ? '' : 'text-muted opacity-25' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="badge bg-light text-dark border">
                                                <i class="bi bi-car-front text-primary me-1"></i> 
                                                <?= htmlspecialchars($rev->brand) ?> <?= htmlspecialchars($rev->model) ?>
                                            </span>
                                        </div>
                                        <p class="card-text text-secondary fst-italic mb-4">
                                            "<?= htmlspecialchars($rev->comment) ?>"
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center gap-3 border-top pt-3">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold avatar-circle" style="width: 40px; height: 40px;">
                                            <?= strtoupper(substr($rev->ime_prezime, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark small"><?= htmlspecialchars($rev->ime_prezime) ?></h6>
                                            <small class="text-muted" style="font-size: 0.75rem;"><?= date('d.m.Y.', strtotime($rev->created_at)) ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4 w-100">Budite prvi koji će ostaviti utisak o našim vozilima!</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- MODAL ZA OSTAVLJANJE RECENZIJE -->
<?php if (isset($_SESSION['korisnik_id'])): ?>
<section class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="reviewModalLabel">Napišite vaše iskustvo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zatvori"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body py-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Izaberite vozilo</label>
                       <select name="vehicle_id" class="form-select" required>
                        <?php if (!empty($user_vehicles)): ?>
                            <option value="">Izaberite vozilo koje ste iznajmljivali</option>
                            <?php foreach ($user_vehicles as $v): ?>
                                <option value="<?= $v->id ?>"><?= htmlspecialchars($v->brand . ' ' . $v->model) ?></option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled selected>Nemate rezervacija za ocenjivanje</option>
                        <?php endif; ?>
                    </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Vaša ocena</label>
                        <select name="rating" class="form-select" required>
                            <option value="5" selected>5 - Odlično</option>
                            <option value="4">4 - Veoma dobro</option>
                            <option value="3">3 - Prosečno</option>
                            <option value="2">2 - Loše</option>
                            <option value="1">1 - Veoma loše</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">Komentar</label>
                        <textarea name="comment" class="form-control" rows="4" placeholder="Podelite vaše utiske o automobilu i usluzi..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Odustani</button>
                    <button type="submit" name="submit_review" class="btn btn-primary fw-bold px-4">Objavi recenziju</button>
                </div>
            </form>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- FAQ SEKCIJA -->
<section class="faq-dark-section py-5 text-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-title text-white">Često postavljana pitanja</h2>
            <p class="text-light opacity-75 small">Brzi odgovori na sve vaše nedoumice pre najma</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (!empty($faqs)): ?>
                    <div class="accordion custom-dark-accordion" id="faqAccordion">
                        <?php foreach ($faqs as $f): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?= $f->id ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $f->id ?>" aria-expanded="false" aria-controls="collapse<?= $f->id ?>">
                                        <?= htmlspecialchars($f->question) ?>
                                    </button>
                                </h2>
                                <div id="collapse<?= $f->id ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $f->id ?>" data-bs-parent="#faqAccordion">
                                    <div class="accordion-body">
                                        <?= nl2br(htmlspecialchars($f->answer)) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">Trenutno nema unetih pitanja.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="text-center mt-4">
        <a href="conditions.php" class="btn btn-outline-primary rounded-pill px-4">
            Pogledajte kompletne uslove najma <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
    </div>
</section>

<?php require_once "includes/footer.php"; ?>