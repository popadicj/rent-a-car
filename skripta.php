<?php
require_once "config/connection.php";

$ime = 'Marko';
$prezime = 'Markovic';
$email = 'marko.markovic@rentacar.com';
$lozinka = password_hash('markovic123', PASSWORD_BCRYPT);
$uloga = 2;

$stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role_id) VALUES (?, ?, ?, ?, ?)");
if ($stmt->execute([$ime, $prezime, $email, $lozinka, $uloga])) {
    echo "Admin uspešno kreiran!";
} else {
    echo "Greška pri kreiranju.";
}
?>