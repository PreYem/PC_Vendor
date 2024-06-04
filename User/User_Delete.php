<?php
include_once ("../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../User/User_SignIn.php");
    exit;
}


$User_ID = $_SESSION['User_ID'];
$query = "SELECT User_Role , User_LastName FROM Users WHERE User_ID = :User_ID";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':User_ID' => $User_ID]);

if ($User = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $User_Role = $User['User_Role'];

    if ($User_Role !== 'Owner') {

        header("Location: ../.");
        exit;
    }
}
;

if (!empty($_GET["id"])) {
    $User_ID = $_GET["id"];


    $Delete_User_Cart = "DELETE FROM ShoppingCart WHERE User_ID = :User_ID";
    $pdostmt = $connexion->prepare($Delete_User_Cart);
    $pdostmt->execute(['User_ID' => $User_ID]);

    $Clean_OrderItems = "DELETE FROM OrderItems WHERE Order_ID IN (SELECT Order_ID FROM Orders WHERE User_ID = :User_ID)";
    $pdostmt = $connexion->prepare($Clean_OrderItems);
    $pdostmt->execute(['User_ID' => $User_ID]);

    $Clearn_Orders = "DELETE FROM Orders WHERE User_ID = :User_ID";
    $pdostmt = $connexion->prepare($Clearn_Orders);
    $pdostmt->execute(['User_ID' => $User_ID]);


    $Delete_User_Query = "DELETE FROM Users WHERE User_ID = :User_ID";
    $pdostmtDeleteUser = $connexion->prepare($Delete_User_Query);
    $pdostmtDeleteUser->execute([':User_ID' => $User_ID]);


    $_SESSION['User_Delete'] = $User['User_LastName'] . " Account Deleted Successfully";
    header("Location: ../User/User_Management.php");

    exit;
} else {
    header("Location: ../User/User_Management.php");
    exit;
}