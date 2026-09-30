<?php
session_start();
require_once "../config/connection.php";

if (!isset($_SESSION['korisnik_id']) || !in_array($_SESSION['uloga_id'], [1, 2])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['json'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Pristup odbijen.']);
        exit();
    }
    echo "<div class='alert alert-danger'>Pristup odbijen. Nemate odgovarajuće privilegije.</div>";
    exit();
}

$action = $_POST['action'] ?? $_GET['action'] ?? 'dashboard';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if ($action === 'update_reservation_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $stmt = $conn->prepare("UPDATE reservations SET status = :status WHERE id = :id");
        echo json_encode(['success' => $stmt->execute([':status' => $status, ':id' => $id])]);
        exit();
    }

    if ($action === 'delete_user' && $_SESSION['uloga_id'] == 1) {
        $id = (int)($_POST['id'] ?? 0);
        if ($id == $_SESSION['korisnik_id']) {
            echo json_encode(['success' => false, 'message' => 'Ne možete obrisati sebe.']);
            exit();
        }
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        echo json_encode(['success' => $stmt->execute([':id' => $id])]);
        exit();
    }

    if ($action === 'delete_vehicle') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM vehicles WHERE id = :id");
        echo json_encode(['success' => $stmt->execute([':id' => $id])]);
        exit();
    }

    if ($action === 'save_vehicle') {
        $id = $_POST['id'] ?? null;
        $category_id = $_POST['category_id'] ?? null;
        $fuel_type_id = $_POST['fuel_type_id'] ?? null;
        $transmission_id = $_POST['transmission_id'] ?? null;
        $registration_number = $_POST['registration_number'] ?? '';
        $seats = $_POST['seats'] ?? 5;
        $brand = $_POST['brand'] ?? '';
        $model = $_POST['model'] ?? '';
        $year = $_POST['year'] ?? '';
        $price = $_POST['price'] ?? '';

        $existingImage = trim($_POST['existing_image'] ?? '');
        $imageName = !empty($existingImage) ? $existingImage : 'default.jpg';

        if (empty($brand) || empty($model) || $price <= 0 || $year <= 0) {
            echo json_encode(['success' => false, 'message' => 'Molimo popunite sva polja ispravno.']);
            exit();
        }

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['image']['tmp_name'];
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $targetDir = '../assets/img/';

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            if (move_uploaded_file($tmp, $targetDir . $fileName)) {
                $imageName = $fileName;
            }
        }

        try {
            if (!empty($id)) {
                $stmt = $conn->prepare("UPDATE vehicles SET category_id = :cat, fuel_type_id = :fuel, transmission_id = :trans, registration_number = :reg, seats = :seats, brand = :b, model = :m, year = :y, price_per_day = :p, image = :img WHERE id = :id");
                $res = $stmt->execute([
                    ':cat' => $category_id, ':fuel' => $fuel_type_id, ':trans' => $transmission_id,
                    ':reg' => $registration_number, ':seats' => $seats,
                    ':b' => $brand, ':m' => $model, ':y' => $year, ':p' => $price, ':img' => $imageName, ':id' => $id
                ]);
            } else {
                $stmt = $conn->prepare("INSERT INTO vehicles (category_id, fuel_type_id, transmission_id, registration_number, seats, brand, model, year, price_per_day, image) VALUES (:cat, :fuel, :trans, :reg, :seats, :b, :m, :y, :p, :img)");
                $res = $stmt->execute([
                    ':cat' => $category_id, ':fuel' => $fuel_type_id, ':trans' => $transmission_id,
                    ':reg' => $registration_number, ':seats' => $seats,
                    ':b' => $brand, ':m' => $model, ':y' => $year, ':p' => $price, ':img' => $imageName
                ]);
            }
            echo json_encode(['success' => (bool)$res]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Greška u bazi: ' . $e->getMessage()]);
        }
        exit();
    }

    if ($action === 'delete_faq' && $_SESSION['uloga_id'] == 1) {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM faqs WHERE id = :id");
        echo json_encode(['success' => $stmt->execute([':id' => $id])]);
        exit();
    }

    if ($action === 'save_faq' && $_SESSION['uloga_id'] == 1) {
        $id = $_POST['id'] ?? '';
        $q = trim($_POST['question'] ?? '');
        $a = trim($_POST['answer'] ?? '');
        $cat_id = (int)($_POST['faq_category_id'] ?? 1);
        if ($cat_id <= 0) {
            $cat_id = 1;
        }

        if (empty($q) || empty($a)) {
            echo json_encode(['success' => false, 'message' => 'Pitanje i odgovor ne smeju biti prazni.']);
            exit();
        }

        try {
           if (!empty($id)) {
                $stmt = $conn->prepare("UPDATE faqs SET question = :q, answer = :a, faq_category_id = :cat WHERE id = :id");
                $res = $stmt->execute([':q' => $q, ':a' => $a, ':cat' => $cat_id, ':id' => $id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO faqs (faq_category_id, question, answer) VALUES (:cat, :q, :a)");
                $res = $stmt->execute([':cat' => $cat_id, ':q' => $q, ':a' => $a]);
            }
            echo json_encode(['success' => (bool)$res]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Greška u bazi: ' . $e->getMessage()]);
        }
        exit();
    }
    if ($action === 'delete_review') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = :id");
        echo json_encode(['success' => $stmt->execute([':id' => $id])]);
        exit();
    }
    if ($action === 'delete_service') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM service_records WHERE id = :id");
        echo json_encode(['success' => $stmt->execute([':id' => $id])]);
        exit();
    }
    if ($action === 'save_service') {
        $id = $_POST['id'] ?? '';
        $vehicle_id = (int)($_POST['vehicle_id'] ?? 0);
        $service_date = $_POST['service_date'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $cost = (float)($_POST['cost'] ?? 0);

        if ($vehicle_id <= 0 || empty($service_date) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Popunite sva obavezna polja.']);
            exit();
        }

        try {
            if (!empty($id)) {
                $stmt = $conn->prepare("UPDATE service_records SET vehicle_id = :v_id, service_date = :s_date, description = :desc, cost = :cost WHERE id = :id");
                $res = $stmt->execute([':v_id' => $vehicle_id, ':s_date' => $service_date, ':desc' => $description, ':cost' => $cost, ':id' => $id]);
            } else {
                $stmt = $conn->prepare("INSERT INTO service_records (vehicle_id, service_date, description, cost) VALUES (:v_id, :s_date, :desc, :cost)");
                $res = $stmt->execute([':v_id' => $vehicle_id, ':s_date' => $service_date, ':desc' => $description, ':cost' => $cost]);
            }

            if ($res && $service_date === date('Y-m-d')) {
                $stmtStatus = $conn->prepare("UPDATE vehicles SET status = 'In Service' WHERE id = :v_id");
                $stmtStatus->execute([':v_id' => $vehicle_id]);
            }

            echo json_encode(['success' => (bool)$res]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Greška u bazi: ' . $e->getMessage()]);
        }
        exit();
    }
}

if (isset($_GET['get_single'])) {
    header('Content-Type: application/json');
    $id = (int)($_GET['id'] ?? 0);

    if ($action === 'get_vehicle') {
        $stmt = $conn->prepare("SELECT * FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        exit();
    }
    if ($action === 'get_faq' && $_SESSION['uloga_id'] == 1) {
        $stmt = $conn->prepare("SELECT * FROM faqs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        exit();
    }
    if ($action === 'get_service') {
        $stmt = $conn->prepare("SELECT * FROM service_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        echo json_encode(['success' => true, 'data' => $stmt->fetch(PDO::FETCH_ASSOC)]);
        exit();
    }
}

try {
    if ($action === 'dashboard') {
        $total_vehicles = $conn->query("SELECT COUNT(*) FROM vehicles")->fetchColumn();
        $total_reservations = $conn->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
        $total_users = $conn->query("SELECT COUNT(*) FROM users WHERE role_id = 3")->fetchColumn();
        $total_service_cost = $conn->query("SELECT SUM(cost) FROM service_records")->fetchColumn() ?: 0;
        ?>
        <h4 class="fw-bold mb-3">Dobrodošli, <?= htmlspecialchars($_SESSION['ime_prezime'] ?? 'Admin') ?>!</h4>
        <p class="text-muted mb-4">Pregled trenutnog stanja u sistemu.</p>
        
        <div class="row g-3">
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted small">Ukupno vozila</div>
                    <div class="fs-3 fw-bold text-primary"><?= $total_vehicles ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted small">Ukupno rezervacija</div>
                    <div class="fs-3 fw-bold text-success"><?= $total_reservations ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted small">Registrovani klijenti</div>
                    <div class="fs-3 fw-bold text-warning"><?= $total_users ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 bg-light rounded border text-center">
                    <div class="text-muted small">Troškovi servisa</div>
                    <div class="fs-3 fw-bold text-danger"><?= number_format($total_service_cost, 2, ',', '.') ?> €</div>
                </div>
            </div>
        </div>
        <?php
    }

    elseif ($action === 'vehicles') {
        $vehicles = $conn->query("SELECT v.*, c.name as category_name FROM vehicles v LEFT JOIN categories c ON v.category_id = c.id ORDER BY v.id DESC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Upravljanje vozilima</h4>
            <button class="btn btn-primary btn-sm btn-open-add-vehicle">
                <i class="fa-solid fa-plus me-1"></i> Dodaj vozilo
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Slika</th>
                        <th>Vozilo</th>
                        <th>Kategorija</th>
                        <th>Registracija</th>
                        <th>Cena / dan</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($vehicles)): ?>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td><img src="assets/img/<?= htmlspecialchars($v['image'] ?? 'default.jpg') ?>" width="50" height="35" class="rounded object-fit-cover"></td>
                                <td><strong><?= htmlspecialchars($v['brand'] . ' ' . $v['model']) ?></strong> (<?= htmlspecialchars($v['year']) ?>)</td>
                                <td><?= htmlspecialchars($v['category_name'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($v['registration_number']) ?></td>
                                <td><?= number_format($v['price_per_day'], 2, ',', '.') ?> €</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-warning btn-edit-vehicle" data-id="<?= $v['id'] ?>"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-vehicle" data-id="<?= $v['id'] ?>"><i class="fa-solid fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Nema registrovanih vozila.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    elseif ($action === 'reservations') {
        $reservations = $conn->query("
            SELECT r.*, u.first_name, u.last_name, v.brand, v.model 
            FROM reservations r
            JOIN users u ON r.user_id = u.id
            JOIN vehicles v ON r.vehicle_id = v.id
            ORDER BY r.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <h4 class="fw-bold mb-3">Upravljanje rezervacijama</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Korisnik</th>
                        <th>Vozilo</th>
                        <th>Datum od - do</th>
                        <th>Status</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $r): 
                        $st = $r['status'] ?? 'Pending';
                        $badgeClass = ($st === 'Confirmed') ? 'success' : (($st === 'Completed') ? 'info' : (($st === 'Cancelled') ? 'danger' : 'warning'));
                        $statusText = ($st === 'Confirmed') ? 'Potvrđeno' : (($st === 'Completed') ? 'Završeno' : (($st === 'Cancelled') ? 'Otkazano' : 'Na čekanju'));
                    ?>
                        <tr>
                            <td>#<?= $r['id'] ?></td>
                            <td><?= htmlspecialchars(($r['first_name'] ?? '') . " " . ($r['last_name'] ?? '')) ?></td>
                            <td><?= htmlspecialchars(($r['brand'] ?? '') . " " . ($r['model'] ?? '')) ?></td>
                            <td class="small"><?= isset($r['start_date']) ? date("d.m.Y.", strtotime($r['start_date'])) : '' ?> - <?= isset($r['end_date']) ? date("d.m.Y.", strtotime($r['end_date'])) : '' ?></td>
                            <td><span class="badge bg-<?= $badgeClass ?>"><?= $statusText ?></span></td>
                            <td>
                                <select class="form-select form-select-sm update-status" data-id="<?= $r['id'] ?>">
                                    <option value="Pending" <?= $st === 'Pending' ? 'selected' : '' ?>>Na čekanju</option>
                                    <option value="Confirmed" <?= $st === 'Confirmed' ? 'selected' : '' ?>>Potvrdi / Odobri</option>
                                    <option value="Completed" <?= $st === 'Completed' ? 'selected' : '' ?>>Završeno</option>
                                    <option value="Cancelled" <?= $st === 'Cancelled' ? 'selected' : '' ?>>Otkaži</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    elseif ($action === 'services') {
        $filter_vehicle = (int)($_GET['vehicle_id'] ?? $_POST['vehicle_id'] ?? 0);
        
        $query = "SELECT s.*, v.brand, v.model, v.registration_number FROM service_records s JOIN vehicles v ON s.vehicle_id = v.id";
        if ($filter_vehicle > 0) {
            $query .= " WHERE s.vehicle_id = :v_id";
        }
        $query .= " ORDER BY s.service_date DESC";

        $stmt = $conn->prepare($query);
        if ($filter_vehicle > 0) {
            $stmt->bindValue(':v_id', $filter_vehicle, PDO::PARAM_INT);
        }
        $stmt->execute();
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $all_v = $conn->query("SELECT id, brand, model, registration_number FROM vehicles ORDER BY brand ASC")->fetchAll(PDO::FETCH_ASSOC);
        
        $total_sum = array_reduce($services, function($sum, $item) {
            return $sum + ($item['cost'] ?? 0);
        }, 0);
        ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Evidencija servisa</h4>
            <div>
                <button class="btn btn-outline-secondary btn-sm me-2" onclick="window.print();">
                    <i class="fa-solid fa-print me-1"></i> Štampaj izveštaj
                </button>
                <!-- DODATO DUGME ZA PDF -->
                <button class="btn btn-outline-danger btn-sm me-2 btn-export-pdf">
                    <i class="fa-solid fa-file-pdf me-1"></i> Sačuvaj kao PDF
                </button>
                <button class="btn btn-primary btn-sm btn-open-add-service">
                    <i class="fa-solid fa-plus me-1"></i> Dodaj servis
                </button>
            </div>
        </div>

        <!-- OMOTAČ SA ID-EM KOJI SE PREVODI U PDF -->
        <div id="pdf-export-content" class="p-2 bg-white">
            <div class="row mb-3">
                <div class="col-md-5 no-print-pdf">
                    <select id="filterServiceVehicle" class="form-select form-select-sm">
                        <option value="0">-- Sva vozila (Prikaži sve) --</option>
                        <?php foreach ($all_v as $v_item): ?>
                            <option value="<?= $v_item['id'] ?>" <?= $filter_vehicle == $v_item['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($v_item['brand'] . ' ' . $v_item['model'] . ' (' . $v_item['registration_number'] . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-7 text-end align-self-center">
                    <strong>Ukupno za izabrani pregled: <span class="text-danger"><?= number_format($total_sum, 2, ',', '.') ?> €</span></strong>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Vozilo</th>
                            <th>Datum servisa</th>
                            <th>Opis radova</th>
                            <th>Trošak</th>
                            <th class="no-print-pdf">Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($services)): ?>
                            <?php foreach ($services as $s): ?>
                                <tr>
                                    <td>#<?= $s['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($s['brand'] . " " . $s['model']) ?></strong> <small class="text-muted">(<?= htmlspecialchars($s['registration_number']) ?>)</small></td>
                                    <td><?= date("d.m.Y.", strtotime($s['service_date'])) ?></td>
                                    <td><?= htmlspecialchars($s['description']) ?></td>
                                    <td class="fw-bold text-danger"><?= number_format($s['cost'], 2, ',', '.') ?> €</td>
                                    <td class="no-print-pdf">
                                        <button class="btn btn-sm btn-outline-warning me-1 btn-edit-service" data-id="<?= $s['id'] ?>">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger btn-delete-service" data-id="<?= $s['id'] ?>">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Nema zabeleženih servisa za izabrani kriterijum.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    elseif ($action === 'users' && $_SESSION['uloga_id'] == 1) {
        $users = $conn->query("
            SELECT u.*, r.name as role_name 
            FROM users u 
            JOIN roles r ON u.role_id = r.id 
            ORDER BY u.id DESC
        ")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <h4 class="fw-bold mb-3">Upravljanje korisnicima</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Ime i prezime</th>
                        <th>Email</th>
                        <th>Uloga</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><?= htmlspecialchars(($u['first_name'] ?? '') . " " . ($u['last_name'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($u['role_name'] ?? '') ?></span></td>
                            <td>
                                <?php if ($u['id'] != $_SESSION['korisnik_id']): ?>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-user" data-id="<?= $u['id'] ?>">
                                        <i class="fa-solid fa-user-xmark"></i> Obriši
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small">Trenutni nalog</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    elseif ($action === 'reviews') {
        $reviews = $conn->query("
            SELECT r.*, u.first_name, u.last_name, v.brand, v.model 
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN vehicles v ON r.vehicle_id = v.id
            ORDER BY r.id DESC
        ")->fetchAll();
        ?>
        <h4 class="fw-bold mb-3">Upravljanje recenzijama</h4>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Korisnik</th>
                        <th>Vozilo</th>
                        <th>Ocena</th>
                        <th>Komentar</th>
                        <th>Datum</th>
                        <th>Akcija</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $rev): ?>
                            <tr>
                                <td>#<?= $rev->id ?></td>
                                <td><?= htmlspecialchars(($rev->first_name ?? '') . ' ' . ($rev->last_name ?? '')) ?></td>
                                <td><strong><?= htmlspecialchars($rev->brand . ' ' . $rev->model) ?></strong></td>
                                <td>
                                    <span class="text-warning fw-bold">
                                        <i class="fa-solid fa-star"></i> <?= $rev->rating ?>/5
                                    </span>
                                </td>
                                <td class="small" style="max-width: 250px;"><?= htmlspecialchars($rev->comment ?? '') ?></td>
                                <td class="small text-muted"><?= date('d.m.Y. H:i', strtotime($rev->created_at)) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger btn-delete-review" data-id="<?= $rev->id ?>">
                                        <i class="fa-solid fa-trash"></i> Obriši
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Nema zabeleženih recenzija.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    elseif ($action === 'faqs' && $_SESSION['uloga_id'] == 1) {
        $faqs = $conn->query("SELECT * FROM faqs ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
        ?>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">FAQ Pitanja</h4>
            <button class="btn btn-primary btn-sm btn-open-add-faq">
                <i class="fa-solid fa-plus me-1"></i> Dodaj pitanje
            </button>
        </div>

        <div class="accordion" id="faqAccordion">
            <?php foreach ($faqs as $f): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $f['id'] ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $f['id'] ?>">
                            <?= htmlspecialchars($f['question'] ?? '') ?>
                        </button>
                    </h2>
                    <div id="collapse<?= $f['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body d-flex justify-content-between align-items-center">
                            <div><?= htmlspecialchars($f['answer'] ?? '') ?></div>
                            <div>
                                <button class="btn btn-sm btn-outline-warning me-1 btn-edit-faq" data-id="<?= $f['id'] ?>">
                                    <i class="fa-solid fa-pen"></i> Izmeni
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-faq" data-id="<?= $f['id'] ?>">
                                    <i class="fa-solid fa-trash"></i> Obriši
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
    elseif ($action === 'messages') {
        // Uzima defaultni fetch mode (PDO::FETCH_OBJ) iz tvoje konekcije
        $messages = $conn->query("SELECT * FROM messages ORDER BY id DESC")->fetchAll();
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold m-0">Prispale poruke sa kontakt forme</h4>
            <span class="badge bg-primary fs-6"><?= count($messages) ?> poruk<?= count($messages) == 1 ? 'a' : 'e' ?></span>
        </div>

        <?php if (empty($messages)): ?>
            <div class="alert alert-light text-center py-5 border rounded-3 text-muted">
                <i class="fa-solid fa-inbox fs-1 mb-2 d-block"></i>
                Nema pristiglih poruka sa kontakt forme.
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($messages as $m): ?>
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-light border-bottom-0 d-flex justify-content-between align-items-center py-3 px-4">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-dark">#<?= $m->id ?></span>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($m->sender_name) ?></h6>
                                    <a href="mailto:<?= htmlspecialchars($m->sender_email) ?>" class="small text-decoration-none text-muted">
                                        <i class="fa-regular fa-envelope me-1"></i><?= htmlspecialchars($m->sender_email) ?>
                                    </a>
                                </div>
                            </div>
                            <span class="small text-muted">
                                <i class="fa-regular fa-clock me-1"></i><?= date('d.m.Y. H:i', strtotime($m->created_at)) ?>
                            </span>
                        </div>
                        <div class="card-body px-4 py-3">
                            <h6 class="fw-bold text-primary mb-2">
                                Naslov: <?= htmlspecialchars($m->subject) ?>
                            </h6>
                            <p class="card-text text-secondary mb-0" style="white-space: pre-line;">
                                <?= htmlspecialchars($m->message) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php
    }
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Greška u bazi: " . htmlspecialchars($e->getMessage()) . "</div>";
}