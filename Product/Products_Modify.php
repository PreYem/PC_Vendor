<?php
include_once ("../DB_Connexion.php");
if (!empty($_GET["id"])) {
    $Product_ID = $_GET["id"];
    $Title_Name = "SELECT Product_Name FROM Products WHERE Product_ID = :Product_ID ";
    $pdoTitle_Name = $connexion->prepare($Title_Name);
    $pdoTitle_Name->execute(['Product_ID' => $Product_ID]);
    $Title_Name = $pdoTitle_Name->fetch(PDO::FETCH_ASSOC);
    $Title_Name = strlen($Title_Name['Product_Name']) > 10 ? substr($Title_Name['Product_Name'], 0, 20) . "..." : $Title_Name['Product_Name'];
} else {
    header("Location: ../.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title><?php echo $Title_Name . ' ' ?>| Edit | PC Vendor</title>



    <?php
    session_start();
    include_once ("../DB_Connexion.php");


    if (isset($_SESSION['User_ID'])) {
        $User_ID = $_SESSION['User_ID'];
        $Users = "SELECT User_ID, User_Role, User_FirstName, User_LastName FROM Users WHERE User_ID = :User_ID";
        $pdoUsers = $connexion->prepare($Users);
        $pdoUsers->execute([':User_ID' => $User_ID]);
        $User = $pdoUsers->fetch(PDO::FETCH_ASSOC);
        $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];
        $User_Role = $User['User_Role'];
        $Current_User_Role = $User['User_Role'];
    } else {
        $User_Role = 'Client';
    }

    if ($User_Role === 'Client') {
        header("Location: ../.");
        exit;
    }


    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_SESSION['User_ID'])) {
        $Shopping_Cart = "SELECT CartItem_ID FROM ShoppingCart WHERE User_ID = :User_ID";
        $pdostmt_shopping = $connexion->prepare($Shopping_Cart);
        $pdostmt_shopping->execute([':User_ID' => $User_ID]);
        $Shopping_Cart = $pdostmt_shopping->fetchAll(PDO::FETCH_ASSOC);
        $Cart_Count = $pdostmt_shopping->rowCount();
    }

    $Error_Message = '';

    if (!empty($_GET["id"])) {
        $Product_ID = $_GET["id"];

        $Product_Display = "SELECT Products.*, Categories.Category_Name, SubCategories.SubCategory_Name , Manufacturers.Manufacturer_Name
            FROM Products
            INNER JOIN Categories ON Products.Category_ID = Categories.Category_ID
            INNER JOIN SubCategories ON Products.SubCategory_ID = SubCategories.SubCategory_ID
            INNER JOIN Manufacturers ON Products.Manufacturer_ID = Manufacturers.Manufacturer_ID
            WHERE Products.Product_ID = :Product_ID
        ";
        $pdo_Product_Display = $connexion->prepare($Product_Display);
        $pdo_Product_Display->execute([':Product_ID' => $Product_ID]);
        $Product_Display = $pdo_Product_Display->fetch(PDO::FETCH_ASSOC);

        $Specifications_Query = "SELECT * FROM ProductSpecifications WHERE Product_ID = :Product_ID";
        $pdoSpecifications = $connexion->prepare($Specifications_Query);
        $pdoSpecifications->execute([':Product_ID' => $Product_ID]);
        $Specifications = $pdoSpecifications->fetchAll(PDO::FETCH_ASSOC);



    } else {
        header("Location: ../.");
        exit;
    }

    $querySpecifications = "SELECT * FROM ProductSpecifications WHERE Product_ID = :Product_ID";
    $pdostmtSpecifications = $connexion->prepare($querySpecifications);
    $pdostmtSpecifications->execute(["Product_ID" => $Product_ID]);
    $specifications = $pdostmtSpecifications->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($_POST)) {
        // 11 Total values to be sent to the database
    
        $Product_ID = $_GET["id"];
        $Product_Name = $_POST['Product_Name']; // Product_Name
        $Category_Name = $_POST['Category_Name']; // 
        $SubCategory_Name = $_POST['SubCategory_Name'];
        $Manufacturer_Name = $_POST['Manufacturer_Name'];
        $Buying_Price = $_POST['Buying_Price']; // Buying_Price
        $Selling_Price = $_POST['Selling_Price']; // Selling_Price
        $Product_Quantity = $_POST['Product_Quantity']; // Product_Quantity
        $Product_Desc = $_POST['Product_Desc']; // Product_Desc
        $Product_Visibility = $_POST['Product_Visibility']; // Product_Visibility
    
        $Q_Category = "SELECT Category_ID FROM Categories WHERE Category_Name = :Category_Name";
        $pdoQ_Category = $connexion->prepare($Q_Category);
        $pdoQ_Category->execute([':Category_Name' => $Category_Name]);
        $Category_ID = $pdoQ_Category->fetchColumn(); // Category_ID
    
        $Q_SubCategory = "SELECT SubCategory_ID FROM SubCategories WHERE SubCategory_Name = :SubCategory_Name";
        $pdoQ_SubCategory = $connexion->prepare($Q_SubCategory);
        $pdoQ_SubCategory->execute([':SubCategory_Name' => $SubCategory_Name]);
        $SubCategory_ID = $pdoQ_SubCategory->fetchColumn(); // SubCategory_ID
    
        $Q_Manufacturer = "SELECT Manufacturer_ID FROM Manufacturers WHERE Manufacturer_Name = :Manufacturer_Name";
        $pdoQ_Manufacturer = $connexion->prepare($Q_Manufacturer);
        $pdoQ_Manufacturer->execute([':Manufacturer_Name' => $Manufacturer_Name]);
        $Manufacturer_ID = $pdoQ_Manufacturer->fetchColumn(); // Manufacturer_ID
    

        $Product_Name_Lower = strtolower($Product_Name);

        $Product_Name_Unique = "SELECT Product_Name FROM Products 
                                WHERE LOWER(Product_Name) = :Product_Name_Lower AND Product_ID != :Product_ID";
        $pdoProduct_Name_Unique = $connexion->prepare($Product_Name_Unique);
        $pdoProduct_Name_Unique->execute([':Product_Name_Lower' => $Product_Name_Lower, ':Product_ID' => $Product_ID]);
        $Product_Name_Unique = $pdoProduct_Name_Unique->fetch(PDO::FETCH_ASSOC);


        if (!$Product_Name_Unique) {
            // Updating picture if it's been inserted, otherwise it stays the same as how it is in the databse
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
                    $Product_Picture = $targetFilePath;
                    $Update_Picture = "UPDATE Products SET Product_Picture = :Product_Picture WHERE Product_ID = :Product_ID";
                    $pdoUpdate_Picture = $connexion->prepare($Update_Picture);
                    $pdoUpdate_Picture->execute([
                        ':Product_Picture' => $Product_Picture,
                        ':Product_ID' => $Product_ID
                    ]);
                }
            }
            $Update_Product = "UPDATE Products SET Product_Name = :Product_Name, Category_ID = :Category_ID, SubCategory_ID = :SubCategory_ID, 
                                Manufacturer_ID = :Manufacturer_ID, Selling_Price = :Selling_Price, Buying_Price = :Buying_Price, 
                                Product_Quantity = :Product_Quantity, Product_Desc = :Product_Desc, 
                                Product_Visibility = :Product_Visibility 
                                WHERE Product_ID = :Product_ID";
            $pdoUpdate_Product = $connexion->prepare($Update_Product);
            $pdoUpdate_Product->execute([
                ':Product_Name' => $Product_Name,
                ':Category_ID' => $Category_ID,
                ':SubCategory_ID' => $SubCategory_ID,
                ':Manufacturer_ID' => $Manufacturer_ID,
                ':Selling_Price' => $Selling_Price,
                ':Buying_Price' => $Buying_Price,
                ':Product_Quantity' => $Product_Quantity,
                ':Product_Desc' => $Product_Desc,
                ':Product_Visibility' => $Product_Visibility,
                ':Product_ID' => $Product_ID
            ]);


            $deletedSpecs = isset($_POST['deletedSpecs']) ? json_decode($_POST['deletedSpecs']) : [];
            if (!empty($deletedSpecs)) {
                $placeholders = implode(',', array_fill(0, count($deletedSpecs), '?'));
                $queryRemoveSpecs = "DELETE FROM ProductSpecifications WHERE Product_ID = ? AND Specification_Name IN ($placeholders)";
                $params = array_merge([$Product_ID], $deletedSpecs);
                $pdostmtRemoveSpecs = $connexion->prepare($queryRemoveSpecs);
                $pdostmtRemoveSpecs->execute($params);
            }

            $productId = $Product_ID;

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


            $_SESSION['Product_Update'] = "Product Updated Successfully";

            header("Location: ../.");
            exit();
        } else {
            $Error_Message = "A product with that name already exists, try a different name for your product.";
        }
    }
    ?>


