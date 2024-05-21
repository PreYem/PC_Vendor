<?php

session_start();

$_SESSION = array();

session_destroy();

header("Location: ../index.php");
exit; // Ensure that no further code is executed after redirection
