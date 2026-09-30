<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "config/connection.php";

if (!isset($_SESSION['user_id']) && isset($_SESSION['korisnik_id'])) {
    $_SESSION['user_id'] = $_SESSION['korisnik_id']; 
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$successMsg = "";
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'cancel_reservation') {
    $reservationId = intval($_POST['reservation_id'] ?? 0);

    if ($reservationId > 0) {
        $cancelStmt = $conn->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = :res_id AND user_id = :user_id AND status = 'Pending'");
        $cancelStmt->execute([
            ':res_id' => $reservationId,
            ':user_id' => $userId
        ]);

        if ($cancelStmt->rowCount() > 0) {
            $successMsg = "Rezervacija je uspešno otkazana.";
        } else {
            $errorMsg = "Nije moguće otkazati izabranu rezervaciju.";
        }
    }
}

$userStmt = $conn->prepare("SELECT first_name, last_name, email, phone FROM users WHERE id = :id");
$userStmt->execute([':id' => $userId]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$resQuery = "SELECT r.id AS reservation_id, r.start_date, r.end_date, r.total_price, r.status,
                    v.brand, v.model, v.image
             FROM reservations r
             JOIN vehicles v ON r.vehicle_id = v.id
             WHERE r.user_id = :user_id
             ORDER BY r.created_at DESC";

$resStmt = $conn->prepare($resQuery);
$resStmt->execute([':user_id' => $userId]);
$reservations = $resStmt->fetchAll(PDO::FETCH_ASSOC);

include_once "includes/header.php";
?>

<div class="container py-5">
    
    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4 text-center">
                    <div class="mb-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 32px;">
                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?></h4>
                    <p class="text-muted small mb-3">Klijent</p>
                    <hr class="my-3">
                    <div class="text-start">
                        <div class="mb-2">
                            <strong class="d-block text-muted small">Email adresa:</strong>
                            <span><?= htmlspecialchars($user['email']) ?></span>
                        </div>
                        <div>
                            <strong class="d-block text-muted small">Broj telefona:</strong>
                            <span><?= htmlspecialchars($user['phone'] ?? '-') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Moje rezervacije</h4>

                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-4 text-muted">
                            <p class="mb-2">Trenutno nemate nijednu rezervaciju.</p>
                            <a href="vozila.php" class="btn btn-outline-primary btn-sm">Pregledajte ponudu vozila</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($reservations as $res): 
                                $status = strtolower($res['status']);
                            ?>
                                <div class="card border rounded-3 overflow-hidden shadow-sm">
                                    <div class="row g-0 align-items-center">
                                        <div class="col-md-4 col-12 bg-light">
                                            <img src="assets/img/<?= htmlspecialchars($res['image']) ?>" alt="Slika vozila" class="w-100 d-block" style="height: 140px; object-fit: cover;">
                                        </div>
                                        <div class="col-md-8 col-12 p-3">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <h5 class="fw-bold text-dark mb-1 fs-5"><?= htmlspecialchars($res['brand'] . " " . $res['model']) ?></h5>
                                                    <small class="text-muted">
                                                        <i class="fa-regular fa-calendar me-1"></i>
                                                        <?= date("d.m.Y.", strtotime($res['start_date'])) ?> — <?= date("d.m.Y.", strtotime($res['end_date'])) ?>
                                                    </small>
                                                </div>
                                                <div>
                                                    <?php if ($status === 'pending'): ?>
                                                        <span class="badge bg-warning text-dark px-3 py-2">Na čekanju</span>
                                                    <?php elseif ($status === 'confirmed'): ?>
                                                        <span class="badge bg-success px-3 py-2">Potvrđeno</span>
                                                    <?php elseif ($status === 'cancelled'): ?>
                                                        <span class="badge bg-danger px-3 py-2">Otkazano</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary px-3 py-2"><?= htmlspecialchars($status) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <div>
                                                    <span class="text-muted small d-block">Ukupna cena:</span>
                                                    <strong class="fs-5 text-primary"><?= number_format($res['total_price'], 2) ?> €</strong>
                                                </div>

                                               <?php if ($status === 'pending'): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger btn-sm px-3 fw-semibold" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#cancelModal<?= $res['reservation_id'] ?>">
                                                        <i class="fa-solid fa-xmark me-1"></i> Otkaži
                                                    </button>      
                                                    <div class="modal fade" id="cancelModal<?= $res['reservation_id'] ?>" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content border-0 shadow">
                                                                <div class="modal-header bg-danger text-white">
                                                                    <h5 class="modal-title fw-bold">
                                                                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Potvrda otkazivanja
                                                                    </h5>
                                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Zatvori"></button>
                                                                </div>
                                                                <div class="modal-body py-4 text-center">
                                                                    <p class="fs-5 mb-1">Da li ste sigurni da želite da otkažete rezervaciju za:</p>
                                                                    <strong class="text-primary fs-5"><?= htmlspecialchars($res['brand'] . " " . $res['model']) ?></strong>?
                                                                    <div class="text-muted small mt-2">
                                                                        Termin: <?= date("d.m.Y.", strtotime($res['start_date'])) ?> — <?= date("d.m.Y.", strtotime($res['end_date'])) ?>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer bg-light">
                                                                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Ne, odustani</button>
                                                                
                                                                    <form action="profile.php" method="POST" class="d-inline">
                                                                        <input type="hidden" name="action" value="cancel_reservation">
                                                                        <input type="hidden" name="reservation_id" value="<?= $res['reservation_id'] ?>">
                                                                        <button type="submit" class="btn btn-danger px-4">Da, otkaži</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<?php include_once "includes/footer.php"; ?>