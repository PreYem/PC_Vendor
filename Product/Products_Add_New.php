<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>

    <?php include_once ("Default_Page.php");
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
                    'Product_Picture' => $productPicture,
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
                header("Location: ../index.php");
            } else {
                $Error_Message = 'A Product with that name already exists, try a different name';
            }
        }
    }
    ?>

</head>

<body>
    <div class="outer-container">
        <div class="container">
            <div class="content-wrapper pt-16">
                <div class="flex justify-center items-center bg-gray-100 min-h-screen py-8 total-wrapper">
                    <div id="add-wrapper" class="max-w-5xl mx-3 p-6 bg-white rounded shadow">
                        <h1 class="text-3xl font-bold mb-6 text-blue-800 text-center h1-Add">Add a New Product</h1>
                        <form action="#" method="POST" enctype="multipart/form-data">
                            <div class="flex space-x-6">
                                <div class="w-1/3">

                                    <!-- Add more fields as needed -->
                                    <div class="mb-4">
                                        <label for="Product_Name"
                                            class="block text-sm font-medium text-gray-700">Product
                                            Name:</label>
                                        <input type="text" name="Product_Name" placeholder="Example: RTX 2060" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="mb-4">
                                        <label for="Category_Name"
                                            class="block text-sm font-medium text-gray-700">Category:</label>
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
                                        <label for="SubCategory_Name"
                                            class="block text-sm font-medium text-gray-700">Sub
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
                                        <label for="Buying_Price" class="block text-sm font-medium text-gray-700">Buying
                                            Price:</label>
                                        <input type="number" name="Buying_Price" placeholder="In Dhs" value="0" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="mb-4">
                                        <label for="Selling_Price"
                                            class="block text-sm font-medium text-gray-700">Selling
                                            Price:</label>
                                        <input type="number" name="Selling_Price" placeholder="In Dhs" value="0"
                                            required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="mb-4">
                                        <label for="Product_Quantity"
                                            class="block text-sm font-medium text-gray-700">Quantity:</label>
                                        <input type="number" name="Product_Quantity" placeholder="How many in stock"
                                            value="0" required
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div class="mb-4">
                                        <label for="Product_Picture"
                                            class="block text-sm font-medium text-gray-700">Product
                                            Picture:</label>
                                        <input type="file" name="Product_Picture" accept="image/*"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>

                                </div>
                                <div class="w-1/3">

                                    <div class="mb-4">
                                        <label for="Product_Desc"
                                            class="block text-sm font-medium text-gray-700">Product
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
            </div>
        </div>
    </div>
</body>
<style>
    .container {
        margin-top: -8%;
    }
</style>

</html>