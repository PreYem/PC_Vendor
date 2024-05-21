<?php
include_once ("../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
    // User is not logged in, redirect to login page
    header("Location: ../User/User_SignIn.php");
    exit;
}


$userId = $_SESSION['User_ID'];
$query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':userId' => $userId]);

if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $userRole = $row['User_Role'];

    if ($userRole !== 'Owner') {

        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
} ;

if (!empty($_GET["id"])) {
    $User_ID = $_GET["id"];

    $Delete_User_Query = "DELETE FROM Users WHERE User_ID = :User_ID" ;
    $pdostmtDeleteUser = $connexion->prepare($Delete_User_Query) ;

    $pdostmtDeleteUser->execute([':User_ID' => $User_ID]) ;
    header("Location: ../User/User_Management.php");
    exit;
} else {
    header("Location: ../User/User_Management.php");
    exit;
}