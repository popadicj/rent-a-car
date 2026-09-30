<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once "config/connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$loggedIme = "";
$loggedEmail = "";

$currentUserId = $_SESSION['user_id'] ?? $_SESSION['korisnik_id'] ?? null;

if ($currentUserId) {
    $uStmt = $conn->prepare("SELECT first_name, last_name, email FROM users WHERE id = :id");
    $uStmt->execute([':id' => $currentUserId]);
    $loggedUser = $uStmt->fetch();

    if ($loggedUser) {
        $firstName = is_object($loggedUser) ? $loggedUser->first_name : $loggedUser['first_name'];
        $lastName  = is_object($loggedUser) ? $loggedUser->last_name : $loggedUser['last_name'];
        $emailUser = is_object($loggedUser) ? $loggedUser->email : $loggedUser['email'];

        $loggedIme = $firstName . ' ' . $lastName;
        $loggedEmail = $emailUser;
    }
} else {
    unset($_POST['ime'], $_POST['email']);
}

$successMsg = "";
$errorMsg = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['btn_send_message'])) {
    $ime    = trim($_POST['ime'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $naslov = trim($_POST['naslov'] ?? '');
    $poruka = trim($_POST['poruka'] ?? '');

    if (!empty($ime) && !empty($email) && !empty($naslov) && !empty($poruka)) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                $stmt = $conn->prepare("INSERT INTO messages (sender_name, sender_email, subject, message, created_at) VALUES (:ime, :email, :naslov, :poruka, NOW())");
                $stmt->execute([
                    ':ime'    => $ime,
                    ':email'   => $email,
                    ':naslov'  => $naslov,
                    ':poruka'  => $poruka
                ]);
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com'; 
                $mail->SMTPAuth   = true;
                $mail->Username   = 'jelena.popadic.94.23@ict.edu.rs';
                $mail->Password   = 'wkpt schk mlif wnzm';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('jelena.popadic.94.23@ict.edu.rs', 'Rent-a-Car Contact');
                $mail->addAddress('jelena.popadic.94.23@ict.edu.rs');
                $mail->addReplyTo($email, $ime);

                $mail->isHTML(true);
                $mail->Subject = "Nova poruka: " . $naslov;
                $mail->Body    = "
                    <h3>Pristigla je nova poruka sa kontakt forme</h3>
                    <p><b>Ime i prezime:</b> " . htmlspecialchars($ime) . "</p>
                    <p><b>Email:</b> " . htmlspecialchars($email) . "</p>
                    <p><b>Naslov:</b> " . htmlspecialchars($naslov) . "</p>
                    <p><b>Poruka:</b><br>" . nl2br(htmlspecialchars($poruka)) . "</p>
                ";

                $mail->send();
                $successMsg = "Vaša poruka je uspešno poslata! Odgovorićemo vam u najkraćem mogućem roku.";
                
                unset($_POST['naslov'], $_POST['poruka']);
                if (!$currentUserId) {
                    unset($_POST['ime'], $_POST['email']);
                }

            } catch (Exception $e) {
                $successMsg = "Vaša poruka je sačuvana u sistemu, ali slanje notifikacije na mail nije uspelo.";
            }
        } else {
            $errorMsg = "Molimo vas da unesete validnu e-mail adresu.";
        }
    } else {
        $errorMsg = "Molimo vas da popunite sva obavezna polja.";
    }
}
include_once "includes/header.php";
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="fw-bold display-5">Kontaktirajte Nas</h1>
        <p class="text-muted lead">Tu smo da odgovorimo na sva vaša pitanja i pružimo vam podršku 24/7.</p>
    </div>
    <?php if (!empty($successMsg)): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($successMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i><?= htmlspecialchars($errorMsg) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100 bg-primary text-white p-4">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <h3 class="fw-bold mb-4">Informacije</h3>
                        <p class="opacity-75 mb-4">Imate pitanje u vezi sa rezervacijom, vozilima ili uslovima iznajmljivanja? Pozovite nas ili posetite našu poslovnicu.</p>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-white text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-location-dot fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Adresa poslovnice</h6>
                                <p class="mb-0 opacity-75">Bulevar Oslobođenja 123, Beograd</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-white text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-phone fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Broj telefona</h6>
                                <p class="mb-0 opacity-75">+381 11 123 4567 / +381 64 123 4567</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start mb-4">
                            <div class="bg-white text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-envelope fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Email adresa</h6>
                                <p class="mb-0 opacity-75">info@rentacar-premium.rs</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-start">
                            <div class="bg-white text-primary rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="fa-solid fa-clock fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1">Radno vreme</h6>
                                <p class="mb-0 opacity-75">Ponedeljak - Nedelja: 08:00 - 20:00h</p>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4 border-top border-white border-opacity-25 mt-4">
                        <div class="d-flex gap-3">
                            <a href="#" class="text-white fs-4 opacity-75 opacity-100-hover"><i class="fa-brands fa-facebook"></i></a>
                            <a href="#" class="text-white fs-4 opacity-75 opacity-100-hover"><i class="fa-brands fa-instagram"></i></a>
                            <a href="#" class="text-white fs-4 opacity-75 opacity-100-hover"><i class="fa-brands fa-linkedin"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 p-4">
                <div class="card-body">
                    <h3 class="fw-bold mb-4 text-dark">Pošaljite nam poruku</h3>
                    
                    <form id="contactForm" action="contact.php" method="POST" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Vaše Ime i Prezime *</label>
                                <input type="text" 
                                    name="ime" 
                                    id="contactIme"
                                    class="form-control form-control-lg fs-6" 
                                    placeholder="Petar Petrović" 
                                    value="<?= htmlspecialchars($loggedIme ?: ($_POST['ime'] ?? '')) ?>">
                                <div class="invalid-feedback" id="errIme"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email adresa *</label>
                                <input type="email" 
                                    name="email" 
                                    id="contactEmail"
                                    class="form-control form-control-lg fs-6" 
                                    placeholder="petar@example.com" 
                                    value="<?= htmlspecialchars($loggedEmail ?: ($_POST['email'] ?? '')) ?>">
                                <div class="invalid-feedback" id="errEmail"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Naslov poruke *</label>
                                <input type="text" 
                                    name="naslov" 
                                    id="contactNaslov"
                                    class="form-control form-control-lg fs-6" 
                                    placeholder="npr. Upit za dugoročni najam" 
                                    value="<?= htmlspecialchars($_POST['naslov'] ?? '') ?>">
                                <div class="invalid-feedback" id="errNaslov"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Vaša poruka *</label>
                                <textarea name="poruka" 
                                        id="contactPoruka" 
                                        rows="5" 
                                        class="form-control fs-6" 
                                        placeholder="Napišite vaše pitanje ili zahtev (min. 10 karaktera)..."><?= htmlspecialchars($_POST['poruka'] ?? '') ?></textarea>
                                <div class="invalid-feedback" id="errPoruka"></div>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" name="btn_send_message" class="btn btn-primary btn-lg w-100 fw-semibold">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Pošalji poruku
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_once "includes/footer.php"; ?>