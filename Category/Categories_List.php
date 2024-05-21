<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>List of Categories</title>
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
    $Error_Message = '';

    $query = "SELECT 
    c.Category_ID,
    c.Category_Name,
    c.Category_Desc,
    COUNT(DISTINCT p.Product_ID) AS ProductCount,
    COUNT(DISTINCT s.SubCategory_ID) AS SubCategoryCount
    FROM 
    Categories c
    LEFT JOIN 
    Products p ON c.Category_ID = p.Category_ID
    LEFT JOIN 
    SubCategories s ON c.Category_ID = s.Category_ID
    GROUP BY 
    c.Category_ID, c.Category_Name, c.Category_Desc";

    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute();

    // Fetch all rows as associative array
    $categoryCounts = $pdostmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('tbody tr');
            console.log("1")
            rows.forEach(row => {
                const categoryNameCell = row.querySelector('td:nth-child(2)');
                const categoryName = categoryNameCell.textContent.trim();
                console.log("2")

                // Check if category name is "Unspecified" and disable buttons accordingly
                if (categoryName === "Unspecified") {
                    const editButton = row.querySelector('.edit-button');
                    const deleteButton = row.querySelector('.delete-button');
                    console.log("3")

                    editButton.disabled = true;

                    deleteButton.disabled = true;

                    editButton.hidden = true;

                    deleteButton.hidden= true;

                }
            });
        });
    </script>




</head>

<body class="bg-gray-100 font-sans">

    <div class="container mx-auto px-4 py-8">

        <div class="flex justify-between items-center mb-8">
            <a href="../index.php"
                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 focus:outline-none focus:bg-green-600">Main
                Page</a>
            <a href="Categories_Add.php"
                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">New
                Category</a>

        </div>

        <h1 class="text-3xl font-bold text-center mb-6">Categories</h1>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white rounded-lg shadow-lg">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-4 py-2">Category ID</th>
                        <th class="px-4 py-2">Category Name</th>
                        <th class="px-4 py-2">Product Count</th>
                        <th class="px-4 py-2">Category Description</th>
                        <th class="px-4 py-2">Sub Category Count</th>
                        <th class="px-4 py-2">Options</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600">
                    <?php foreach ($categoryCounts as $ligne): ?>
                        <tr class="hover:bg-gray-100 transition-colors duration-200">
                            <td class="border px-4 py-2"><?php echo $ligne["Category_ID"] ?></td>
                            <td class="border px-4 py-2"><?php echo $ligne["Category_Name"] ?></td>
                            <td class="border px-4 py-2"><?php echo $ligne["ProductCount"] ?></td>
                            <td class="border px-4 py-2"><?php echo $ligne["Category_Desc"] ?></td>
                            <td class="border px-4 py-2"><?php echo $ligne["SubCategoryCount"] ?></td>
                            <td class="border px-4 py-2">
                            <a href="Categories_Modify.php?id=<?php echo urlencode($ligne["Category_Name"]) ?>" class="edit-button text-blue-500 hover:underline mr-2"  >Edit</a>
                            <a href="Categories_Delete.php?id=<?php echo urlencode($ligne["Category_Name"]) ?>" class="delete-button text-red-500 hover:underline"  >Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>