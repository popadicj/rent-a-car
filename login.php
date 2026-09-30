<?php
session_start();
require_once "config/connection.php";
require_once "includes/header.php";

$poruka = "";
$uspesna_poruka = "";

if (isset($_SESSION['success_msg'])) {
    $uspesna_poruka = $_SESSION['success_msg'];
    unset($_SESSION['success_msg']);
}

if (isset($_POST['btnLogin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $poruka = "Popunite sva polja!";
    } else {
        $sql = "SELECT u.*, r.name AS uloga_naziv 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.email = :email";
                
        $stmt = $conn->prepare($sql);
        $stmt->execute([':email' => $email]);
        $korisnik = $stmt->fetch();

        if ($korisnik && password_verify($password, $korisnik->password)) {
            if ($korisnik->is_verified == 0) {
                $_SESSION['verify_email'] = $korisnik->email;
                header("Location: verify.php");
                exit();
            }
            $_SESSION['korisnik_id'] = $korisnik->id;
            $_SESSION['ime_prezime'] = $korisnik->first_name . " " . $korisnik->last_name;
            $_SESSION['uloga_id']    = $korisnik->role_id;
            $_SESSION['uloga_naziv'] = $korisnik->uloga_naziv;

            if ($korisnik->role_id == 1 || $korisnik->role_id == 2) {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $poruka = "Pogrešna e-mail adresa ili lozinka!";
        }
    }
}
?>

<main class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="bg-white rounded-3 border p-4 p-md-5 shadow-sm">
                <div class="text-center mb-4">
                    <div class="bg-primary-icon bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-right-to-bracket fs-4"></i>
                    </div>
                    <h3 class="fw-bold mb-1">Prijava na sistem</h3>
                    <p class="text-muted small">Unesite vaše podatke za pristup nalogu</p>
                </div>
                <?php if (!empty($poruka)): ?>
                    <div class="alert alert-danger border-0 shadow-sm text-center small mb-4" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i> <?= $poruka ?>
                    </div>
                <?php endif; ?>
                <form action="login.php" method="POST" id="loginForm" novalidate>
                    <div class="mb-3">
                        <label for="loginEmail" class="form-label fw-semibold small text-muted">Email adresa</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control bg-light border-start-0" id="loginEmail" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="marko@gmail.com" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label for="loginPassword" class="form-label fw-semibold small text-muted">Lozinka</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="fa-solid fa-lock"></i>
                            </span>
                            <input type="password" class="form-control bg-light border-start-0" id="loginPassword" name="password" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" name="btnLogin" class="btn btn-primary btn-lg w-100 fw-bold fs-6 py-2">
                        Prijavi se <i class="fa-solid fa-arrow-right-to-bracket ms-2"></i>
                    </button>
                </form>
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="small text-muted mb-0">
                        Nemate nalog? <a href="register.php" class="text-primary fw-bold text-decoration-none">Registrujte se</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once "includes/footer.php"; ?>