<?php
include_once ("../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../User/User_SignIn.php");
    exit;
}


$userId = $_SESSION['User_ID'];
$query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':userId' => $userId]);

if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $userRole = $row['User_Role'];


    if ($userRole !== 'Owner' && $userRole !== 'Admin') {

        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
}
;

if (!empty($_GET["id"])) {

    $productId = $_GET["id"];
    try {
        
        $connexion->beginTransaction(); 
        
        $queryDeleteSpecs = "DELETE FROM ProductSpecifications WHERE Product_ID = :productId";
        $pdostmtDeleteSpecs = $connexion->prepare($queryDeleteSpecs);
        $pdostmtDeleteSpecs->execute(["productId" => $productId]);

        $queryDeleteSpecs = "DELETE FROM ShoppingCart WHERE Product_ID = :productId";
        $pdostmtDeleteSpecs = $connexion->prepare($queryDeleteSpecs);
        $pdostmtDeleteSpecs->execute(["productId" => $productId]);
    
        
        $Delete_From_Cart = "DELETE FROM ShoppingCart WHERE Product_ID = :productId";
        $pdostmtDeleteCart = $connexion->prepare($Delete_From_Cart);
        $pdostmtDeleteCart->execute(["productId" => $productId]);
    
        
        $queryDeleteProduct = "DELETE FROM Products WHERE Product_ID = :productId";
        $pdostmtDeleteProduct = $connexion->prepare($queryDeleteProduct);
        $pdostmtDeleteProduct->execute(["productId" => $productId]);
    
        $connexion->commit(); 

    
        $_SESSION['Product_Delete'] = "Product Deleted Successfully";

    } catch (PDOException $e) {
        $connexion->rollBack();

        $_SESSION['Product_Delete'] = "Error : Product could not be deleted, check if a client order contains this product. ";
    }
    

    
    header("Location: ../.");
    exit();
} else {

    $_SESSION['Product_Delete'] = "Product Could not be deleted";
}


