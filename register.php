<?php
session_start();
require_once "config/connection.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

$greske = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $first_name       = trim($_POST['first_name'] ?? '');
    $last_name        = trim($_POST['last_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    $name_regex = '/^[A-ZŠĐČĆŽ][a-zšđčćžA-ZŠĐČĆŽ\s\-]{1,29}$/u';
    $phone_regex = '/^(\+381|0)[6][0-9][\s\-]?\d{3,4}[\s\-]?\d{3,4}$/';
    $pass_regex  = '/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*#?&]{8,}$/';

    if (empty($first_name)) {
        $greske[] = "Ime je obavezno!";
    } elseif (!preg_match($name_regex, $first_name)) {
        $greske[] = "Ime mora početi velikim slovom (npr. Marko).";
    }

    if (empty($last_name)) {
        $greske[] = "Prezime je obavezno!";
    } elseif (!preg_match($name_regex, $last_name)) {
        $greske[] = "Prezime mora početi velikim slovom (npr. Marković).";
    }

    if (empty($email)) {
        $greske[] = "Email je obavezan!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $greske[] = "Unesite validnu email adresu.";
    }

    if (!empty($phone) && !preg_match($phone_regex, $phone)) {
        $greske[] = "Ispravan format telefona: 0641234567 ili +381641234567.";
    }

    if (empty($password)) {
        $greske[] = "Lozinka je obavezna!";
    } elseif (!preg_match($pass_regex, $password)) {
        $greske[] = "Lozinka mora imati min. 8 karaktera, bar jedno slovo i bar jedan broj.";
    }

    if ($password !== $password_confirm) {
        $greske[] = "Lozinke se ne poklapaju!";
    }

    if (empty($greske)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        
        if ($stmt->rowCount() > 0) {
            $greske[] = "Korisnik sa ovim email-om već postoji!";
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $role_id = 3; 

            $otp_code = random_int(100000, 999999);
            $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            $ins = $conn->prepare("
                INSERT INTO users (first_name, last_name, email, phone, password, role_id, is_verified, verification_code, code_expires_at) 
                VALUES (:first_name, :last_name, :email, :phone, :password, :role_id, 0, :code, :expires)
            ");
            
            $inserted = $ins->execute([
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'email'      => $email,
                'phone'      => $phone,
                'password'   => $hash,
                'role_id'    => $role_id,
                'code'       => $otp_code,
                'expires'    => $expires_at
            ]);

            if ($inserted) {
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'jelena.popadic.94.23@ict.edu.rs';  
                    $mail->Password   = 'wkpt schk mlif wnzm';  
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('jelena.popadic.94.23@ict.edu.rs', 'Rent-a-Car');
                    $mail->addAddress($email, $first_name . ' ' . $last_name);

                    $mail->isHTML(true);
                    $mail->CharSet = 'UTF-8';
                    $mail->Subject = 'Verifikacioni kod za aktivaciju naloga';
                    $mail->Body    = "
                        <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 10px;'>
                            <h2 style='color: #0d6efd; text-align: center;'>Rent-a-Car</h2>
                            <p>Zdravo <strong>{$first_name}</strong>,</p>
                            <p>Hvala vam na registraciji. Vaš jednokratni verifikacioni kod je:</p>
                            <div style='text-align: center; margin: 25px 0;'>
                                <span style='background: #f1f5f9; color: #0d6efd; font-size: 28px; font-weight: bold; letter-spacing: 6px; padding: 10px 20px; border-radius: 8px; border: 1px dashed #0d6efd;'>{$otp_code}</span>
                            </div>
                            <p style='font-size: 13px; color: #64748b; text-align: center;'>Kod važi narednih 15 minuta.</p>
                        </div>
                    ";

                    $mail->send();

                    $_SESSION['verify_email'] = $email;
                    header("Location: verify.php");
                    exit();

                } catch (Exception $e) {
                    $greske[] = "Greška pri slanju verifikacionog mejla. Poruka: " . $mail->ErrorInfo;
                }
            }
        }
    }
}
include_once "includes/header.php";
?>

<main class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="bg-white rounded-3 border p-4 p-md-5 shadow-sm">
                
                <div class="text-center mb-4">
                    <div class="bg-primary-icon bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-user-plus fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Kreirajte nalog</h3>
                    <p class="text-muted small">Registrujte se za brz i lak najam vozila</p>
                </div>

                <?php if (!empty($greske)): ?>
                    <div class="alert alert-danger border-0 shadow-sm mb-4 small">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($greske as $g): ?>
                                <li><?= htmlspecialchars($g) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST" id="registerForm" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="regIme" class="form-label fw-semibold small text-muted">Ime</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="regIme" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" placeholder="Marko" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="regPrezime" class="form-label fw-semibold small text-muted">Prezime</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="regPrezime" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" placeholder="Marković" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="regEmail" class="form-label fw-semibold small text-muted">Email adresa</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" class="form-control bg-light border-start-0" id="regEmail" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="marko@gmail.com" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="regTelefon" class="form-label fw-semibold small text-muted">Broj telefona</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
                                <input type="text" class="form-control bg-light border-start-0" id="regTelefon" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="0641234567">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="regPassword" class="form-label fw-semibold small text-muted">Lozinka</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" class="form-control bg-light border-start-0" id="regPassword" name="password" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="regPasswordConfirm" class="form-label fw-semibold small text-muted">Potvrdi lozinku</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-shield-halved"></i></span>
                                <input type="password" class="form-control bg-light border-start-0" id="regPasswordConfirm" name="password_confirm" placeholder="••••••••" required>
                            </div>
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" name="register" class="btn btn-primary btn-lg w-100 fw-bold fs-6 py-2">
                                Nastavi na verifikaciju <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </div>
                </form>
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="small text-muted mb-0">
                        Već imate nalog? <a href="login.php" class="text-primary fw-bold text-decoration-none">Prijavite se</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include_once "includes/footer.php"; ?>