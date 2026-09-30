<?php
session_start();
require_once 'config/connection.php';

$stmt = $conn->query("
    SELECT c.name AS category_name, f.question, f.answer 
    FROM faq_categories c
    JOIN faqs f ON c.id = f.faq_category_id
    WHERE f.is_active = 1
    ORDER BY c.sort_order ASC, f.sort_order ASC
");
$raw_faqs = $stmt->fetchAll();

$grouped_faqs = [];
foreach ($raw_faqs as $row) {
    $grouped_faqs[$row->category_name][] = $row;
}

$category_icons = [
    'Opšti uslovi i dokumentacija' => 'fa-id-card',
    'Osiguranje i kilometraža'     => 'fa-shield-halved',
    'Plaćanje i depozit'           => 'fa-credit-card',
    'Procedura u slučaju nezgode'   => 'fa-triangle-exclamation'
];
require_once 'includes/header.php';
?>
    <div class="conditions-page">
    <div class="container">
        <div class="page-header text-center">
            <h1 class="fw-bold page-title">Uslovi najma i informacije</h1>
            <p class="page-subtitle col-md-8 mx-auto mt-2">Sve informacije o proceduri iznajmljivanja, osiguranju i dokumentaciji na jednom mestu.</p>
        </div>
        <div class="row g-4 text-center mb-5">
            <div class="col-md-3">
                <div class="info-card p-4 h-100">
                    <div class="icon-box bg-primary-icon bg-opacity-10 text-primary">
                        <i class="fa-solid fa-user-check fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark">21+ Godina</h6>
                    <p class="small text-muted mb-0">Minimum 2 godine posedovanja vozačke dozvole.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-card p-4 h-100">
                    <div class="icon-box bg-success bg-opacity-10 text-success">
                        <i class="fa-solid fa-file-contract fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Dokumenti</h6>
                    <p class="small text-muted mb-0">Važeća lična karta ili pasoš uz vozačku dozvolu.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-card p-4 h-100">
                    <div class="icon-box bg-warning bg-opacity-10 text-warning">
                        <i class="fa-solid fa-shield-cat fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Kasko Osiguranje</h6>
                    <p class="small text-muted mb-0">Osnovno kasko osiguranje uračunato u cenu.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-card p-4 h-100">
                    <div class="icon-box bg-info bg-opacity-10 text-info">
                        <i class="fa-solid fa-gas-pump fa-lg"></i>
                    </div>
                    <h6 class="fw-bold text-dark">Gorivo</h6>
                    <p class="small text-muted mb-0">Pravilo "Puno za puno" pri preuzimanju i vraćanju.</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <?php if (!empty($grouped_faqs)): ?>
                    <?php $catIndex = 0; foreach ($grouped_faqs as $catName => $items): $catIndex++; ?>
                        <?php $icon = $category_icons[$catName] ?? 'fa-circle-info'; ?>
                        <div class="faq-group-card p-4 mb-4">
                            <h5 class="fw-bold mb-3 faq-group-title d-flex align-items-center">
                                <i class="fa-solid <?= $icon ?> text-primary me-2"></i><?= htmlspecialchars($catName) ?>
                            </h5>
                            <div class="accordion accordion-flush faq-accordion" id="accordionCat<?= $catIndex ?>">
                                <?php foreach ($items as $i => $item): ?>
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading<?= $catIndex ?>_<?= $i ?>">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $catIndex ?>_<?= $i ?>">
                                                <?= htmlspecialchars($item->question) ?>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $catIndex ?>_<?= $i ?>" class="accordion-collapse collapse" data-bs-parent="#accordionCat<?= $catIndex ?>">
                                            <div class="accordion-body">
                                                <?= nl2br(htmlspecialchars($item->answer)) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>