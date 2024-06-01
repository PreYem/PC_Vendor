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
    $Manufacturer_ID = $_GET["id"];


    $queryUnspecified = "SELECT Manufacturer_ID FROM Manufacturers WHERE Manufacturer_Name = 'Unspecified'";
    $pdostmtUnspecified = $connexion->prepare($queryUnspecified);
    $pdostmtUnspecified->execute();
    $Unspecified_Manufacturer_ID = $pdostmtUnspecified->fetchColumn();

    
    $updateQuery = "UPDATE Products SET Manufacturer_ID = :Unspecified_Manufacturer_ID WHERE Manufacturer_ID = :Manufacturer_ID ";
    $pdostmtUpdate = $connexion->prepare($updateQuery);
    $pdostmtUpdate->execute(["Manufacturer_ID" => $Manufacturer_ID]);

    // Delete the manufacturer
    $deleteQuery = "DELETE FROM Manufacturers WHERE Manufacturer_ID = :Manufacturer_ID";
    $pdostmtDelete = $connexion->prepare($deleteQuery);
    $pdostmtDelete->execute(["Manufacturer_ID" => $Manufacturer_ID]);

    header("Location: Manufacturers_List.php");
    exit();
}

