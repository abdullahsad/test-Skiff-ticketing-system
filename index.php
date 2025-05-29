<?php
    require_once 'helpers/database.php';
    require_once 'routes/routes.php';
    header('Content-Type: application/json');
    if (!function_exists('dd')) {
        function dd(...$vars) {
            echo '<pre>';
            foreach ($vars as $var) {
                var_dump($var);
            }
            echo '</pre>';
            die();
        }
    }
    handleRequest($pdo);
?>
