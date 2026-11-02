<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Copy this file to application/config/owner_databases.local.php ONLY on your hosting.
// Do not commit the local file or actual database passwords to GitHub.
// Use a dedicated MySQL account with SELECT permission only.
// The POS default database is included automatically as "Base principal".
return array(
    'Segundo negocio' => array(
        'hostname' => '127.0.0.1',
        'database' => 'NOMBRE_BASE_SEGUNDA',
        'username' => 'USUARIO_SOLO_LECTURA',
        'password' => 'CONTRASENA_SOLO_EN_HOSTING',
        'dbprefix' => 'phppos_',
    ),
);
