<?php
include_once ("../DB_Connexion.php");

function formatNumber($number)
{
    return number_format($number, 0, '', ' ');
}

session_start();

if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
    // User is not logged in, redirect to login page
    header("Location: ../User/User_SignIn.php");
    exit; // Ensure script stops after redirection
}

$User_ID = $_SESSION['User_ID'];
$query = "SELECT User_Role FROM Users WHERE User_ID = :User_ID";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':User_ID' => $User_ID]);

if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $userRole = $row['User_Role'];


    if ($userRole !== 'Owner' && $userRole !== 'Admin' && $userRole !== 'Client') {

        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
}
;

$cartItem = null ;


if (!empty($_GET["id"])) {
    $Product_ID = $_GET["id"];
    $User_ID = $_SESSION['User_ID'];


    $ProductQuantity = "SELECT Product_Quantity FROM Products WHERE Product_ID = :Product_ID";
    $pdostmtProduct = $connexion->prepare($ProductQuantity);
    $pdostmtProduct->execute([':Product_ID' => $Product_ID]);
    $product = $pdostmtProduct->fetch(PDO::FETCH_ASSOC);




    if ($product['Product_Quantity'] > 0) {
        $currentDBQuantity = $product['Product_Quantity'];

        $cartQuery = "SELECT CartItem_ID, Quantity FROM ShoppingCart WHERE User_ID = :User_ID AND Product_ID = :Product_ID";
        $pdostmtCart = $connexion->prepare($cartQuery);
        $pdostmtCart->execute([
            'User_ID' => $User_ID,
            'Product_ID' => $Product_ID
        ]);
        $cartItem = $pdostmtCart->fetch(PDO::FETCH_ASSOC);


    }

    if ($cartItem) {
        
        $cartQuantity = min($cartItem['Quantity'] + 1, $currentDBQuantity);

        $cartQuantity_Increment = "UPDATE ShoppingCart SET Quantity = :Quantity WHERE CartItem_ID = :CartItem_ID";
        $pdostmtUpdate = $connexion->prepare($cartQuantity_Increment);
        $pdostmtUpdate->execute([
            ':Quantity' => $cartQuantity,
            ':CartItem_ID' => $cartItem['CartItem_ID']
        ]);
        echo "1";
    } else {
        $Shopping_Query = "INSERT INTO ShoppingCart (User_ID , Product_ID , Quantity)
        Values(:User_ID , :Product_ID , 1)";
        $pdostmt = $connexion->prepare($Shopping_Query);
        $pdostmt->execute([
            ':User_ID' => $User_ID,
            ':Product_ID' => $Product_ID
        ]);
        echo "2";

    }


    
     header("Location: ../index.php");
    
     exit;
    
} else {
    echo "Error";
}
