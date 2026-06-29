<?php

  // Copia este archivo como config.php y ajusta los valores a tu entorno

  // DB Params
  define('DB_HOST', 'localhost');
  define('DB_USER', 'root');
  define('DB_PASS', '');
  define('DB_NAME', 'finanzas_db');

  // App Root
  define('APPROOT', dirname(dirname(__FILE__)));
  // URL Root (ajustar en producción: 'https://tudominio.com')
  define('URLROOT', 'http://localhost/Finanzas');
  // Site Name
  define('SITENAME', 'Control de Finanzas');
