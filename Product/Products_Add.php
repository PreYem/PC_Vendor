<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>New Product</title>
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
    session_start(); // Start or resume existing session
    
    // Check if user is logged in and has the appropriate role
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
        // User is not logged in, redirect to login page
        header("Location: ../User/User_SignIn.php");
        exit; // Ensure script stops after redirection
    }

    // Retrieve the user's role from the database based on User_ID stored in session
    $userId = $_SESSION['User_ID'];
    $query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];

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
    ; ?>

    <?php

    $count = 0;
    $Error_Message = '';

    if (!empty($_POST)) {

        if (!empty($_POST["Product_Name"])) {
            $productName = $_POST["Product_Name"];


            $lowercaseProductName = strtolower($productName);


            $queryCheck = "SELECT Product_ID FROM Products WHERE LOWER(Product_Name) = :LowercaseProductName";
            $pdostmtCheck = $connexion->prepare($queryCheck);
            $pdostmtCheck->execute(["LowercaseProductName" => $lowercaseProductName]);

            $existingProduct = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$existingProduct) {

                $categoryName = $_POST['Category_Name'];
                $manufacturerName = $_POST['Manufacturer_Name'];
                $SubCategory_Name = $_POST['SubCategory_Name'];

                $queryCategory = "SELECT Category_ID FROM Categories WHERE Category_Name = :categoryName";
                $pdostmtCategory = $connexion->prepare($queryCategory);
                $pdostmtCategory->execute(['categoryName' => $categoryName]);
                $category = $pdostmtCategory->fetch(PDO::FETCH_ASSOC);
                $categoryId = $category['Category_ID'];

                $queryManufacturer = "SELECT Manufacturer_ID FROM Manufacturers WHERE Manufacturer_Name = :manufacturerName";
                $pdostmtManufacturer = $connexion->prepare($queryManufacturer);
                $pdostmtManufacturer->execute(['manufacturerName' => $manufacturerName]);
                $manufacturer = $pdostmtManufacturer->fetch(PDO::FETCH_ASSOC);
                $manufacturerId = $manufacturer['Manufacturer_ID'];

                $querySubCategory = "SELECT SubCategory_ID FROM SubCategories WHERE SubCategory_Name = :SubCategory_Name";
                $pdostmtSubCategory = $connexion->prepare($querySubCategory);
                $pdostmtSubCategory->execute(['SubCategory_Name' => $SubCategory_Name]);
                $subcategory = $pdostmtSubCategory->fetch(PDO::FETCH_ASSOC);
                $subcategoryId = $subcategory['SubCategory_ID'];



                $_POST['Category_ID'] = $categoryId;
                $_POST['Manufacturer_ID'] = $manufacturerId;
                $_POST['SubCategory_ID'] = $subcategoryId;


                if (!empty($_FILES['Product_Picture']['name'])) {
                    $uploadedFileName = $_FILES['Product_Picture']['name'];
                    $uploadedFileTmpName = $_FILES['Product_Picture']['tmp_name'];
                    $fileExtension = substr($uploadedFileName, -4);

                    $uploadFolder = 'Product_Pictures/';
                    $pattern = '/[^\w\-\.]/';
                    $cleanedProductName = preg_replace($pattern, '_', $productName);
                    $uniqueFileName = uniqid() . '_' . $cleanedProductName . $fileExtension;
                    $targetFilePath = $uploadFolder . $uniqueFileName;
                    if (move_uploaded_file($uploadedFileTmpName, $targetFilePath)) {
                        $productPicture = $targetFilePath;
                    } else {
                        $Error_Message = 'Failed to upload image. Please try again.';
                    }
                } else {

                    $defaultImagePath = 'Product_Pictures/Default_Product_Picture.jpg';
                    $productPicture = $defaultImagePath;
                }


                date_default_timezone_set('UTC');


                $currentDateTimeUTC = new DateTime();


                $currentDateTimeUTC->setTimezone(new DateTimeZone('Africa/Casablanca'));


                $detailedDateTimeForDB = $currentDateTimeUTC->format('Y-m-d H:i:s');

                $detailedDateTimeForDisplay = $currentDateTimeUTC->format('Y-m-d \a\t H:i:s');

                $insertProductQuery = "INSERT INTO Products (Product_Name, Category_ID, SubCategory_ID, Manufacturer_ID, Selling_Price, Buying_Price, Product_Quantity, Product_Picture, 
                       Product_Desc, Product_Visibility, Date_Created) 
                       VALUES (:Product_Name, :Category_ID, :SubCategory_ID, :Manufacturer_ID, :Selling_Price, :Buying_Price, :Product_Quantity, :Product_Picture, :Product_Desc, 
                       :Product_Visibility, :Date_Created)";

                $pdostmtProduct = $connexion->prepare($insertProductQuery);
                $pdostmtProduct->execute([
                    'Product_Name' => $_POST['Product_Name'],
                    'Category_ID' => $_POST['Category_ID'],
                    'SubCategory_ID' => $_POST['SubCategory_ID'],
                    'Manufacturer_ID' => $_POST['Manufacturer_ID'],
                    'Selling_Price' => $_POST['Selling_Price'],
                    'Buying_Price' => $_POST['Buying_Price'],
                    'Product_Quantity' => $_POST['Product_Quantity'],
                    'Product_Picture' => $productPicture, // Set the product picture column to the file path
                    'Product_Desc' => $_POST['Product_Desc'],
                    'Product_Visibility' => $_POST['Product_Visibility'],

                    'Date_Created' => $detailedDateTimeForDB
                ]);

                // Display the date with the "at" text in the desired format on the webpage
                echo "Date Created: " . $detailedDateTimeForDisplay;




                $productId = $connexion->lastInsertId();


                if (!empty($_POST['Specification_Name']) && !empty($_POST['Specification_Value'])) {

                    $specificationNames = $_POST['Specification_Name'];
                    $specificationValues = $_POST['Specification_Value'];


                    foreach ($specificationNames as $index => $specificationName) {
                        $specificationValue = $specificationValues[$index];


                        $insertSpecificationQuery = "INSERT INTO ProductSpecifications (Product_ID, Specification_Name, Specification_Value) 
                                              VALUES (:productId, :specificationName, :specificationValue)";


                        $pdostmtSpecification = $connexion->prepare($insertSpecificationQuery);
                        $pdostmtSpecification->execute([
                            'productId' => $productId,
                            'specificationName' => $specificationName,
                            'specificationValue' => $specificationValue
                        ]);
                    }
                }

                header("Location: ../index.php");



            } else {
                $Error_Message = 'A Product with that name already exists, try again!';

            }
        }
    }
    ?>