</head>



<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href=".././"><img src="../Logo.png" alt="Logo" id="Logo"></a>

            <!-- Category Links -->
            <div class="flex flex-wrap space-x-4">
                <?php foreach ($Categories as $Category): ?>
                    <?php if ($Category['Category_Name'] !== 'Unspecified'):
                        ?>
                        <?php
                        $Category_ID = $Category['Category_ID'];
                        $SubCategoriesQuery = "SELECT SubCategory_ID, SubCategory_Name FROM SubCategories WHERE Category_ID = :Category_ID ORDER BY SubCategory_ID ASC";
                        $pdoSubCategories = $connexion->prepare($SubCategoriesQuery);
                        $pdoSubCategories->execute([':Category_ID' => $Category_ID]);
                        $SubCategories = $pdoSubCategories->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <div class="relative category">
                            <a href=".././?id=<?php echo $Category['Category_ID'] ?>&Type=Category&Name=<?php echo str_replace(' ', '', $Category['Category_Name']) ?>"
                                class="px-3 py-2 hover:bg-gray-700"><?php echo $Category['Category_Name']; ?></a>
                            <?php if (!empty($SubCategories)): ?>
                                <div class="category-dropdown absolute top-full left-0 mt-1 bg-gray-800 rounded shadow-md p-2 hidden"
                                    style="min-width: 200px;">
                                    <?php foreach ($SubCategories as $SubCategory): ?>
                                        <?php if ($SubCategory['SubCategory_Name'] !== 'Unspecified'):

                                            ?>
                                            <a href=".././?id=<?php echo $SubCategory['SubCategory_ID'] ?>&Type=SubCategory&Name=<?php
                                               echo str_replace(' ', '', $SubCategory['SubCategory_Name']) ?>"
                                                class="block px-2 py-1 hover:bg-blue-600"><?php echo $SubCategory['SubCategory_Name']; ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- User Links -->
            <div class="flex space-x-4">
                <?php if (isset($_SESSION['User_ID'])):
                    if ($User_Role === 'Owner') {
                        $Emoji = '👑';
                    } elseif ($User_Role === 'Admin') {
                        $Emoji = '👨‍💼';
                    } else {
                        $Emoji = '💼';
                    }
                    ?>
                    <a href="../User/User_ShoppingCart.php"
                        class="flex items-center text-gray-300 hover:bg-green-700 px-3 py-2 rounded-md text-sm font-medium">
                        🛒 Shopping Cart
                        <?php if ($Cart_Count > 0) { ?>
                            (<?php echo $Cart_Count ?>)
                        <?php } ?>
                    </a>
                    <a class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium"
                        href="../User/User_Modify.php?id=<?php echo $User_ID; ?>&FullName=<?php echo urlencode($User_FullName); ?>">Currently
                        Logged in As : <br><span><?php echo $Emoji . ' ' . $User_FullName ?> -
                            <?php echo $User_Role ?></span></a>

                    <a href="../User/User_Logout.php"
                        class="text-gray-300 hover:bg-red-700 px-4 py-4 rounded-md text-sm font-medium">Logout</a>
                <?php else: ?>
                    <a href="../User/User_SignIn.php"
                        class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="../User/User_SignUp.php"
                        class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>
                <?php endif; ?>
            </div>

        </div>

        <?php if (!empty($_SESSION['User_ID']) || !empty($_SESSION['User_Role'])): ?>
            <?php if ($User['User_Role'] !== 'Client') { ?>
                <div class="bg-gray-800 text-white py-2 px-4">
                    <h6 class="text-sm font-medium text-gray-300 mb-1">Management Section</h6>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="space-y-1">
                            <a href="../Product/Products_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Product List</a>
                            <a href="../Product/Products_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Product</a>
                        </div>
                        <div class="space-y-1">
                            <a href="../Category/Categories_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Category List</a>
                            <a href="../Category/Categories_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Category</a>
                        </div>
                        <div class="space-y-1">
                            <a href="../Category/SubCategories/SubCategories_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Subcategory List</a>
                            <a href="Category/SubCategories/SubCategories_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Subcategory</a>
                        </div>
                        <div class="space-y-1">
                            <a href="../Manufacturer/Manufacturers_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Manufacturer List</a>
                            <a href="../Manufacturer/Manufacturers_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Manufacturer</a>
                        </div>
                        <?php if ($User['User_Role'] === 'Owner') { ?>
                            <div class="space-y-1">
                                <a href="../User/User_Management.php"
                                    class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑
                                    Users Dashboard</a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </nav>



    <div class="flex justify-center items-center bg-gray-100 min-h-screen py-8 total-wrapper">
        <div id="add-wrapper" class="max-w-5xl mx-3 p-6 bg-white rounded shadow" style=" min-height : 625px">
            <h1 class="text-3xl font-bold mb-6 text-blue-800 text-center h1-Add">Edit Product - ID :
                <?php echo $Product_Display['Product_ID'] ?>
            </h1>
            <form action="#" method="POST" enctype="multipart/form-data">
                <div class="flex space-x-6">
                    <div class="w-1/3">
                        <?php if ($Error_Message !== '') { ?>
                            <span
                                class="inline-block bg-yellow-200 text-yellow-800 rounded-full px-3 py-1 text-xs font-semibold mr-2">
                                <?php echo $Error_Message; ?>
                            </span>
                        <?php } ?>

                        <!-- Add more fields as needed -->
                        <div class="mb-4">
                            <label for="Product_Name" class="block text-sm font-medium text-gray-700">Product
                                Name:</label>
                            <input type="text" name="Product_Name" placeholder="Example: RTX 2060" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                value="<?php echo $Product_Display['Product_Name'] ?>">
                        </div>
                        <div class="mb-4">
                            <label for="Category_Name" class="block text-sm font-medium text-gray-700">Category:</label>
                            <select name="Category_Name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <?php
                                $queryCategories = "SELECT Category_Name FROM Categories ORDER BY Category_ID";
                                $pdostmtCategories = $connexion->prepare($queryCategories);
                                $pdostmtCategories->execute();
                                $Categories = $pdostmtCategories->fetchAll(PDO::FETCH_COLUMN);

                                foreach ($Categories as $Category) {
                                    $selected = '';
                                    if ($Category === $Product_Display['Category_Name']) {
                                        $selected = 'selected';
                                    }
                                    ?>
                                    <option value="<?php echo $Category; ?>" <?php echo $selected; ?>>
                                        <?php echo $Category; ?>
                                    </option>
                                <?php } ?>
                            </select>

                        </div>
                        <div class="mb-4">
                            <label for="SubCategory_Name" class="block text-sm font-medium text-gray-700">Sub
                                Category:</label>
                            <select name="SubCategory_Name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <?php
                                $querySubCategories = "SELECT SubCategory_Name FROM SubCategories ORDER BY SubCategory_ID";
                                $pdostmtSubCategories = $connexion->prepare($querySubCategories);
                                $pdostmtSubCategories->execute();
                                $SubCategories = $pdostmtSubCategories->fetchAll(PDO::FETCH_COLUMN);

                                foreach ($SubCategories as $SubCategory) {
                                    $selected = '';
                                    if ($SubCategory === $Product_Display['SubCategory_Name']) {
                                        $selected = 'selected';

                                    }

                                    ?>
                                    <option value="<?php echo $SubCategory; ?>" <?php echo $selected; ?>>
                                        <?php echo $SubCategory; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="Manufacturer_Name"
                                class="block text-sm font-medium text-gray-700">Manufacturer:</label>
                            <select name="Manufacturer_Name" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <?php
                                $queryManufacturers = "SELECT Manufacturer_Name FROM Manufacturers ORDER BY Manufacturer_ID";
                                $pdostmtManufacturers = $connexion->prepare($queryManufacturers);
                                $pdostmtManufacturers->execute();
                                $Manufacturers = $pdostmtManufacturers->fetchAll(PDO::FETCH_COLUMN);

                                foreach ($Manufacturers as $Manufacturer) {
                                    $selected = '';
                                    if ($Manufacturer === $Product_Display['Manufacturer_Name']) {
                                        $selected = 'selected';
                                    } ?>
                                    <option value="<?php echo $Manufacturer; ?>" <?php echo $selected; ?>>
                                        <?php echo $Manufacturer; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="Buying_Price" class="block text-sm font-medium text-gray-700">Buying
                                Price:</label>
                            <input type="number" name="Buying_Price" placeholder="In Dhs"
                                value="<?php echo $Product_Display['Buying_Price']; ?>" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="Selling_Price" class="block text-sm font-medium text-gray-700">Selling
                                Price:</label>
                            <input type="number" name="Selling_Price" placeholder="In Dhs"
                                value="<?php echo $Product_Display['Selling_Price']; ?>" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="Product_Quantity"
                                class="block text-sm font-medium text-gray-700">Quantity:</label>
                            <input type="number" name="Product_Quantity" placeholder="How many in stock"
                                value="<?php echo $Product_Display['Product_Quantity']; ?>" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="Product_Picture" class="block text-sm font-medium text-gray-700">Product
                                Picture:</label>
                            <input type="file" name="Product_Picture" accept="image/*"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div style="position : fixed ; margin-top : -10px">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block">Save
                                Changes</button>
                            <button type="reset"
                                class="bg-gray-500 hover:bg-red-600 text-white py-2 px-4 rounded-md inline-block">Reset</button>
                            <input type="hidden" id="deletedSpecs" name="deletedSpecs" value="">
                        </div>


                    </div>
                    <div class="w-1/3">

                        <div class="mb-4">
                            <label for="Product_Desc" class="block text-sm font-medium text-gray-700">Product
                                Description:</label>
                            <textarea name="Product_Desc" id="Product_Desc" cols="30" rows="5"
                                placeholder="Write a brief description about this product"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo $Product_Display['Product_Desc']; ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="Product_Visibility"
                                class="block text-sm font-medium text-gray-700">Visibility:</label>
                            <select name="Product_Visibility"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="Visible" <?php echo $Product_Display['Product_Visibility'] === 'Visible' ? 'selected' : ''; ?>>Visible</option>
                                <option value="Invisible" <?php echo $Product_Display['Product_Visibility'] === 'Invisible' ? 'selected' : ''; ?>>Invisible
                                </option>
                            </select>

                        </div>
                        <div class="mb-4">
                            <label for="Product_Picture" class="block mb-2 ">Current Product Picture</label>
                            <img src="<?php echo $Product_Display['Product_Picture']; ?>" alt="Error: Picture not Found"
                                class="border border-gray-400 rounded shadow-md max-w-full h-auto">
                        </div>



                        <!-- Add more fields as needed -->
                    </div>
                    <div class="w-1/3">

                        <div class="mb-4">
                            <h6 class="text-lg font-small mb-1">Specifications:</h6>
                            <div id="specifications">
                                <button type="button"
                                    class="mt-2 bg-blue-50 hover:bg-blue-500 text-white py-1 px-2 rounded-md"
                                    onclick="addSpecification()">➕</button>
                                <?php foreach ($specifications as $specification): ?>
                                    <div class="specification-container space-y-1 p-2 border rounded-lg">
                                        <label for="Specification_Name[]"
                                            class="block text-xs font-medium text-gray-700">Name:</label>
                                        <input type="text" name="Specification_Name[]"
                                            value="<?php echo $specification['Specification_Name']; ?>"
                                            class="w-full px-2 py-1 text-xs border-2 border-blue-500 rounded-md focus:outline-none focus:border-blue-700">
                                        <label for="Specification_Value[]"
                                            class="block text-xs font-medium text-gray-700">Value:</label>
                                        <input type="text" name="Specification_Value[]"
                                            value="<?php echo $specification['Specification_Value']; ?>"
                                            class="w-full px-2 py-1 text-xs border-2 border-green-500 rounded-md focus:outline-none focus:border-green-700">
                                        <button type="button" onclick="removeSpecification(this)"
                                            class="mt-1 px-2 py-1 text-xs bg-red-500 text-white font-bold rounded hover:bg-red-700 focus:outline-none">➖</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Add more fields as needed -->
                    </div>
                </div>




            </form>
        </div>
    </div>




    <script>
        window.addEventListener('DOMContentLoaded', function () {
            adjustContentMargin();
        });

        window.addEventListener('resize', function () {
            adjustContentMargin();
        });

        function adjustContentMargin() {
            var navHeight = document.querySelector('nav').offsetHeight;
            document.querySelector('.content-wrapper').style.marginTop = navHeight + 'px';
        }

        function addSpecification() {
            const specificationsDiv = document.getElementById('specifications');
            const newSpecDiv = document.createElement('div');
            newSpecDiv.className = 'specification-container space-y-1 p-2 border rounded-lg';
            newSpecDiv.innerHTML = `
        <label for="Specification_Name[]" class="block text-xs font-medium text-gray-700">Name:</label>
        <input type="text" name="Specification_Name[]" required class="w-full px-2 py-1 text-xs border-2 border-blue-500 rounded-md focus:outline-none focus:border-blue-700">
        <label for="Specification_Value[]" class="block text-xs font-medium text-gray-700">Value:</label>
        <input type="text" name="Specification_Value[]" required class="w-full px-2 py-1 text-xs border-2 border-green-500 rounded-md focus:outline-none focus:border-green-700">
        <button type="button" onclick="removeSpecification(this)" class="mt-1 px-2 py-1 text-xs bg-red-500 text-white font-bold rounded hover:bg-red-700 focus:outline-none">➖</button>
    `;
            specificationsDiv.appendChild(newSpecDiv);
        }

        function removeSpecification(button) {
            const specContainer = button.parentElement;
            const specName = specContainer.querySelector('input[name="Specification_Name[]"]').value;
            const deletedSpecsInput = document.getElementById('deletedSpecs');
            const deletedSpecs = deletedSpecsInput.value ? JSON.parse(deletedSpecsInput.value) : [];
            deletedSpecs.push(specName);
            deletedSpecsInput.value = JSON.stringify(deletedSpecs);
            specContainer.remove();
        }


    </script>

</body>

</html>
<style>
    #add-wrapper {
        width: 100%;
        margin-top: 11%;
        width: 60%;
    }


    .visibility-status {
        background-color: #EF4444;
        /* Red background */
        color: #FFFFFF;
        /* White text */
        padding: 0.25rem 0.5rem;
        /* Adjust padding */
        border-radius: 0.25rem;
        /* Rounded corners */
        font-size: 0.875rem;
        /* Adjust font size */
        font-weight: 500;
        /* Medium font weight */
        margin-top: 0.5rem;
        /* Add some space at the top */
        display: inline-block;
        /* Display as inline block */
        position: absolute;
        top: 0;
        right: 0;
        z-index: 1;
        /* Ensure it's above the image */
    }

    .category-dropdown {
        display: none;
    }

    .category:hover .category-dropdown {
        display: block;
    }

    #Logo {
        width: 30px;
        height: 34px;
    }

    nav {

        height: auto;
        position: fixed;

        width: 100%;

        z-index: 1000;

        margin-bottom: auto;
        opacity: 95%;
    }

    body {
        background-color: #e4e8f3;
    }

    .content-wrapper {
        padding-top: auto;

    }

    .card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .outer-container {
        display: flex;
        justify-content: center;
        width: 100%;
        padding-top: 16px;
    }

    .container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        width: 90%;
        max-width: 1500px;
        justify-content: space-between;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;

    }

    .content-wrapper {
        flex: 1;
        margin-top: 0;
        padding-top: 10px;

        overflow-y: auto;
    }
</style>