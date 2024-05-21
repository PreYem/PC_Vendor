<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../Logo.png" type="image/x-icon">
    <title>Edit Sub Category</title>
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
        $SubCategoryQuery = "SELECT SubCategory_ID , SubCategory_Name , SubCategory_Desc , Category_ID FROM SubCategories WHERE SubCategory_ID = :SubCategory_ID";
        $pdostmt = $connexion->prepare($SubCategoryQuery);
        $pdostmt->execute(["SubCategory_ID" => $SubCategory_ID]);
        $ligne = $pdostmt->fetch(PDO::FETCH_ASSOC);
        $pdostmt->closeCursor();
    }


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

<body>
    <section>
        <table style="border : 2px black solid">
            <form action="" method="POST" enctype="multipart/form-data">
                <tr>
                    <td>
                        <label for="SubCategory_ID">SubCategory ID</label>
                    </td>
                    <td>
                        <span>
                            <?php echo $ligne['SubCategory_ID'] ?>
                        </span>
                    </td>

                </tr>
                <tr>
                    <td><label for="SubCategory_Name">Sub Category Name:</label></td>
                    <td><input type="text" name="SubCategory_Name" placeholder="Your Sub Category Name"
                            value="<?php echo $ligne['SubCategory_Name'] ?>"></td>
                </tr>
                <tr>
                    <td><label for="Category_Name">Main Category:</label></td>
                    <td>
                        <select name="Category_Name" required>
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
                <tr>
                    <td><label for="SubCategory_Desc">Sub Category Description:</label></td>
                    <td><textarea name="SubCategory_Desc" cols="30" rows="10"
                            placeholder="Write a few lines describing the sub category"><?php echo $ligne['SubCategory_Desc'] ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <button type="submit" class="Btn">Save Changes</button>
                        <button type="reset" class="Btn">Clear</button>
                        <a href="SubCategories_List.php">List Sub Categories</a>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <span><?php echo $Error_Message ?></span>
                    </td>
                </tr>
            </form>
        </table>
    </section>
</body> 

</html>