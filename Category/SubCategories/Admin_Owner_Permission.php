<?php 
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
        $User_Username = $row['User_Username'];

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
    };