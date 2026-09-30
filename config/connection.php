<?php
    define("SERVER","");
    define("DATABASE","");
    define("USERNAME","");
    define("PASSWORD","");
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
