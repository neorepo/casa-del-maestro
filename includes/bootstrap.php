<?php

date_default_timezone_set('AMERICA/ARGENTINA/BUENOS_AIRES');

// requirements
require("constants.php");
require("functions.php");

session_name('ID');
// enable sessions
session_start();

require_once '../src/Token.php';

require_once '../src/Db.php';
require_once '../src/Flash.php';

// require authentication for most pages
if (!preg_match("{(?:usuario_login|usuario_logout|usuario_register)\.php$}", $_SERVER["PHP_SELF"]))
{
    if (empty($_SESSION["uid"]))
    {
        redirect("usuario_login.php");
    }
}