<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../Logo.png" type="image/x-icon">
    
    <?php
    include_once ("../../DB_Connexion.php");
    $Error_Message = '' ;

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

            header("Location: ../User/User_Unauthorized.html");
            exit;
        }
    }
    ;

    if (!empty($_GET["id"])) {
        $SubCategory_ID = $_GET["id"];
        $SubCategoryQuery = "SELECT SubCategory_ID, SubCategory_Name, SubCategory_Desc, Category_ID FROM SubCategories WHERE SubCategory_ID = :SubCategory_ID";
        $pdostmt = $connexion->prepare($SubCategoryQuery);
        $pdostmt->execute(["SubCategory_ID" => $SubCategory_ID]);
        $ligne = $pdostmt->fetch(PDO::FETCH_ASSOC);
        $pdostmt->closeCursor();
    
        // Set the page title dynamically
        echo '<title>' . $ligne['SubCategory_Name'] . '</title>';
    } else {
        // Set a default title if $_GET["id"] is empty
        echo '<title>Edit Subcategory</title>';
    } ;
    

    if (!empty($_POST)) {
        $SubCategory_ID = $_GET["id"];
        $SubCategory_Name = $_POST["SubCategory_Name"];
        $Category_Name = $_POST["Category_Name"];
        $SubCategory_Desc = $_POST["SubCategory_Desc"];


        $queryCategory = "SELECT Category_ID FROM Categories WHERE Category_Name = :Category_Name";
        $pdostmtCategory = $connexion->prepare($queryCategory);
        $pdostmtCategory->execute(["Category_Name" => $Category_Name]);
        $Category_ID = $pdostmtCategory->fetchColumn();

        $SubCategory_Name_Lowercase = strtolower($SubCategory_Name);

        $QueryCheck = "SELECT SubCategory_ID FROM SubCategories WHERE LOWER(SubCategory_Name) = :SubCategory_Name_Lowercase AND SubCategory_ID != :SubCategory_ID";
        $pdostmtCheck = $connexion->prepare($QueryCheck);
        $pdostmtCheck->execute([
            "SubCategory_Name_Lowercase" => $SubCategory_Name_Lowercase,
            "SubCategory_ID" => $SubCategory_ID
        ]);




        $existingSubCategory = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

        
        if (!$existingSubCategory) {
            $UpdateQuery = "UPDATE SubCategories SET SubCategory_Name= :SubCategory_Name , Category_ID = :Category_ID , SubCategory_Desc = :SubCategory_Desc WHERE SubCategory_ID = :SubCategory_ID";
            $pdostmtUpdate = $connexion->prepare($UpdateQuery);
            $pdostmtUpdate->execute([
                "SubCategory_Name" => $SubCategory_Name,
                "Category_ID" => $Category_ID,
                "SubCategory_Desc" => $SubCategory_Desc,
                "SubCategory_ID" => $SubCategory_ID
            ]);
            $pdostmtUpdate->closeCursor();
            header("Location: SubCategories_List.php");
            exit();
        } else {
            $Error_Message = 'A Sub Category with that name already exists, try again!';
        }
    }
    ;

    




    ?>

</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <section class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <table class="w-full border-collapse">
            <form action="" method="POST" enctype="multipart/form-data">
                <tr class="border-b">
                    <td class="py-2 px-4">
                        <label for="SubCategory_ID" class="font-semibold">SubCategory ID</label>
                    </td>
                    <td class="py-2 px-4">
                        <span class="text-gray-700"><?php echo $ligne['SubCategory_ID'] ?></span>
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 px-4">
                        <label for="SubCategory_Name" class="font-semibold">Sub Category Name:</label>
                    </td>
                    <td class="py-2 px-4">
                        <input type="text" name="SubCategory_Name" class="w-full p-2 border border-gray-300 rounded" placeholder="Your Sub Category Name" value="<?php echo $ligne['SubCategory_Name'] ?>">
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 px-4">
                        <label for="Category_Name" class="font-semibold">Main Category:</label>
                    </td>
                    <td class="py-2 px-4">
                        <select name="Category_Name" class="w-full p-2 border border-gray-300 rounded" required>
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
                    </td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 px-4">
                        <label for="SubCategory_Desc" class="font-semibold">Sub Category Description:</label>
                    </td>
                    <td class="py-2 px-4">
                        <textarea name="SubCategory_Desc" class="w-full p-2 border border-gray-300 rounded" cols="30" rows="10" placeholder="Write a few lines describing the sub category"><?php echo $ligne['SubCategory_Desc'] ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="py-4 text-center">
                        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-700 transition duration-200">Save Changes</button>
                        <button type="reset" class="bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-700 transition duration-200 ml-2">Clear</button>
                        <a href="SubCategories_List.php" class="text-blue-500 hover:underline ml-2">List Sub Categories</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" class="py-2 text-center text-red-500">
                        <span><?php echo $Error_Message ?></span>
                    </td>
                </tr>
            </form>
        </table>
    </section>
</body>

</html>