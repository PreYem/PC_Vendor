<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Edit Category</title>
    <link href="../output.css" rel="stylesheet">
    <style>
        input,
        textarea {
            border: 1px black solid;
        }
    </style>

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
    };

    $ligne = [];

    if (!empty($_GET["id"])) {
        $categoryName = urldecode($_GET["id"]);
        $query = "SELECT Category_ID, Category_Name, Category_Desc FROM Categories WHERE Category_Name = :Category_Name";
        $pdostmt = $connexion->prepare($query);
        $pdostmt->execute(["Category_Name" => $categoryName]);
        $ligne = $pdostmt->fetch(PDO::FETCH_ASSOC);
        $pdostmt->closeCursor();
    }

    $Error_Message = '';
    if (!empty($_POST)) {
        $categoryName = $_POST["Category_Name"];
        $categoryDesc = $_POST["Category_Desc"];
        $categoryId = $_POST["Category_ID"];


        $lowercaseCategory = strtolower($categoryName);


        $queryCheck = "SELECT Category_ID FROM Categories WHERE LOWER(Category_Name) = :LowercaseCategory AND Category_ID != :CategoryId";
        $pdostmtCheck = $connexion->prepare($queryCheck);
        $pdostmtCheck->execute([
            "LowercaseCategory" => $lowercaseCategory,
            "CategoryId" => $categoryId
        ]);

        $existingCategory = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existingCategory) {

            $queryUpdate = "UPDATE Categories SET Category_Name=:Category_Name, Category_Desc=:Category_Desc WHERE Category_ID=:Category_ID";
            $pdostmtUpdate = $connexion->prepare($queryUpdate);

            $pdostmtUpdate->execute([
                "Category_Name" => $categoryName,
                "Category_Desc" => $categoryDesc,
                "Category_ID" => $categoryId,
            ]);

            $pdostmtUpdate->closeCursor();
            header("Location: Categories_List.php");
            exit();
        } else {
            $Error_Message = 'A category with that name already exists, try again!';

        }
    }
    ?>

</head>

<body>

    <section class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">

        <form action="" method="POST" enctype="multipart/form-data">

            <!-- Category ID (hidden) -->
            <input type="hidden" name="Category_ID" value="<?php echo $ligne["Category_ID"]; ?>">

            <!-- Category Name -->
            <div class="mb-4">
                <label for="Category_Name" class="block text-sm font-bold text-gray-700">Category Name:</label>
                <input type="text" name="Category_Name" value="<?php echo $ligne["Category_Name"]; ?>"
                    placeholder="Your Category Name"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Category Description -->
            <div class="mb-4">
                <label for="Category_Desc" class="block text-sm font-bold text-gray-700">Category Description:</label>
                <textarea name="Category_Desc" cols="30" rows="5"
                    placeholder="Write a few lines describing the category"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-blue-500 focus:border-blue-500"><?php echo $ligne["Category_Desc"]; ?></textarea>
            </div>

            <!-- Save Changes Button -->
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                Save Changes
            </button>

            <!-- Delete Button -->
            <a href="Categories_Delete.php?id=<?php echo urlencode($ligne["Category_Name"]) ?>"
                class="inline-block mt-2 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-red-500">
                Delete
            </a>

            <!-- Error Message -->
            <div class="text-red-600 mt-2">
                <?php echo $Error_Message ?>
            </div>

            <!-- Close Button (non-submit) -->
            <a href="Categories_List.php" onclick="closeModifyModal()"
                class="inline-block mt-4 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                Close
            </a>

        </form>

    </section>


</body>

</html>