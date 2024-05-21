<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>List of Manufactuers</title>
    <link href="../output.css" rel="stylesheet">

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
    }
    ;

    $query = "select Manufacturer_ID,Manufacturer_Name, Manufacturer_Desc from Manufacturers";

    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute();

    ?>

</head>

<body class="bg-gray-200 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="mb-4 flex justify-between">
            <a href="Manufacturers_Add.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">New Manufacturer</a>
            <a href="../index.php" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Main Page</a>
        </div>

        <h1 class="text-2xl font-bold mb-4">List of Manufacturers</h1>

        <table class="w-full border border-black">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-4 py-2">Manufacturer ID</th>
                    <th class="border px-4 py-2">Manufacturer Name</th>
                    <th class="border px-4 py-2">Manufacturer Description</th>
                    <th class="border px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($ligne = $pdostmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="border px-4 py-2"><?php echo $ligne["Manufacturer_ID"] ?></td>
                        <td class="border px-4 py-2"><?php echo $ligne["Manufacturer_Name"] ?></td>
                        <td class="border px-4 py-2"><?php echo $ligne["Manufacturer_Desc"] ?></td>
                        <td class="border px-4 py-2">
                            <a href="Manufacturers_Modify.php?id=<?php echo urlencode($ligne["Manufacturer_Name"]) ?>" class="text-blue-600 hover:underline">Edit</a>
                            <a href="Manufacturers_Delete.php?id=<?php echo urlencode($ligne["Manufacturer_Name"]) ?>" class="text-red-600 hover:underline ml-2">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>


</html>