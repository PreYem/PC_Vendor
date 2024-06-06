<?php

include_once ("../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../.");
    exit();
}


$userId = $_SESSION['User_ID'];
$query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':userId' => $userId]);

if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $userRole = $row['User_Role'];

    if ($userRole !== 'Owner' && $userRole !== 'Admin' && $userRole !== 'Client') {

        header("Location: ../.");
        exit;
    }
}
;



if (!empty($_GET['id'])) {

    $User_ID = $_SESSION['User_ID'];
    $Order_ID = $_GET['id'];

    $Who_Cancelled_ID = "SELECT User_ID From Orders WHERE Order_ID  = $Order_ID ";
    $pdostmt = $connexion->prepare($Who_Cancelled_ID);
    $pdostmt->execute();
    $Who_Cancelled_ID = $pdostmt->fetch(PDO::FETCH_ASSOC);

    try {

        if ($Who_Cancelled_ID['User_ID'] == $User_ID) {
            $Cancel_Order = "UPDATE Orders SET Order_Status = 'Cancelled by User' WHERE Order_ID = $Order_ID AND User_ID = :Who_Cancelled_ID";
            $pdostmt = $connexion->prepare($Cancel_Order);
            $pdostmt->execute(['Who_Cancelled_ID' => $Who_Cancelled_ID['User_ID']]);
            header("Location: User_PendingOrders");
            $_SESSION['Order_Cancelled'] = "Order has been cancelled!";


        } else {
            $Cancel_Order = "UPDATE Orders SET Order_Status = 'Cancelled by Management' WHERE Order_ID = $Order_ID AND User_ID = :Who_Cancelled_ID";
            $pdostmt = $connexion->prepare($Cancel_Order);
            $pdostmt->execute(['Who_Cancelled_ID' => $Who_Cancelled_ID['User_ID']]);

            header("Location: User_GlobalOrders");
            $_SESSION['Order_Cancelled'] = "Order has been cancelled!";
        }

    } catch (PDOException) {
        $_SESSION['Order_Cancelled'] = "Error : Order could not be cancelled!";
        header("Location: User_GlobalOrders");
    }

} else {
    $_SESSION['Product_Delete'] = "Error : Order invalid";
    header("Location: ../.");
    exit();
}


