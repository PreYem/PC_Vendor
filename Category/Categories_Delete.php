<?php

include_once ("../DB_Connexion.php");
session_start();
if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../.");
    exit;
}

$User_ID = $_SESSION['User_ID'];
$query = "SELECT User_Role, User_Username , User_FirstName , User_LastName FROM Users WHERE User_ID = :User_ID";
$pdostmt = $connexion->prepare($query);
$pdostmt->execute([':User_ID' => $User_ID]);

if ($User = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
    $User_Role = $User['User_Role'];
    $User_Username = $User['User_Username'];
    $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];

    if ($User_Role == 'Client') {
        header("Location: ../.");
        exit;
    }
}
;

if (!empty($_GET["id"])) {
    $Category_ID = $_GET["id"];



    $Unspecified_Category_ID = "SELECT Category_ID FROM Categories WHERE Category_Name = 'Unspecified'";
    $pdoUnspecified_Category_ID = $connexion->prepare($Unspecified_Category_ID);
    $pdoUnspecified_Category_ID->execute();
    $Unspecified_Category_ID = $pdoUnspecified_Category_ID->fetchColumn();



    $querySubcategories = "SELECT SubCategory_ID FROM SubCategories WHERE Category_ID = :Unspecified_Category_ID";
    $pdostmtSubcategories = $connexion->prepare($querySubcategories);
    $pdostmtSubcategories->execute(["Unspecified_Category_ID" => $Unspecified_Category_ID]);
    $subcategories = $pdostmtSubcategories->fetchColumn();

    if (!empty($subcategories)) {
        $queryUpdateSubcategories = "UPDATE SubCategories SET Category_ID = :Unspecified_Category_ID WHERE Category_ID = :Category_ID";
        $pdostmtUpdateSubcategories = $connexion->prepare($queryUpdateSubcategories);
        $pdostmtUpdateSubcategories->execute([
            'Unspecified_Category_ID' => $Unspecified_Category_ID, 
            'Category_ID' => $Category_ID
            ]);
    }

    $updateQuery = "UPDATE Products SET Category_ID = :Unspecified_Category_ID WHERE Category_ID = :Category_ID";
    $pdostmtUpdate = $connexion->prepare($updateQuery);
    $pdostmtUpdate->execute([
        "Category_ID" => $Category_ID,
        ":Unspecified_Category_ID" => $Unspecified_Category_ID
        
    ]);

    // Delete the category
    $deleteQuery = "DELETE FROM Categories WHERE Category_ID = :Category_ID";
    $pdostmtDelete = $connexion->prepare($deleteQuery);
    $pdostmtDelete->execute(["Category_ID" => $Category_ID]);

    $_SESSION['Category_Delete'] = "Category Deleted Successfully";
    header("Location: Categories_List.php");
    
    exit();
} else {
    $_SESSION['Category_Delete'] = "Error : Category Could Not Be Deleted";
    header("Location: ../.");
    exit();
}


