<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Add Product | PC Vendor</title>
    <?php
    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
    }
    include_once ("../DB_Connexion.php");
    session_start();
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

        header("Location: ../User/User_SignIn.php");
        exit;
    }

    $User_ID = $_SESSION['User_ID'];
    $query = "SELECT User_Role, User_Username , User_FirstName , User_LastName FROM Users WHERE User_ID = :User_ID";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':User_ID' => $User_ID]);

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $User_Role = $row['User_Role'];
        $User_Username = $row['User_Username'];
        $User_FullName = $row['User_FirstName'] . ' ' . $row['User_LastName'];

        if ($User_Role === 'Owner') {
            $showUserManagement = true;
        } else {
            $showUserManagement = false;
        }


        if ($User_Role == 'Client') {
            header("Location: ../User/User_Unauthorized.html");
            exit;
        }
    }
    ;

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
    ;
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

                    $productPicture = 'Product_Pictures/Default_Product_Picture.jpg';

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

                $_SESSION['Product_Add/Update'] = "Product Added Successfully";

                header("Location: ../.");
            } else {
                $Error_Message = 'A Product with that name already exists, try again!';
            }
        }
    }
    ?>
</head>



<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">

            <a href="../"><img src="../Logo.png" alt="Logo" id="Logo"></a>


            <div class="flex grid-cols-4 gap-1">
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
                            <a href="../?id=<?php echo $Category['Category_ID'] ?>&Type=Category&Name=<?php echo str_replace(' ', '', $Category['Category_Name']) ?>"
                                class="px-3 py-2 hover:bg-gray-700"><?php echo $Category['Category_Name']; ?></a>
                            <?php if (!empty($SubCategories)): ?>
                                <div class="category-dropdown absolute top-full left-0 mt-1 bg-gray-800 rounded shadow-md p-2 hidden"
                                    style="min-width: 200px;">
                                    <?php foreach ($SubCategories as $SubCategory): ?>
                                        <?php if ($SubCategory['SubCategory_Name'] !== 'Unspecified'): ?>
                                            <a href="../?id=<?php echo $SubCategory['SubCategory_ID'] ?>&Type=SubCategory&Name=<?php echo str_replace(' ', '', $SubCategory['SubCategory_Name']) ?>"
                                                class="block px-2 py-1 hover:bg-blue-600"><?php echo $SubCategory['SubCategory_Name']; ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
                <div><a href="./../?Status=New" class="px-2 py-2 hover:bg-yellow-700">✨Newest Products✨</a></div>
            </div>


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
                    <a href="User_ShoppingCartTEST.php"
                        class="flex items-center text-gray-300 hover:bg-green-700 px-3 py-2 rounded-md text-sm font-medium">🛒
                        Shopping Cart
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
            <?php if ($row['User_Role'] !== 'Client') { ?>
                <div class="bg-gray-800 text-white py-2 px-4">
                    <h6 class="text-sm font-medium text-gray-300 mb-1">Management Section</h6>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                        <div class="space-y-1">
                            <a href="../Product/Products_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Product List (Old)</a>
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
                            <a href="../Category/SubCategories/SubCategories_Add.php"
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
                        <div class="space-y-1">
                            <?php if ($showUserManagement) { ?>

                                <a href="../User/User_Management.php"
                                    class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑
                                    Users Dashboard</a>
                            <?php } ?>
                            <a href="../User/User_GlobalOrders.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🚨
                                Pending Orders <?php
                                $Order_Pending = "SELECT Order_ID FROM Orders WHERE  Order_Status NOT IN ('Cancelled By User', 'Cancelled by Management') ";
                                $pdostmt = $connexion->prepare($Order_Pending);
                                $pdostmt->execute();

                                $Order_Count = $pdostmt->rowCount();
                                ?> <span style="color : red"><?php if ($Order_Count > 0) {
                                     echo '(' . $Order_Count . ')';
                                 } ?></span></a>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </nav>

    <div class="flex justify-center items-center bg-gray-100 min-h-screen py-8 total-wrapper">
        <div id="add-wrapper" class="max-w-5xl mx-3 p-6 bg-white rounded shadow">
            <h1 class="text-3xl font-bold mb-6 text-blue-800 text-center h1-Add">Add a New Product</h1>
            <form action="#" method="POST" enctype="multipart/form-data">
                <div class="flex space-x-6">
                    <div class="w-1/3">

                        <!-- Add more fields as needed -->
                        <div class="mb-4">
                            <label for="Product_Name" class="block text-sm font-medium text-gray-700">Product
                                Name:</label>
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
                                    echo "<option value=\"$SubCategory\">$SubCategory</option>";
                                }
                                ?>
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
                                    echo "<option value=\"$Manufacturer\">$Manufacturer</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="Buying_Price" class="block text-sm font-medium text-gray-700">Buying Price:</label>
                            <input type="number" name="Buying_Price" placeholder="In Dhs" value="1" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="Selling_Price" class="block text-sm font-medium text-gray-700">Selling
                                Price:</label>
                            <input type="number" name="Selling_Price" placeholder="In Dhs" value="1" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="Product_Quantity"
                                class="block text-sm font-medium text-gray-700">Quantity:</label>
                            <input type="number" name="Product_Quantity" placeholder="How many in stock" value="1"
                                required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div class="mb-4">
                            <label for="Product_Picture" class="block text-sm font-medium text-gray-700">Product
                                Picture:</label>
                            <input type="file" name="Product_Picture" accept="image/*"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>

                    </div>
                    <div class="w-1/3">

                        <div class="mb-4">
                            <label for="Product_Desc" class="block text-sm font-medium text-gray-700">Product
                                Description:</label>
                            <textarea name="Product_Desc" id="Product_Desc" cols="30" rows="5"
                                placeholder="Write a brief description about this product"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="Product_Visibility"
                                class="block text-sm font-medium text-gray-700">Visibility:</label>
                            <select name="Product_Visibility"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="Visible">Visible</option>
                                <option value="Invisible">Invisible</option>
                            </select>
                        </div>
                        <!-- Add more fields as needed -->
                    </div>
                    <div class="w-1/3">

                        <div class="mb-4">
                            <h6 class="text-lg font-small mb-1">Specifications:</h6>
                            <div id="specifications"></div>
                            <button type="button"
                                class="mt-2 bg-purple-500 hover:bg-purple-600 text-white py-1 px-2 rounded-md"
                                onclick="addSpecification()">➕ Specification</button>
                        </div>
                        <!-- Add more fields as needed -->
                    </div>
                </div>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block">Add
                    Product</button>
                <button type="reset"
                    class="bg-gray-500 hover:bg-red-600 text-white py-2 px-4 rounded-md inline-block">Reset</button>

                <span class="text-red-500 block mt-4">
                    <?php echo $Error_Message ?>
                </span>
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
            newSpecDiv.className = 'space-y-1 p-2 border rounded-lg';
            newSpecDiv.innerHTML = `
                <label for="Specification_Name[]" class="block text-xs font-medium text-gray-700">Name:</label>
                <input type="text" name="Specification_Name[]" required class="w-full px-2 py-1 text-xs border-2 border-blue-500 rounded-md focus:outline-none focus:border-blue-700">
                <label for="Specification_Value[]" class="block text-xs font-medium text-gray-700">Value:</label>
                <input type="text" name="Specification_Value[]" required class="w-full px-2 py-1 text-xs border-2 border-green-500 rounded-md focus:outline-none focus:border-green-700">
                <button type="button" onclick="removeSpecification(this)" class="mt-1 px-2 py-1 text-xs bg-red-500 text-white font-bold rounded hover:bg-red-700 focus:outline-none">X</button>
            `;
            specificationsDiv.appendChild(newSpecDiv);
        }

        function removeSpecification(button) {
            const specDiv = button.parentElement;
            specDiv.remove();
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

    .h1-Add {
        border: 1px solid black;
        margin-left: 200px;
        margin-right: 200px;

    }

    .form-wrapper {
        margin-top: 10%;
    }

    .CC {
        margin-left: 40%;
        margin-top: 20%;
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
        opacity: 99%;
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

    .product-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        max-width: 100%;

        display: inline-block;
        max-height: 1.2em;

    }
</style>