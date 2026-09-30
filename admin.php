<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once "config/connection.php";

if (!isset($_SESSION['korisnik_id']) || !in_array($_SESSION['uloga_id'], [1, 2])) {
    header("Location: login.php");
    exit();
}
$cats = [];
$fuels = [];
$trans = [];

try {
    if (isset($conn)) {
        $cats = $conn->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
        $fuels = $conn->query("SELECT * FROM fuel_types")->fetchAll(PDO::FETCH_ASSOC);
        $trans = $conn->query("SELECT * FROM transmissions")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {
    
}
require_once "includes/header.php";
?>

<main class="container my-5">
    <h2 class="fw-bold mb-4">Admin Dashboard</h2>
    <div class="row">
        <!-- LEVI MENI -->
        <div class="col-md-3 mb-4">
            <div class="list-group shadow-sm" id="admin-menu">
                <button type="button" class="list-group-item list-group-item-action active" data-action="dashboard">
                    <i class="fa-solid fa-chart-line me-2"></i> Statistički pregled
                </button>
                <button type="button" class="list-group-item list-group-item-action" data-action="reservations">
                    <i class="fa-solid fa-calendar-check me-2"></i> Upravljanje rezervacijama
                </button>
                <button type="button" class="list-group-item list-group-item-action" data-action="vehicles">
                    <i class="fa-solid fa-car me-2"></i> Upravljanje vozilima
                </button>
                <button type="button" class="list-group-item list-group-item-action" data-action="services">
                    <i class="fa-solid fa-wrench me-2"></i> Evidencija servisa
                </button>
                <?php if ($_SESSION['uloga_id'] == 1): ?>
                    <button type="button" class="list-group-item list-group-item-action" data-action="users">
                        <i class="fa-solid fa-users me-2"></i> Lista korisnika
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" data-action="reviews">
                        <i class="fa-solid fa-star me-2"></i> Recenzije
                    </button>
                    <button type="button" class="list-group-item list-group-item-action" data-action="faqs">
                        <i class="fa-solid fa-circle-question me-2"></i> FAQ Pitanja
                    </button>
                    <button class="list-group-item list-group-item-action" data-action="messages">
                        <i class="fa-solid fa-envelope me-2"></i> Poruke
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- DESNI SADRŽAJ -->
        <div class="col-md-9">
            <div class="bg-white p-4 rounded-3 border shadow-sm" id="admin-content">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- MODAL ZA VOZILO -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="vehicleModalTitle">Dodaj novo vozilo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="vehicleForm" method="POST" enctype="multipart/form-data" onsubmit="return false;">
            <input type="hidden" name="id" id="vehicleId">
            <input type="hidden" name="existing_image" id="vehicleExistingImage">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Marka</label>
                    <input type="text" name="brand" id="vehicleBrand" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" id="vehicleModel" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kategorija</label>
                    <select name="category_id" id="vehicleCategory" class="form-select" required>
                        <option value="">-- Izaberite --</option>
                        <?php foreach ($cats as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? $c['naziv'] ?? $c['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Gorivo</label>
                    <select name="fuel_type_id" id="vehicleFuel" class="form-select" required>
                        <option value="">-- Izaberite --</option>
                        <?php foreach ($fuels as $f): ?>
                            <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['name'] ?? $f['naziv'] ?? $f['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Menjač</label>
                    <select name="transmission_id" id="vehicleTransmission" class="form-select" required>
                        <option value="">-- Izaberite --</option>
                        <?php foreach ($trans as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name'] ?? $t['naziv'] ?? $t['id']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Godište</label>
                    <input type="number" name="year" id="vehicleYear" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Broj sedišta</label>
                    <input type="number" name="seats" id="vehicleSeats" class="form-control" value="5" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Registracija</label>
                    <input type="text" name="registration_number" id="vehicleRegistration" class="form-control" placeholder="BG-123-AA" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Cena po danu (€)</label>
                <input type="number" step="0.01" name="price" id="vehiclePrice" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Slika vozila</label>
                <input type="file" name="image" class="form-control" accept="image/*">
                <small class="text-muted">Ostavite prazno ako ne želite da menjate postojeću sliku.</small>
            </div>

            <button type="submit" id="btnSaveVehicle" class="btn btn-primary w-100">Sačuvaj vozilo</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ZA FAQ -->
<div class="modal fade" id="faqModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="faqModalTitle">Dodaj FAQ Pitanje</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="faqForm" onsubmit="return false;">
            <input type="hidden" name="id" id="faqId">
            <input type="hidden" name="faq_category_id" id="faqCategoryId" value="1">
            <div class="mb-3">
                <label class="form-label">Pitanje</label>
                <input type="text" name="question" id="faqQuestion" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Odgovor</label>
                <textarea name="answer" id="faqAnswer" class="form-control" rows="4" required></textarea>
            </div>
            <button type="submit" id="btnSaveFaq" class="btn btn-primary w-100">Sačuvaj pitanje</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ZA POTVRDU BRISANJA -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Potvrda brisanja</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="deleteModalBody">
        Da li ste sigurni da želite da obrišete ovu stavku?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
        <button type="button" class="btn btn-danger" id="btnConfirmDelete">Obriši</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL ZA SERVIS -->
<div class="modal fade" id="serviceModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="serviceModalTitle">Evidentiraj novi servis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="serviceForm" onsubmit="return false;">
            <input type="hidden" name="id" id="serviceId">
            
            <div class="mb-3">
                <label class="form-label">Izaberite vozilo</label>
                <select name="vehicle_id" id="serviceVehicleId" class="form-select" required>
                    <option value="">-- Izaberite vozilo --</option>
                    <?php 
                    $all_vehicles = $conn->query("SELECT id, brand, model, registration_number FROM vehicles ORDER BY brand ASC")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($all_vehicles as $v_opt): 
                    ?>
                        <option value="<?= $v_opt['id'] ?>">
                            <?= htmlspecialchars($v_opt['brand'] . ' ' . $v_opt['model'] . ' (' . $v_opt['registration_number'] . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Datum servisa</label>
                <input type="date" name="service_date" id="serviceDate" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Opis radova</label>
                <textarea name="description" id="serviceDescription" class="form-control" rows="3" placeholder="npr. Mali servis, zamena kočionih pločica..." required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Trošak (€)</label>
                <input type="number" step="0.01" name="cost" id="serviceCost" class="form-control" placeholder="150" required>
            </div>

            <button type="submit" id="btnSaveService" class="btn btn-primary w-100">Sačuvaj servis</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once "includes/footer.php"; ?>