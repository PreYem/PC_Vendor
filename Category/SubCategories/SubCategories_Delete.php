<?php
include_once ("../../DB_Connexion.php");

session_start();


if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

    header("Location: ../../.");
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
        header("Location: ../../.");
        exit;
    }
}
;

if (!empty($_GET["id"])) {
    $SubCategory_ID = $_GET["id"];
    

    $Q_Unspecified_SubCategory = "SELECT SubCategory_ID FROM SubCategories WHERE SubCategory_Name = 'Unspecified'";
    $pdoQ_Unspecified_SubCategory = $connexion->prepare($Q_Unspecified_SubCategory);
    $pdoQ_Unspecified_SubCategory->execute();
    $Unspecified_SubCategory_ID = $pdoQ_Unspecified_SubCategory->fetchColumn();



    $Q_ResetProduct = "UPDATE Products SET SubCategory_ID = :Unspecified_SubCategory_ID WHERE SubCategory_ID = :SubCategory_ID ";
    $pdoQ_ResetProduct = $connexion->prepare($Q_ResetProduct);
    $pdoQ_ResetProduct->execute([
        ':Unspecified_SubCategory_ID' => $Unspecified_SubCategory_ID , 
        'SubCategory_ID' => $SubCategory_ID 
    ]);



    $Q_Delete_SubCategory = "DELETE FROM SubCategories WHERE SubCategory_ID = :SubCategory_ID";
    $pdo_Q_Delete_SubCategory = $connexion->prepare($Q_Delete_SubCategory);
    $pdo_Q_Delete_SubCategory->execute(["SubCategory_ID" => $SubCategory_ID]);

    $_SESSION['SubCategory_Delete'] = "SubCategory Deleted Successfully";
    header("Location: SubCategories_List.php");
    exit();


} else {
    $_SESSION['SubCategory_Delete'] = "Error : SubCategory Could Not Be Deleted";
}
;
