<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Modify Product</title>
    <link href="../output.css" rel="stylesheet">
    <style>
        input {
            border: 1px black solid;
        }

        body {
            background-color: grey;
        }
    </style>

    <?php

    include_once ("../DB_Connexion.php");

    session_start();


    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

        header("Location: ../User/User_SignIn.php");
        exit;
    }

    // Retrieve the user's role from the database based on User_ID stored in session
    $userId = $_SESSION['User_ID'];
    $query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];

        // Check if the user has the required role (Owner or Admin) to access this page
        if ($userRole !== 'Owner' && $userRole !== 'Admin') {
            // User does not have sufficient permissions, redirect to unauthorized page
            header("Location: ../User/User_Unauthorized.html");
            exit; // Ensure script stops after redirection
        }
    }
    ;

    $Error_Message = '';

    if (!empty($_POST)) {
        $productId = $_POST['Product_ID'];
        $productName = $_POST['Product_Name'];
        $categoryName = $_POST['Category_Name'];
        $SubCategory_Name = $_POST['SubCategory_Name'];
        $manufacturerName = $_POST['Manufacturer_Name'];
        $buyingPrice = $_POST['Buying_Price'];
        $sellingPrice = $_POST['Selling_Price'];
        $quantity = $_POST['Product_Quantity'];
        $productDesc = $_POST['Product_Desc'];
        $visibility = $_POST['Product_Visibility'];


        $querySubCategory = "SELECT SubCategory_ID FROM SubCategories WHERE SubCategory_Name = :SubCategory_Name";
        $pdostmtCategory = $connexion->prepare($querySubCategory);
        $pdostmtCategory->execute(['SubCategory_Name' => $SubCategory_Name]);
        $SubCategory_ID = $pdostmtCategory->fetchColumn();


        // Retrieve category ID based on name
        $queryCategory = "SELECT Category_ID FROM Categories WHERE Category_Name = :categoryName";
        $pdostmtCategory = $connexion->prepare($queryCategory);
        $pdostmtCategory->execute(['categoryName' => $categoryName]);
        $categoryId = $pdostmtCategory->fetchColumn();

        // Retrieve manufacturer ID based on name
        $queryManufacturer = "SELECT Manufacturer_ID FROM Manufacturers WHERE Manufacturer_Name = :manufacturerName";
        $pdostmtManufacturer = $connexion->prepare($queryManufacturer);
        $pdostmtManufacturer->execute(['manufacturerName' => $manufacturerName]);
        $manufacturerId = $pdostmtManufacturer->fetchColumn();

        // Check if a product with the same name already exists
        $queryCheck = "SELECT Product_ID FROM Products WHERE LOWER(Product_Name) = :productName AND Product_ID != :productId";
        $pdostmtCheck = $connexion->prepare($queryCheck);
        $pdostmtCheck->execute([
            "productName" => strtolower($productName),
            "productId" => $productId
        ]);
        $existingProduct = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existingProduct) {
            // Update the product if no product with the same name exists
            $queryUpdate = "UPDATE Products 
            SET Product_Name = :productName,
                Category_ID = :categoryId,
                SubCategory_ID = :SubCategory_ID, 
                Manufacturer_ID = :manufacturerId,
                Selling_Price = :sellingPrice,
                Buying_Price = :buyingPrice,
                Product_Quantity = :quantity,
                Product_Desc = :productDesc,
                Product_Visibility = :visibility 
            WHERE Product_ID = :productId";

            $pdostmtUpdate = $connexion->prepare($queryUpdate);
            $pdostmtUpdate->execute([
                "productName" => $productName,
                "categoryId" => $categoryId,
                "SubCategory_ID" => $SubCategory_ID, // Corrected parameter name without space
                "manufacturerId" => $manufacturerId,
                "quantity" => $quantity,
                "productDesc" => $productDesc,
                "visibility" => $visibility,
                "productId" => $productId,
                "buyingPrice" => $buyingPrice,
                "sellingPrice" => $sellingPrice
            ]);

            if ($_FILES['New_Product_Picture']['tmp_name']) {
                $uploadDirectory = 'Product_Pictures/'; // Specify the directory where files will be uploaded
                $uploadedFilePath = $uploadDirectory . $_FILES['New_Product_Picture']['name'];

                if (move_uploaded_file($_FILES['New_Product_Picture']['tmp_name'], $uploadedFilePath)) {
                    // File uploaded successfully, now update the database with the file path
                    $queryUpdatePicture = "UPDATE Products SET Product_Picture = :newImagePath WHERE Product_ID = :productId";
                    $pdostmtUpdatePicture = $connexion->prepare($queryUpdatePicture);
                    $pdostmtUpdatePicture->execute([
                        "newImagePath" => $uploadedFilePath,
                        "productId" => $productId
                    ]);
                } else {

                    echo "File upload failed.";
                }
            }



            $deletedSpecs = isset($_POST['deletedSpecs']) ? json_decode($_POST['deletedSpecs']) : [];
            if (!empty($deletedSpecs)) {
                $placeholders = implode(',', array_fill(0, count($deletedSpecs), '?'));
                $queryRemoveSpecs = "DELETE FROM ProductSpecifications WHERE Product_ID = ? AND Specification_Name IN ($placeholders)";
                $params = array_merge([$productId], $deletedSpecs);
                $pdostmtRemoveSpecs = $connexion->prepare($queryRemoveSpecs);
                $pdostmtRemoveSpecs->execute($params);
            }



            // Update or insert product specifications
            if (isset($_POST['Specification_Name'])) {
                foreach ($_POST['Specification_Name'] as $index => $specName) {
                    $specValue = $_POST['Specification_Value'][$index];
                    // Check if the specification already exists for the product
                    $querySpecExists = "SELECT COUNT(*) FROM ProductSpecifications WHERE Product_ID = :productId AND Specification_Name = :specName";
                    $pdostmtSpecExists = $connexion->prepare($querySpecExists);
                    $pdostmtSpecExists->execute([
                        "productId" => $productId,
                        "specName" => $specName
                    ]);
                    $specExists = $pdostmtSpecExists->fetchColumn();

                    if ($specExists) {
                        // Update the existing specification
                        $queryUpdateSpec = "UPDATE ProductSpecifications SET Specification_Value = :specValue WHERE Product_ID = :productId AND Specification_Name = :specName";
                        $pdostmtUpdateSpec = $connexion->prepare($queryUpdateSpec);
                        $pdostmtUpdateSpec->execute([
                            "specValue" => $specValue,
                            "productId" => $productId,
                            "specName" => $specName
                        ]);
                    } else {
                        // Insert the new specification
                        $queryInsertSpec = "INSERT INTO ProductSpecifications (Product_ID, Specification_Name, Specification_Value) VALUES (:productId, :specName, :specValue)";
                        $pdostmtInsertSpec = $connexion->prepare($queryInsertSpec);
                        $pdostmtInsertSpec->execute([
                            "productId" => $productId,
                            "specName" => $specName,
                            "specValue" => $specValue
                        ]);
                    }
                }
            }

            $pdostmtUpdate->closeCursor();
            header("Location: ../index.php");
            exit();
        } else {
            // Display error message if a product with the same name already exists
            $Error_Message = 'A product with that name already exists, try again!';
        }
    }


    ?>






