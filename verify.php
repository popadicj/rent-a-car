<?php
session_start();
require_once "config/connection.php";

if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verify_email'];
$greske = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['verify'])) {
    $code = trim($_POST['otp_code']);

    if (empty($code)) {
        $greske[] = "Unesite verifikacioni kod!";
    } else {
        $stmt = $conn->prepare("SELECT id, code_expires_at FROM users WHERE email = :email AND verification_code = :code");
        $stmt->execute(['email' => $email, 'code' => $code]);
        $user = $stmt->fetch();

        if ($user) {
            if (strtotime($user->code_expires_at) < time()) {
                $greske[] = "Verifikacioni kod je istekao. Registrujte se ponovo.";
            } else {
                $upd = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL, code_expires_at = NULL WHERE id = :id");
                $upd->execute(['id' => $user->id]);

                unset($_SESSION['verify_email']);
                $_SESSION['success_msg'] = "Nalog je uspešno verifikovan! Možete se prijaviti.";
                header("Location: login.php");
                exit();
            }
        } else {
            $greske[] = "Netačan verifikacioni kod!";
        }
    }
}

include_once "includes/header.php";
?>

<main class="container my-5 py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bg-white rounded-3 border p-4 p-md-5 shadow-sm text-center">
                <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-envelope-circle-check fs-3"></i>
                </div>
                
                <h3 class="fw-bold mb-2">Potvrdite vašu adresu</h3>
                <p class="text-muted small mb-4">
                    Poslali smo 6-cifreni kod na adresu: <br><strong><?= htmlspecialchars($email) ?></strong>
                </p>

                <?php if (!empty($greske)): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4 small text-start">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($greske as $g): ?>
                                <li><?= htmlspecialchars($g) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="verify.php" method="POST">
                    <div class="mb-4">
                        <input type="text" name="otp_code" class="form-control form-control-lg text-center fw-bold fs-4" placeholder="123456" maxlength="6" required style="letter-spacing: 6px;">
                    </div>

                    <button type="submit" name="verify" class="btn btn-primary btn-lg w-100 fw-bold fs-6 mb-3">
                        Potvrdi nalog
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include_once "includes/footer.php"; ?>