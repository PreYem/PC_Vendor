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

    if ($userRole !== 'Owner' && $userRole !== 'Admin' && $userRole !== 'Client') {

        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
} ;



if (!empty($_GET['id'])) {

    $User_ID = $_SESSION['User_ID'];

    $Order_ID = $_GET['id'] ;

    $Order_Status = 'Cancelled by User' ;

    $Cancel_Order = "UPDATE Orders SET Order_Status = :Order_Status WHERE Order_ID = :Order_ID AND User_ID = :User_ID" ;
    $pdostmt = $connexion->prepare($Cancel_Order); // Prepare the SQL statement
    $pdostmt->execute([
        ':Order_Status' => $Order_Status,
        ':Order_ID' => $Order_ID,
        ':User_ID' => $User_ID
    ]);

    header("Location: User_OrderStatus.php");


}


