<?php

include_once ("../DB_Connexion.php"); // Include database connection at the beginning
    
    session_start(); // Start or resume existing session
    
    // Check if user is logged in and has the appropriate role
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
        // User is not logged in, redirect to login page
        header("Location: ../User/User_SignIn.php");
        exit; // Ensure script stops after redirection
    }

    // Retrieve the user's role from the database based on User_ID stored in session
    $userId = $_SESSION['User_ID'];
    $query = "SELECT User_Role, User_Username FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);

    

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];
        $User_Username = $row['User_Username'] ;

        if ($userRole === 'Owner') {
            $showUserManagement = true; // Flag to show the "User Management" button/link
        } else {
            $showUserManagement = false; // Hide the "User Management" button/link for other roles
        }

        // Check if the user has the required role (Owner or Admin) to access this page
        if ($userRole !== 'Owner' && $userRole !== 'Admin') {
            // User does not have sufficient permissions, redirect to unauthorized page
            header("Location: ../User/User_Unauthorized.html");
            exit; // Ensure script stops after redirection
        }
    } ;

if (!empty($_GET["id"])) {
    $Manufacturer_Name = urldecode($_GET["id"]);

    // Get the ID of the "Unspecified" manufacturer
    $queryUnspecified = "SELECT Manufacturer_ID FROM Manufacturers WHERE Manufacturer_Name = 'Unspecified'";
    $pdostmtUnspecified = $connexion->prepare($queryUnspecified);
    $pdostmtUnspecified->execute();
    $unspecifiedManufacturerId = $pdostmtUnspecified->fetchColumn();

    // Update products with the specified manufacturer name to use the "Unspecified" manufacturer
    $updateQuery = "UPDATE Products SET Manufacturer_ID = :unspecifiedManufacturerId WHERE Manufacturer_ID = (SELECT Manufacturer_ID FROM Manufacturers WHERE Manufacturer_Name = :Manufacturer_Name)";
    $pdostmtUpdate = $connexion->prepare($updateQuery);
    $pdostmtUpdate->execute(["unspecifiedManufacturerId" => $unspecifiedManufacturerId, "Manufacturer_Name" => $Manufacturer_Name]);

    // Delete the manufacturer
    $deleteQuery = "DELETE FROM Manufacturers WHERE Manufacturer_Name = :Manufacturer_Name";
    $pdostmtDelete = $connexion->prepare($deleteQuery);
    $pdostmtDelete->execute(["Manufacturer_Name" => $Manufacturer_Name]);

    header("Location: Manufacturers_List.php");
    exit();
}

