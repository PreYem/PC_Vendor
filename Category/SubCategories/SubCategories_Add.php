<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sub Category</title>
    <link rel="icon" href="../../Logo.png" type="image/x-icon">
    <?php
    include_once ("../../DB_Connexion.php"); // Include database connection at the beginning
    
    session_start(); // Start or resume existing session
    
    // Check if user is logged in and has the appropriate role
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
        // User is not logged in, redirect to login page
        header("Location: ../../User/User_SignIn.php");
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
    // -----------------------------------------------------------------------------------------------------------------------------------------------------------------
    $Error_Message = '';

    if (!empty($_POST)) {
        $SubCategory_Name = $_POST["SubCategory_Name"];
        $Category_Name = $_POST["Category_Name"];
        $SubCategory_Desc = $_POST["SubCategory_Desc"];

        $queryCategory = "SELECT Category_ID FROM Categories WHERE Category_Name = :Category_Name";
        $pdostmtCategory = $connexion->prepare($queryCategory);
        $pdostmtCategory->execute(["Category_Name" => $Category_Name]);
        $Category_ID = $pdostmtCategory->fetchColumn();


        $SubCategory_Name_Lowercase = strtolower($SubCategory_Name);
        $QueryCheck = "SELECT SubCategory_ID FROM SubCategories WHERE LOWER(SubCategory_Name) = :SubCategory_Name_Lowercase";
        $pdostmtCheck = $connexion->prepare($QueryCheck);
        $pdostmtCheck->execute([
            "SubCategory_Name_Lowercase" => $SubCategory_Name_Lowercase,

        ]);
        $existingSubCategory = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);


        if (!$existingSubCategory) {
            $Query_Insert_SubCategory = "INSERT INTO SubCategories(SubCategory_Name, Category_ID, SubCategory_Desc) 
                                         VALUES (:SubCategory_Name, :Category_ID, :SubCategory_Desc)";

            $pdostmtInsertSubCategory = $connexion->prepare($Query_Insert_SubCategory);

            $pdostmtInsertSubCategory->execute([
                "SubCategory_Name" => $SubCategory_Name,
                "Category_ID" => $Category_ID,
                "SubCategory_Desc" => $SubCategory_Desc
            ]);
            $pdostmtInsertSubCategory->closeCursor();

            // Redirect after successful insertion
            header("Location: SubCategories_List.php");
            exit();
        } else {
            // Category name not found
            $Error_Message = 'Sub Category already exists, please check the spelling or choose a new name';
        }
    }
    ;

    ?>

</head>

<body class="bg-gray-200">
    <section class="max-w-4xl mx-auto py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-4">Add Sub Category</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="SubCategory_Name" class="block text-sm font-medium text-gray-700">Sub Category Name:</label>
                    <input type="text" name="SubCategory_Name" id="SubCategory_Name" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Your Sub Category Name" required>
                </div>
                <div class="mb-4">
                    <label for="Category_Name" class="block text-sm font-medium text-gray-700">Main Category:</label>
                    <select name="Category_Name" id="Category_Name" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" required>
                        <?php
                        $queryCategories = "SELECT Category_Name, Category_ID FROM Categories ORDER BY Category_ID";
                        $pdostmtCategories = $connexion->prepare($queryCategories);
                        $pdostmtCategories->execute();
                        $categories = $pdostmtCategories->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($categories as $category) {
                            echo "<option value=\"$category\">$category</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="SubCategory_Desc" class="block text-sm font-medium text-gray-700">Sub Category Description:</label>
                    <textarea name="SubCategory_Desc" id="SubCategory_Desc" rows="4" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Write a few lines describing the sub category"></textarea>
                </div>
                <div class="mb-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Add Sub Category</button>
                    <button type="reset" class="px-4 py-2 bg-gray-400 text-white rounded-md ml-2 hover:bg-gray-500">Clear</button>
                    <a href="SubCategories_List.php" class="ml-2 text-indigo-600 hover:underline">Close</a>
                </div>
                <?php if (!empty($Error_Message)) : ?>
                    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded-md">
                        <?php echo $Error_Message; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </section>
</body>

</html>