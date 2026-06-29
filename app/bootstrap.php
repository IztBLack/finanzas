<?php

require_once __DIR__ . '/libraries/Controller.php';

// Load Config
require_once __DIR__ . '/config/config.php';
// Load Helpers
require_once __DIR__ . '/helpers/session_helper.php';
require_once __DIR__ . '/helpers/url_helper.php';


// Autoload Core Classes
spl_autoload_register(function ($className) {
    // Usamos APPROOT para que la ruta sea absoluta y file_exists funcione correctamente
    $file = APPROOT . '/libraries/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

?>

