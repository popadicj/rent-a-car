<?php
http_response_code(404);
require_once "includes/header.php";
?>

<main class="container my-5 text-center py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="display-1 fw-bold text-primary mb-0">404</h1>
            <i class="fa-solid fa-car-burst fs-1 text-muted my-3"></i>
            <h3 class="fw-bold text-dark">Stranica nije pronađena</h3>
            <p class="text-muted mb-4">
                Stranica koju tražite ne postoji ili je premeštena na drugu adresu.
            </p>
            <a href="index.php" class="btn btn-primary px-4">
                <i class="fa-solid fa-house me-2"></i>Nazad na Početnu
            </a>
        </div>
    </div>
</main>

<?php require_once "includes/footer.php"; ?>