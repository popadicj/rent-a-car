<?php
    define("SERVER","sql108.infinityfree.com");
    define("DATABASE","if0_42747848_rentacar");
    define("USERNAME","if0_42747848");
    define("PASSWORD","0K2ufGd5W1");
    define("PORT", "3306");

    try {
    $conn = new PDO("mysql:host=".SERVER.";port=".PORT.";dbname=".DATABASE.";charset=utf8", USERNAME, PASSWORD);

    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
    catch(PDOException $ex){ 
        die("Konekcija nije uspela: " . $ex->getMessage()); 
    }
?>