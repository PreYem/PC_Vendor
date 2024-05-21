<?php

include_once ("../DB_Connexion.php");

session_start(); 


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../User/User_SignIn.php");
    exit; 
}


$userId = $_SESSION['User_ID'];
$query = "SELECT User_Role, User_Username FROM Users WHERE User_ID = :userId";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':userId' => $userId]);



if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $userRole = $row['User_Role'];
    $User_Username = $row['User_Username'];

    if ($userRole === 'Owner') {
        $showUserManagement = true;
    } else {
        $showUserManagement = false;
    }

 
    if ($userRole !== 'Owner' && $userRole !== 'Admin') {

        header("Location: ../User/User_Unauthorized.html");
        exit;
    }
} ;

if (!empty($_GET["id"])) {
    $Category_Name = urldecode($_GET["id"]);

    // Check if there are subcategories associated with the main category
    $querySubcategories = "SELECT SubCategory_ID FROM SubCategories WHERE Category_ID = 
    (SELECT Category_ID FROM Categories WHERE Category_Name = :Category_Name)";
    $pdostmtSubcategories = $connexion->prepare($querySubcategories);
    $pdostmtSubcategories->execute(["Category_Name" => $Category_Name]);

    $subcategories = $pdostmtSubcategories->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($subcategories)) {
        // Reassign subcategories to "Unspecified" category
        $queryUpdateSubcategories = "UPDATE SubCategories SET Category_ID = :unspecifiedCategoryId WHERE Category_ID = 
        (SELECT Category_ID FROM Categories WHERE Category_Name = :Category_Name)";
        $pdostmtUpdateSubcategories = $connexion->prepare($queryUpdateSubcategories);
        $pdostmtUpdateSubcategories->execute(["unspecifiedCategoryId" => $unspecifiedCategoryId, "Category_Name" => $Category_Name]);
    }

    // Get the ID of the "Unspecified" category
    $queryUnspecified = "SELECT Category_ID FROM Categories WHERE Category_Name = 'Unspecified'";
    $pdostmtUnspecified = $connexion->prepare($queryUnspecified);
    $pdostmtUnspecified->execute();
    $unspecifiedCategoryId = $pdostmtUnspecified->fetchColumn();

    // Update products with the specified category name to use the "Unspecified" category
    $updateQuery = "UPDATE Products SET Category_ID = :unspecifiedCategoryId WHERE Category_ID = 
    (SELECT Category_ID FROM Categories WHERE Category_Name = :Category_Name)";
    $pdostmtUpdate = $connexion->prepare($updateQuery);
    $pdostmtUpdate->execute(["unspecifiedCategoryId" => $unspecifiedCategoryId, "Category_Name" => $Category_Name]);

    // Delete the category
    $deleteQuery = "DELETE FROM Categories WHERE Category_Name = :Category_Name";
    $pdostmtDelete = $connexion->prepare($deleteQuery);
    $pdostmtDelete->execute(["Category_Name" => $Category_Name]);

    header("Location: Categories_List.php");
    exit();
}


