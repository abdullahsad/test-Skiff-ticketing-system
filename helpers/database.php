<?php
    // $host = '20.84.41.10';
    // $db = 'ticketing';
    // $user = 'saad2';
    // $pass = 'R3s-HAnq]Wk82jN';
    $host = '127.0.0.1';
    $db = 'ticketing';
    $user = 'root';
    $pass = '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Connected successfully";
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
?>