</head>

<body class="bg-gray-100 p-8">
    <div class="sticky-nav bg-white shadow-md p-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-bold">Add Product</h1>
            <div>
                <a href="../index.php"
                    class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md inline-block">Main Page</a>
                <a href="Products_List.php"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block ml-4">List
                    Products</a>
            </div>
        </div>
    </div>



    <form action="" method="POST" enctype="multipart/form-data"
        class="max-w-md mx-auto bg-white p-8 rounded-md shadow-lg">

        <div class="mb-4">
            <label for="Product_Name" class="block text-sm font-medium text-gray-700">Product Name:</label>
            <input type="text" name="Product_Name" placeholder="Example: RTX 2060" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="mb-4">
            <label for="Category_Name" class="block text-sm font-medium text-gray-700">Category:</label>
            <select name="Category_Name" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php
                $queryCategories = "SELECT Category_Name FROM Categories ORDER BY Category_ID";
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
            <label for="SubCategory_Name" class="block text-sm font-medium text-gray-700">Sub Category:</label>
            <select name="SubCategory_Name" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php
                $querySubCategories = "SELECT SubCategory_Name FROM SubCategories ORDER BY SubCategory_ID";
                $pdostmtSubCategories = $connexion->prepare($querySubCategories);
                $pdostmtSubCategories->execute();
                $SubCategories = $pdostmtSubCategories->fetchAll(PDO::FETCH_COLUMN);

                foreach ($SubCategories as $SubCategory) {
                    echo "<option value=\"$SubCategory\">$SubCategory</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="Manufacturer_Name" class="block text-sm font-medium text-gray-700">Manufacturer:</label>
            <select name="Manufacturer_Name" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <?php
                $queryManufacturers = "SELECT Manufacturer_Name FROM Manufacturers ORDER BY Manufacturer_ID";
                $pdostmtManufacturers = $connexion->prepare($queryManufacturers);
                $pdostmtManufacturers->execute();
                $Manufacturers = $pdostmtManufacturers->fetchAll(PDO::FETCH_COLUMN);

                foreach ($Manufacturers as $Manufacturer) {
                    echo "<option value=\"$Manufacturer\">$Manufacturer</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="Buying_Price" class="block text-sm font-medium text-gray-700">Buying Price:</label>
            <input type="number" name="Buying_Price" placeholder="In Dhs" value="0" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="mb-4">
            <label for="Selling_Price" class="block text-sm font-medium text-gray-700">Selling Price:</label>
            <input type="number" name="Selling_Price" placeholder="In Dhs" value="0" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="mb-4">
            <label for="Product_Quantity" class="block text-sm font-medium text-gray-700">Quantity:</label>
            <input type="number" name="Product_Quantity" placeholder="How many in stock" value="0" required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="mb-4">
            <label for="Product_Picture" class="block text-sm font-medium text-gray-700">Product Picture:</label>
            <input type="file" name="Product_Picture" accept="image/*"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
        </div>

        <div class="mb-4">
            <label for="Product_Desc" class="block text-sm font-medium text-gray-700">Product Description:</label>
            <textarea name="Product_Desc" id="Product_Desc" cols="30" rows="5"
                placeholder="Write a brief description about this product"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
        </div>

        <div class="mb-4">
            <label for="Product_Visibility" class="block text-sm font-medium text-gray-700">Visibility:</label>
            <select name="Product_Visibility"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <option value="Visible">Visible</option>
                <option value="Invisible">Invisible</option>
            </select>
        </div>

        <div class="mb-4">
            <h2 class="text-lg font-medium mb-2">Specifications:</h2>
            <div id="specifications"></div>
            <button type="button" class="mt-2 bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-md"
                onclick="addSpecification()">Add Specification</button>
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block">Add
            Product</button>

        <span class="text-red-500 block mt-4">
            <?php echo $Error_Message ?>
        </span>

    </form>



</body>

<script>
    function addSpecification() {
        const specificationsDiv = document.getElementById('specifications');
        const newSpecDiv = document.createElement('div');
        newSpecDiv.innerHTML = `
                <label for="Specification_Name[]" class="block text-sm font-medium text-gray-700">Specification Name:</label>
                <input type="text" name="Specification_Name[]" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <label for="Specification_Value[]" class="block text-sm font-medium text-gray-700">Specification Value:</label>
                <input type="text" name="Specification_Value[]" required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                <button type="button" onclick="removeSpecification(this)"
                    class="mt-2 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-md inline-block">Remove</button>
            `;
        specificationsDiv.appendChild(newSpecDiv);
    }

    function removeSpecification(button) {
        const specDiv = button.parentElement;
        specDiv.remove();
    }
</script>

</html>