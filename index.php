<?php
    require_once 'helpers/database.php';
    require_once 'routes/routes.php';
    header('Content-Type: application/json');
    handleRequest($pdo);
?>
