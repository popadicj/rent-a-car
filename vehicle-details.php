<?php
session_start();
require_once "config/connection.php";
require_once "includes/header.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT v.*, f.name AS fuel_name, t.name AS transmission_name 
                        FROM vehicles v
                        LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
                        LEFT JOIN transmissions t ON v.transmission_id = t.id
                        WHERE v.id = :id");
$stmt->execute(['id' => $id]);
$vozilo = $stmt->fetch();

if (!$vozilo) {
    echo "<div class='container my-5 py-5 text-center'><div class='alert alert-danger d-inline-block px-5'>Vozilo nije pronađeno.</div><br><a href='vehicles.php' class='btn btn-outline-dark mt-3'>Nazad na sva vozila</a></div>";
    require_once "includes/footer.php";
    exit;
}

$poruka = "";
$greska = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['rezervisi'])) {
    if (!isset($_SESSION['korisnik_id'])) {
        $greska = "Morate biti prijavljeni da biste rezervisali vozilo.";
    } else {
        $datum_od = $_POST['datum_od'];
        $datum_do = $_POST['datum_do'];
        $korisnik_id = $_SESSION['korisnik_id'];

        if (empty($datum_od) || empty($datum_do)) {
            $greska = "Morate izabrati oba datuma!";
        } elseif ($datum_od >= $datum_do) {
            $greska = "Datum završetka mora biti posle datuma početka!";
        } else {
            $check = $conn->prepare("SELECT * FROM reservations WHERE vehicle_id = :vehicle_id AND status != 'cancelled' AND ((start_date <= :datum_do AND end_date >= :datum_od))");
            $check->execute([
                'vehicle_id' => $id,
                'datum_od' => $datum_od,
                'datum_do' => $datum_do
            ]);

            if ($check->rowCount() > 0) {
                $greska = "Vozilo je već rezervisano u izabranom periodu!";
            } else {
                $d1 = new DateTime($datum_od);
                $d2 = new DateTime($datum_do);
                $dani = $d1->diff($d2)->days;
                $ukupna_cena = $dani * $vozilo->price_per_day;

                $ins = $conn->prepare("INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, total_price, status) VALUES (:user_id, :vehicle_id, :start_date, :end_date, :total_price, 'pending')");
                $ins->execute([
                    'user_id' => $korisnik_id,
                    'vehicle_id' => $id,
                    'start_date' => $datum_od,
                    'end_date' => $datum_do,
                    'total_price' => $ukupna_cena
                ]);

                $poruka = "Uspešno ste poslali zahtev za rezervaciju! Ukupna cena: " . number_format($ukupna_cena, 0, ',', '.') . " €.";
            }
        }
    }
}
?>

