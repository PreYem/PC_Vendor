<?php
include_once ("../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../User/User_SignIn.php");
    exit;
}


$User_ID = $_SESSION['User_ID'];
$query = "SELECT User_Role FROM Users WHERE User_ID = :User_ID";
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

    $User_D = "SELECT User_ID , User_LastName FROM Users WHERE User_ID = :User_ID";
    $pdoUser_D  = $connexion->prepare($User_D);
    $pdoUser_D ->execute([':User_ID' => $User_ID]);
    $User = $pdoUser_D ->fetch(PDO::FETCH_ASSOC);





    if ($User) {
        $User_LastName = $User['User_LastName'];
        $User_ID = $User['User_ID'];
    } else {
        $_SESSION['User_Delete'] = "Error : Unable to locate the user";
        header("Location: ../User/User_Management.php");
        exit();

    }


    $Delete_User_Cart = "DELETE FROM ShoppingCart WHERE User_ID = :User_ID";
    $pdoDelete_User_Cart = $connexion->prepare($Delete_User_Cart);
    $pdoDelete_User_Cart->execute(['User_ID' => $User_ID]);

    $Clean_OrderItems = "DELETE FROM OrderItems WHERE Order_ID IN (SELECT Order_ID FROM Orders WHERE User_ID = :User_ID)";
    $pdoClean_OrderItems = $connexion->prepare($Clean_OrderItems);
    $pdoClean_OrderItems->execute(['User_ID' => $User_ID]);

    $Clearn_Orders = "DELETE FROM Orders WHERE User_ID = :User_ID";
    $pdosClearn_Orders = $connexion->prepare($Clearn_Orders);
    $pdosClearn_Orders->execute(['User_ID' => $User_ID]);


    $Delete_User_Query = "DELETE FROM Users WHERE User_ID = :User_ID";
    $pdostmtDeleteUser = $connexion->prepare($Delete_User_Query);
    $pdostmtDeleteUser->execute([':User_ID' => $User_ID]);


    $_SESSION['User_Delete'] = '[' . $User_LastName . ']' . "'s Account Has Been Deleted Successfully";
    header("Location: ../User/User_Management.php");

    exit;
} else {
    header("Location: ../User/User_Management.php");
    exit;
}