</head>

<body>
    <h1>Modify Product</h1>
    <a href="Products_List.php">List Products</a>
    <?php


    if (!empty($_GET["id"])) {
        $productId = $_GET["id"];

        // Retrieve product information based on the provided product ID
        $queryProduct = "SELECT * FROM Products WHERE Product_ID = :productId";
        $pdostmtProduct = $connexion->prepare($queryProduct);
        $pdostmtProduct->execute(["productId" => $productId]);
        $product = $pdostmtProduct->fetch(PDO::FETCH_ASSOC);

        // Retrieve category name for the product
        $queryCategory = "SELECT Category_Name FROM Categories WHERE Category_ID = :categoryId";
        $pdostmtCategory = $connexion->prepare($queryCategory);
        $pdostmtCategory->execute(["categoryId" => $product['Category_ID']]);
        $category = $pdostmtCategory->fetch(PDO::FETCH_ASSOC);

        $CurrentCategoryName = $category['Category_Name'];


        $querySub = "SELECT SubCategory_ID FROM Products WHERE Product_ID = :productId";
        $pdostmtSub = $connexion->prepare($querySub);
        $pdostmtSub->execute(["productId" => $product['Product_ID']]);
        $sub = $pdostmtSub->fetch(PDO::FETCH_ASSOC);
        $CurrentSubCat_ID = $sub['SubCategory_ID'];

        $querySubName = "SELECT SubCategory_Name FROM SubCategories WHERE SubCategory_ID  = :CurrentSubCat_ID";
        $pdostmtSubName = $connexion->prepare($querySubName);
        $pdostmtSubName->execute(["CurrentSubCat_ID" => $CurrentSubCat_ID]);
        $subName = $pdostmtSubName->fetch(PDO::FETCH_ASSOC);
        $CurrentSubCat_Name = $subName['SubCategory_Name'];

        $CurrentManufacturer_ID = "SELECT Manufacturer_ID FROM Products WHERE Product_ID = :productId";
        $pdostmtManufacturer_ID = $connexion->prepare($CurrentManufacturer_ID);
        $pdostmtManufacturer_ID->execute(["productId" => $product['Product_ID']]);
        $Manufacturer_ID = $pdostmtManufacturer_ID->fetch(PDO::FETCH_ASSOC);
        $CurrentManufacturer_ID = $Manufacturer_ID['Manufacturer_ID'];

        $CurrentManufacturer_Name = "SELECT Manufacturer_Name FROM Manufacturers WHERE Manufacturer_ID = :CurrentManufacturer_ID";
        $pdostmtManufacturer_Name = $connexion->prepare($CurrentManufacturer_Name);
        $pdostmtManufacturer_Name->execute(["CurrentManufacturer_ID" => $CurrentManufacturer_ID]);
        $Manufacturer_Name = $pdostmtManufacturer_Name->fetch(PDO::FETCH_ASSOC);

        $CurrentManufacturer_Name = $Manufacturer_Name['Manufacturer_Name'];



        // Retrieve specifications for the product
        $querySpecifications = "SELECT * FROM ProductSpecifications WHERE Product_ID = :productId";
        $pdostmtSpecifications = $connexion->prepare($querySpecifications);
        $pdostmtSpecifications->execute(["productId" => $productId]);
        $specifications = $pdostmtSpecifications->fetchAll(PDO::FETCH_ASSOC);



    }
    ?>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="Product_ID" value="<?php echo $product['Product_ID']; ?>">
        <table>
            <span> </span>
            <tr>
                <td><label for="Product_Name">Product Name:</label></td>
                <td><input type="text" name="Product_Name" value="<?php echo $product['Product_Name']; ?>">
                </td>
            </tr>
            <tr>
                <td><label for="Category_Name">Category:</label></td>
                <td>

                    <select name="Category_Name" required>
                        <?php
                        $queryCategories = "SELECT Category_Name FROM Categories";
                        $pdostmtCategories = $connexion->prepare($queryCategories);
                        $pdostmtCategories->execute();
                        $categories = $pdostmtCategories->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($categories as $category) {
                            $selected = ''; // Initially, no option is selected
                            if ($category == $CurrentCategoryName) {
                                $selected = 'selected'; // If current option is "ABC", mark it as selected
                            }
                            echo "<option value=\"$category\" $selected>$category</option>";
                        }
                        ?>
                    </select>



                </td>

            </tr>

            <tr>
                <td><label for="SubCategory_Name">Sub Category</label></td>
                <td>
                    <select name="SubCategory_Name" required>
                        <?php
                        $querySubCategories = "SELECT SubCategory_Name FROM SubCategories";
                        $pdostmtSubCategories = $connexion->prepare($querySubCategories);
                        $pdostmtSubCategories->execute();
                        $SubCategories = $pdostmtSubCategories->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($SubCategories as $SubCategory) {
                            $selected = '';
                            if ($SubCategory == $CurrentSubCat_Name) {
                                $selected = 'selected';
                            }
                            echo "<option value=\"$SubCategory\" $selected>$SubCategory</option>";
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="Manufacturer_Name">Manufacturer:</label></td>
                <td>
                    <select name="Manufacturer_Name">
                        <?php

                        $queryManufacturers = "SELECT Manufacturer_Name FROM Manufacturers";
                        $pdostmtManufacturers = $connexion->prepare($queryManufacturers);
                        $pdostmtManufacturers->execute();
                        $Manufacturers = $pdostmtManufacturers->fetchAll(PDO::FETCH_COLUMN);

                        foreach ($Manufacturers as $Manufacturer) {
                            $selected = '';
                            if ($Manufacturer == $CurrentManufacturer_Name) {
                                $selected = 'selected';
                            }
                            echo "<option value=\"$Manufacturer\" $selected>$Manufacturer</option>";
                        }
                        ?>
                    </select>

                </td>
            </tr>



            <tr>
                <td><label for="Buying_Price">Buying Price:</label></td>
                <td><input type="number" name="Buying_Price" value="<?php echo $product['Buying_Price']; ?>"></td>
            </tr>
            <tr>
                <td><label for="Selling_Price">Selling Price:</label></td>
                <td><input type="number" name="Selling_Price" value="<?php echo $product['Selling_Price']; ?>"></td>
            </tr>

            <tr>
                <td><label for="Product_Quantity">Quantity:</label></td>
                <td><input type="number" name="Product_Quantity" value="<?php echo $product['Product_Quantity']; ?>">
                </td>
            </tr>
            <tr>
                <td><label for="Product_Picture">Current Picture:</label></td>
                <td>
                    <?php
                    if ($product['Product_Picture']) {
                        // Display the current picture if available
                        echo '<img src="' . $product['Product_Picture'] . '" alt="Current Product Image" style="max-width: 200px; max-height: 200px;">';
                    } else {
                        echo 'No Image Found in Database';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td><label for="New_Product_Picture">New Picture:</label></td>
                <td><input type="file" name="New_Product_Picture" accept="image/*"></td>
            </tr>
            <tr>
                <td><label for="Product_Desc">Product Description:</label></td>
                <td><textarea name="Product_Desc" rows="4" cols="50"><?php echo $product['Product_Desc']; ?></textarea>
                </td>
            </tr>
            <tr>
                <td><label for="Product_Visibility">Visibility:</label></td>
                <td>
                    <select name="Product_Visibility">
                        <option value="Visible" <?php echo ($product['Product_Visibility'] === 'Visible') ? 'selected' : ''; ?>>
                            Visible</option>
                        <option value="Invisible" <?php echo ($product['Product_Visibility'] === 'Invisible') ? 'selected' : ''; ?>>
                            Invisible</option>
                    </select>
                </td>
            </tr>

        </table>
        <h2>Specifications:</h2>
        <table id="specifications">
            <?php foreach ($specifications as $specification): ?>
                <tr>
                    <td><label for="Specification_Name">Specification Name:</label></td>
                    <td><input type="text" name="Specification_Name[]"
                            value="<?php echo $specification['Specification_Name']; ?>"></td>
                    <td><label for="Specification_Value">Specification Value:</label></td>
                    <td><input type="text" name="Specification_Value[]"
                            value="<?php echo $specification['Specification_Value']; ?>"></td>
                    <td><button type="button" onclick="removeSpecification(this)">Remove</button></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="button" style="border: 1px solid black; background-color: purple" onclick="addSpecification()">Add
            Specification</button>
        <br><br>
        <button type="submit">Update Product</button>
        <input type="hidden" id="deletedSpecs" name="deletedSpecs" value="">

    </form>
    <span>
        <?php echo $Error_Message ?>
    </span>


    <script>
        function addSpecification() {
            const table = document.getElementById('specifications');
            const newRow = table.insertRow();
            newRow.innerHTML = `
            <td><label for="Specification_Name[]">Specification Name:</label></td>
            <td><input type="text" name="Specification_Name[]" required></td>
            <td><label for="Specification_Value[]">Specification Value:</label></td>
            <td><input type="text" name="Specification_Value[]" required></td>
            <td><button type="button" onclick="removeSpecification(this)">Remove</button></td>
        `;
        }

        function removeSpecification(button) {
            const specRow = button.parentElement.parentElement;
            const specName = specRow.querySelector('input[name="Specification_Name[]"]').value;
            const deletedSpecsInput = document.getElementById('deletedSpecs');
            const deletedSpecs = deletedSpecsInput.value ? JSON.parse(deletedSpecsInput.value) : [];
            deletedSpecs.push(specName);
            deletedSpecsInput.value = JSON.stringify(deletedSpecs);
            specRow.remove();
        }
    </script>



</body>

</html>