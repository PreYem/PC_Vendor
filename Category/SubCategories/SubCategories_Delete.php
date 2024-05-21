<?php
include_once ("../../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../../User/User_SignIn.php");
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

        header("Location: ../../User/User_Unauthorized.html");
        exit;
    }
}
;

if (!empty($_GET["id"])) {
    $SubCategory_ID = $_GET["id"];
    $UnspecifiedSubCategory = 'Unspecified';

    $ResetProduct = "UPDATE Products SET SubCategory_ID = (SELECT SubCategory_ID FROM SubCategories 
    WHERE SubCategory_Name = :UnspecifiedCategory)
    WHERE SubCategory_ID = :SubCategory_ID";

    // Prepare and execute the update query
    $pdostmtUpdate = $connexion->prepare($ResetProduct);
    $pdostmtUpdate->execute([
        "UnspecifiedCategory" => $UnspecifiedSubCategory,
        "SubCategory_ID" => $SubCategory_ID
    ]);


    $deleteQuery = "DELETE FROM SubCategories WHERE SubCategory_ID = :SubCategory_ID";
    $pdostmtDelete = $connexion->prepare($deleteQuery);
    $pdostmtDelete->execute(["SubCategory_ID" => $SubCategory_ID]);

    header("Location: SubCategories_List.php");
    exit();


}
;
