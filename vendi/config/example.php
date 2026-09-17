<?php
return [
 'app'=>['name'=>'Vendi POS','env'=>'production','timezone'=>'America/Mexico_City'],
 'database'=>['dsn'=>'mysql:host=localhost;dbname=vendi;charset=utf8mb4','user'=>'','password'=>''],
 'licensing'=>['remote_activation_required'=>true,'branch_addon_required'=>true],
 'security'=>['session_secure'=>true,'csrf'=>true]
];