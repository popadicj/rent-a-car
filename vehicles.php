<?php
session_start();
require_once "config/connection.php";
require_once "includes/header.php";

$brand = $_GET['brand'] ?? '';
$fuel_id = $_GET['fuel_id'] ?? '';
$transmission_id = $_GET['transmission_id'] ?? '';
$year = $_GET['year'] ?? '';
$max_price = $_GET['max_price'] ?? '';

$query = "SELECT v.*, f.name AS fuel_name, t.name AS transmission_name 
          FROM vehicles v
          LEFT JOIN fuel_types f ON v.fuel_type_id = f.id
          LEFT JOIN transmissions t ON v.transmission_id = t.id
          WHERE 1=1";
$params = [];

if (!empty($brand)) {
    $query .= " AND v.brand = :brand";
    $params[':brand'] = $brand;
}

if (!empty($fuel_id)) {
    $query .= " AND v.fuel_type_id = :fuel_id";
    $params[':fuel_id'] = $fuel_id;
}

if (!empty($transmission_id)) {
    $query .= " AND v.transmission_id = :transmission_id";
    $params[':transmission_id'] = $transmission_id;
}

if (!empty($year)) {
    $query .= " AND v.year >= :year";
    $params[':year'] = $year;
}

if (!empty($max_price)) {
    $query .= " AND v.price_per_day <= :max_price";
    $params[':max_price'] = $max_price;
}

$stmt = $conn->prepare($query);
$stmt->execute($params);
$vozila = $stmt->fetchAll();


$brands = $conn->query("SELECT DISTINCT brand FROM vehicles ORDER BY brand ASC")->fetchAll(PDO::FETCH_COLUMN);
$fuels = $conn->query("SELECT * FROM fuel_types ORDER BY name ASC")->fetchAll();
$transmissions = $conn->query("SELECT * FROM transmissions ORDER BY name ASC")->fetchAll();
$years = $conn->query("SELECT DISTINCT year FROM vehicles ORDER BY year DESC")->fetchAll(PDO::FETCH_COLUMN);
$max_price_db = $conn->query("SELECT MAX(price_per_day) FROM vehicles")->fetchColumn() ?: 200;
?>

<main class="container my-5 pt-3">
    <div class="d-flex justify-content-between align-items-end mb-4 border-bottom pb-3">
        <div>
            <h2 class="section-title mb-1">Sva Vozila</h2>
            <p class="text-muted small mb-0">Pronađite idealno vozilo za vaše potrebe (<?= count($vozila) ?>)</p>
        </div>
        <?php if (!empty($brand) || !empty($fuel_id) || !empty($transmission_id) || !empty($year) || !empty($max_price)): ?>
            <a href="vehicles.php" class="see-all-link small text-danger"><i class="bi bi-x-circle me-1"></i> Poništi filtere</a>
        <?php endif; ?>
    </div>
    <div class="d-lg-none mb-3">
        <button class="btn btn-outline-dark w-100 fw-bold" type="button" data-bs-toggle="offcanvas" data-bs-target="#filteriOffcanvas">
            <i class="bi bi-funnel me-2"></i> Prikaži filtere
        </button>
    </div>

    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start rounded-3" tabindex="-1" id="filteriOffcanvas">
                
                <div class="offcanvas-header border-bottom">
                    <h5 class="offcanvas-title fw-bold">Filteri</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filteriOffcanvas"></button>
                </div>

                <div class="offcanvas-body p-0">
                    <div class="filter-card p-4 rounded-3 border bg-white w-100">
                        <h5 class="fw-bold mb-3 border-bottom pb-2 d-none d-lg-block">Filteri</h5>

                        <form method="GET" action="vehicles.php" id="filterForm">
                            
                            <!-- Filter: Marka -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Marka vozila</label>
                                <select name="brand" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Sve marke</option>
                                    <?php foreach ($brands as $b): ?>
                                        <option value="<?= htmlspecialchars($b) ?>" <?= $brand === $b ? 'selected' : '' ?>><?= htmlspecialchars($b) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filter: Gorivo (iz fuel_types) -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Tip goriva</label>
                                <select name="fuel_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Svi tipovi</option>
                                    <?php foreach ($fuels as $f): ?>
                                        <option value="<?= $f->id ?>" <?= $fuel_id == $f->id ? 'selected' : '' ?>><?= htmlspecialchars($f->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filter: Menjač -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Tip menjača</label>
                                <select name="transmission_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Svi menjači</option>
                                    <?php foreach ($transmissions as $t): ?>
                                        <option value="<?= $t->id ?>" <?= $transmission_id == $t->id ? 'selected' : '' ?>><?= htmlspecialchars($t->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filter: Godište -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Minimalno godište</label>
                                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Sva godišta</option>
                                    <?php foreach ($years as $y): ?>
                                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?>. i mlađe</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Filter: Cena -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label small fw-bold text-muted mb-0">Maks. cena/dan</label>
                                    <span class="fw-bold text-primary small ms-2" id="priceValue">
                                        <?= number_format((float)($max_price ?: $max_price_db), 0, ',', '.') ?> €
                                    </span>
                                </div>
                                <input type="range" class="form-range" name="max_price" min="10" max="<?= $max_price_db ?>" step="10" 
                                    value="<?= htmlspecialchars($max_price ?: $max_price_db) ?>" 
                                    oninput="document.getElementById('priceValue').innerText = Number(this.value).toLocaleString('sr-RS') + ' €'" 
                                    onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </aside>

        <!-- DESNI PRIKAZ VOZILA -->
        <section class="col-lg-9">
            <?php if (!empty($vozila)): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($vozila as $v): ?>
                        <div class="col">
                            <div class="car-card">
                                <div class="car-card-img-wrap">
                                    <img src="assets/img/<?= htmlspecialchars($v->image) ?>" alt="<?= htmlspecialchars($v->brand) ?>">
                                    <span class="car-year-badge"><?= htmlspecialchars($v->year) ?>.</span>
                                </div>
                                <div class="car-card-body">
                                    <h5 class="car-title mb-2"><?= htmlspecialchars($v->brand) ?> <?= htmlspecialchars($v->model) ?></h5>

                                    <div class="car-tags mb-3">
                                        <span class="car-tag"><i class="bi bi-fuel-pump-fill me-1"></i> <?= htmlspecialchars($v->fuel_name ?? 'N/A') ?></span>
                                        <span class="car-tag"><i class="bi bi-gear-wide-connected me-1"></i> <?= htmlspecialchars($v->transmission_name ?? 'N/A') ?></span>
                                    </div>

                                    <div class="car-card-footer">
                                        <div class="price-box">
                                            <span class="price-label">Cena / dan</span>
                                            <div class="price-amount"><?= number_format($v->price_per_day, 0, ',', '.') ?> <span>€</span></div>
                                        </div>
                                        <a href="vehicle-details.php?id=<?= $v->id ?>" class="btn car-btn">Pogledaj</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 border">
                    <i class="bi bi-search fs-1 text-muted d-block mb-3"></i>
                    <h5 class="fw-bold">Nema vozila sa izabranim filterima</h5>
                    <p class="text-muted small mb-3">Pokušajte sa promenom ili poništavanjem filtera.</p>
                    <a href="vehicles.php" class="btn btn-outline-dark btn-sm">Poništi filtere</a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php require_once "includes/footer.php"; ?>