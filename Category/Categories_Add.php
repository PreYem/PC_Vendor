<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>New Category</title>
    <link href="../output.css" rel="stylesheet">
    <style>
        .Btn {
            background-color: blue;
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
    }
    ;

    $Error_Message = '';

    if (!empty($_POST["Category_Name"]) && !empty($_POST["Category_Desc"])) {
        $categoryName = $_POST["Category_Name"];
        $categoryDesc = $_POST["Category_Desc"];


        $lowercaseCategory = strtolower($categoryName);


        $queryCheck = "SELECT Category_ID FROM Categories WHERE LOWER(Category_Name) = :LowercaseCategory";
        $pdostmtCheck = $connexion->prepare($queryCheck);
        $pdostmtCheck->execute(["LowercaseCategory" => $lowercaseCategory]);

        $existingCategory = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existingCategory) {
            $queryInsert = "INSERT INTO Categories (Category_Name, Category_Desc) VALUES (:Category_Name, :Category_Desc)";
            $pdostmtInsert = $connexion->prepare($queryInsert);

            $pdostmtInsert->execute([
                "Category_Name" => $categoryName,
                "Category_Desc" => $categoryDesc,
            ]);

            $pdostmtInsert->closeCursor();
            header("Location: Categories_List.php");
            exit();
        } else {
            $Error_Message = 'A category with that name already exists, try again!';

        }
    }
    ?>
</head>

<body>

    <div>
        <section class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-4">Add Category</h1>

            <form action="" method="POST" enctype="multipart/form-data">

                <div class="mb-4">
                    <label for="Category_Name" class="block text-sm font-medium text-gray-700">Category Name:</label>
                    <input type="text" name="Category_Name"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter Category Name" required>
                </div>

                <div class="mb-4">
                    <label for="Category_Desc" class="block text-sm font-medium text-gray-700">Category
                        Description:</label>
                    <textarea name="Category_Desc"
                        class="mt-1 p-2 block w-full border border-gray-300 rounded-md h-32 resize-none focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Enter Category Description" required></textarea>
                </div>

                <div class="flex items-center space-x-4">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">
                        Add Category
                    </button>
                    <button type="reset"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:bg-gray-400">
                        Clear
                    </button>
                    <a href="Categories_List.php"
                        class="text-blue-500 hover:underline focus:outline-none focus:text-blue-600">Category List</a>
                </div>

                <div class="mt-4 text-red-600">
                    <?php echo isset($Error_Message) ? $Error_Message : ''; ?>
                </div>

            </form>
        </section>
    </div>



</body>

</html>