<main class="container my-5 pt-2">
    <div class="mb-4">
        <a href="vehicles.php" class="text-decoration-none text-muted small fw-bold"><i class="bi bi-arrow-left me-1"></i> Nazad na sva vozila</a>
    </div>

    <?php if ($poruka): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm"><?= $poruka ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($greska): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm"><?= $greska ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-4 align-items-start">
        <div class="col-lg-7">
            <div class="bg-white rounded-3 border overflow-hidden mb-4 p-2">
                <img src="assets/img/<?= htmlspecialchars($vozilo->image) ?>" class="img-fluid w-100 rounded-3" style="max-height: 420px; object-fit: cover;" alt="<?= htmlspecialchars($vozilo->brand) ?>">
            </div>

            <div class="bg-white rounded-3 border p-4 mb-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-info-circle me-2 text-primary"></i>Specifikacije vozila</h5>
                
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-calendar-check fs-4 text-primary mb-1"></i>
                            <span class="text-muted small">Godište</span>
                            <span class="fw-bold text-dark mt-1"><?= htmlspecialchars($vozilo->year) ?>.</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-fuel-pump fs-4 text-primary mb-1"></i>
                            <span class="text-muted small">Gorivo</span>
                            <span class="fw-bold text-dark mt-1"><?= htmlspecialchars($vozilo->fuel_name ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-gear-wide-connected fs-4 text-primary mb-1"></i>
                            <span class="text-muted small">Menjač</span>
                            <span class="fw-bold text-dark mt-1"><?= htmlspecialchars($vozilo->transmission_name ?? 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-people fs-4 text-primary mb-1"></i>
                            <span class="text-muted small">Sedišta</span>
                            <span class="fw-bold text-dark mt-1"><?= htmlspecialchars($vozilo->seats ?? 5) ?> mesta</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-lightning-charge fs-4 text-primary mb-1"></i>
                            <span class="text-muted small">Snaga</span>
                            <span class="fw-bold text-dark mt-1"><?= htmlspecialchars($vozilo->engine_power ?? 'N/A') ?> KS</span>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 bg-light rounded-3 text-center h-100 d-flex flex-column justify-content-center">
                            <i class="bi bi-door-closed fs-4 text-primary mb-1"></i>
                            <span class="text-muted small">Vrata</span>
                            <span class="fw-bold text-dark mt-1"><?= htmlspecialchars($vozilo->doors ?? 5) ?> vrata</span>
                        </div>
                    </div>
                </div>

                <?php if (!empty($vozilo->description)): ?>
                    <div class="border-top pt-3 mb-3">
                        <h6 class="fw-bold text-dark mb-2">Opis i oprema</h6>
                        <p class="text-muted small mb-0" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($vozilo->description)) ?></p>
                    </div>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top">
                    <div class="row text-muted small g-2">
                        <div class="col-md-6 d-flex align-items-center"><i class="bi bi-shield-check text-success fs-5 me-2"></i> Kasko osiguranje uključeno</div>
                        <div class="col-md-6 d-flex align-items-center"><i class="bi bi-speedometer2 text-success fs-5 me-2"></i> Neograničena kilometraža</div>
                        <div class="col-md-6 d-flex align-items-center"><i class="bi bi-headset text-success fs-5 me-2"></i> 24/7 Podrška na putu</div>
                        <div class="col-md-6 d-flex align-items-center"><i class="bi bi-x-circle text-success fs-5 me-2"></i> Besplatno otkazivanje</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DESNA STRANA: REZERVACIONI WIDGET -->
        <div class="col-lg-5">
            <div class="bg-white rounded-3 border p-4 position-sticky" style="top: 20px;">
                <h3 class="fw-extrabold mb-1"><?= htmlspecialchars($vozilo->brand) ?> <?= htmlspecialchars($vozilo->model) ?></h3>
                
                <div class="d-flex align-items-baseline my-3 pb-3 border-bottom">
                    <span class="fs-2 fw-bold text-primary me-2"><?= number_format($vozilo->price_per_day, 0, ',', '.') ?></span>
                    <span class="text-muted fw-semibold">€ / dan</span>
                </div>

                <h6 class="fw-bold mb-3">Izaberite period najma:</h6>
                
                <form action="vehicle-details.php?id=<?= $vozilo->id ?>" method="POST">
                    <div class="mb-3">
                        <label for="datum_od" class="form-label fw-bold small text-muted">Datum preuzimanja</label>
                        <input type="date" class="form-control" id="datum_od" name="datum_od" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="datum_do" class="form-label fw-bold small text-muted">Datum vraćanja</label>
                        <input type="date" class="form-control" id="datum_do" name="datum_do" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="card bg-light border-0 p-3 mb-4 d-none" id="priceCalculationCard">
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span>Broj dana:</span>
                            <span id="daysCount" class="fw-bold text-dark">0</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="fw-bold text-dark">Ukupno za plaćanje:</span>
                            <span id="totalPrice" class="fw-bold text-primary fs-4">0 €</span>
                        </div>
                    </div>

                    <button type="submit" name="rezervisi" class="btn btn-primary btn-lg w-100 fw-bold fs-6 py-3">
                        Potvrdi rezervaciju <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const datumOd = document.getElementById("datum_od");
    const datumDo = document.getElementById("datum_do");
    const calcCard = document.getElementById("priceCalculationCard");
    const daysCount = document.getElementById("daysCount");
    const totalPrice = document.getElementById("totalPrice");
    const pricePerDay = <?= floatval($vozilo->price_per_day) ?>;

    function calculate() {
        if (datumOd.value && datumDo.value) {
            const d1 = new Date(datumOd.value);
            const d2 = new Date(datumDo.value);

            if (d2 > d1) {
                const diffTime = Math.abs(d2 - d1);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                const total = diffDays * pricePerDay;

                daysCount.textContent = diffDays + " " + (diffDays === 1 ? "dan" : "dana");
                totalPrice.textContent = total.toLocaleString('sr-RS') + "€";
                calcCard.classList.remove("d-none");
            } else {
                calcCard.classList.add("d-none");
            }
        }
    }

    datumOd.addEventListener("change", function() {
        if (datumOd.value) {
            datumDo.min = datumOd.value;
        }
        calculate();
    });
    datumDo.addEventListener("change", calculate);
});
</script>

<?php require_once "includes/footer.php"; ?>