<?php
include_once ("../DB_Connexion.php");

session_start(); // Start or resume existing session

// Check if user is logged in and has the appropriate role
if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
    // User is not logged in, redirect to login page
    header("Location: ../User/User_SignIn.php");
    exit; // Ensure script stops after redirection
}

// Retrieve the user's role from the database based on User_ID stored in session
$userId = $_SESSION['User_ID'];
$query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':userId' => $userId]);

if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $userRole = $row['User_Role'];

    // Check if the user has the required role (Owner or Admin) to access this page
    if ($userRole !== 'Owner' && $userRole !== 'Admin') {
        // User does not have sufficient permissions, redirect to unauthorized page
        header("Location: ../User/User_Unauthorized.html");
        exit; // Ensure script stops after redirection
    }
}
;

if (!empty($_GET["id"])) {
    $productId = $_GET["id"];

    // Delete associated specifications first
    $queryDeleteSpecs = "DELETE FROM ProductSpecifications WHERE Product_ID = :productId";
    $pdostmtDeleteSpecs = $connexion->prepare($queryDeleteSpecs);
    $pdostmtDeleteSpecs->execute(["productId" => $productId]);


    $Delete_From_Cart = "DELETE FROM ShoppingCart WHERE Product_ID = :productId";
    $pdostmtDeleteCart = $connexion->prepare($Delete_From_Cart);
    $pdostmtDeleteCart->execute(["productId" => $productId]);

    // Then delete the product
    $queryDeleteProduct = "DELETE FROM Products WHERE Product_ID = :productId";
    $pdostmtDeleteProduct = $connexion->prepare($queryDeleteProduct);
    $pdostmtDeleteProduct->execute(["productId" => $productId]);

    // Redirect back to the product list page
    header("Location: Products_List.php");
    exit();
} else {
    // If no product ID is provided, redirect back to the product list page
    header("Location: Products_List.php");
    exit();
}


