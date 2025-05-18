<?php
require_once 'includes/functions.php';


if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


session_destroy();


redirect('index.php');


exit();
?>