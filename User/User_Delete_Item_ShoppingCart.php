<?php

include_once ("../DB_Connexion.php");
session_start();
if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../User/User_SignIn.php");
    exit;
}

$User_ID = $_SESSION['User_ID'];
$query = "SELECT User_Role, User_Username FROM Users WHERE User_ID = :User_ID";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':User_ID' => $User_ID]);

if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $User_Role = $row['User_Role'];
    $User_Username = $row['User_Username'];

    if ($User_Role === 'Owner') {
        $showUserManagement = true;
    } else {
        $showUserManagement = false;
    }


    if ($User_Role !== 'Owner' && $User_Role !== 'Admin' && $User_Role !== 'Client') {
        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
}
;

if (!empty($_GET['id'])) {
    $CartItem_ID = $_GET['id'];


    if ($CartItem_ID !== 'clearAllCart') {

        $removeFromCart = "DELETE FROM ShoppingCart WHERE CartItem_ID = :CartItem_ID AND User_ID = :User_ID";
        $pdostmt = $connexion->prepare($removeFromCart);
        $pdostmt->execute([':CartItem_ID' => $CartItem_ID, ':User_ID' => $User_ID]);

        header("Location: User_ShoppingCart.php");
        exit;
    } else {

        $removeAllFromCart = "DELETE FROM ShoppingCart WHERE User_ID = :User_ID";
        $pdostmt = $connexion->prepare($removeAllFromCart);
        $pdostmt->execute([':User_ID' => $User_ID]);

        header("Location: User_ShoppingCart.php");
        exit;
    }

}
