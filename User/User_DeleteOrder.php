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


    if ($User_Role !== 'Owner' && $User_Role !== 'Admin') {

        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
}
;

if (!empty($_GET["id"])) {
    $Order_ID = $_GET["id"];

    $Find_User = "SELECT users.User_LastName FROM orders
    INNER JOIN users ON orders.User_ID = users.User_ID
    WHERE orders.Order_ID = :order_id";
    $pdostmt = $connexion->prepare($Find_User);
    $pdostmt->execute([':order_id' => $Order_ID]);
    $userLastName = $pdostmt->fetchColumn();

    try {
        $connexion->beginTransaction(); 

        $Clear_OrderItems = "DELETE FROM OrderItems WHERE Order_ID = $Order_ID";
        $pdoClear_OI = $connexion->prepare($Clear_OrderItems);
        $pdoClear_OI->execute();

        $Clear_Orders = "DELETE FROM Orders WHERE Order_ID = $Order_ID";
        $pdoClear_O = $connexion->prepare($Clear_Orders);
        $pdoClear_O->execute();


        $_SESSION['Order_Deleted'] = "Order has been deleted for the following user : " . $userLastName;

        $connexion->commit(); 

    } catch (PDOException $e) {
        $connexion->rollBack();
        $_SESSION['Order_Deleted'] = 'Error : Order could not be deleted' ;
    }

    header("Location: User_GlobalOrders.php");
    exit();

}