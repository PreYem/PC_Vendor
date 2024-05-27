<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="Logo.png" type="image/x-icon">
    <title>Home</title>
    <?php
    session_start();
    include_once ("DB_Connexion.php");

    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
    }

    $Visible = 'Visible';
    if (isset($_SESSION['User_ID'])) {
        $User_ID = $_SESSION['User_ID'];
        $Users = "SELECT User_ID, User_Role, User_FirstName, User_LastName FROM Users WHERE User_ID = :User_ID";
        $pdoUsers = $connexion->prepare($Users);
        $pdoUsers->execute([':User_ID' => $User_ID]);
        $User = $pdoUsers->fetch(PDO::FETCH_ASSOC);
        $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];
        $User_Role = $User['User_Role'];

        if ($User_Role !== 'Client') {
            $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture 
                                    FROM Products";
        } else {
            $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture 
                                    FROM Products WHERE Product_Visibility = :Visible";
        }
    } else {
        $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture 
                                FROM Products WHERE Product_Visibility = :Visible";
    }

    $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);

    if (!isset($User_Role) || $User_Role === 'Client') {
        $pdoGeneralProductQuery->bindParam(':Visible', $Visible, PDO::PARAM_STR);
    }

    $pdoGeneralProductQuery->execute();
    $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);



    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);









    ?>
</head>

<style>
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

    .product-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        max-width: 100%;

        display: inline-block;
        max-height: 1.2em;

    }
</style>

<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href="index.php"><img src="Logo.png" alt="Logo" id="Logo"></a>

            <!-- Category Links -->
            <div class="flex flex-wrap space-x-4">
                <?php foreach ($Categories as $Category): ?>
                    <?php if ($Category['Category_Name'] !== 'Unspecified'): ?>
                        <?php
                        $Category_ID = $Category['Category_ID'];
                        $SubCategoriesQuery = "SELECT SubCategory_ID, SubCategory_Name FROM SubCategories WHERE Category_ID = :Category_ID ORDER BY SubCategory_ID ASC";
                        $pdoSubCategories = $connexion->prepare($SubCategoriesQuery);
                        $pdoSubCategories->execute([':Category_ID' => $Category_ID]);
                        $SubCategories = $pdoSubCategories->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <div class="relative category">
                            <a href="#" class="px-3 py-2 hover:bg-gray-700"><?php echo $Category['Category_Name']; ?></a>
                            <?php if (!empty($SubCategories)): ?>
                                <div class="category-dropdown absolute top-full left-0 mt-1 bg-gray-800 rounded shadow-md p-2 hidden"
                                    style="min-width: 200px;">
                                    <?php foreach ($SubCategories as $SubCategory): ?>
                                        <?php if ($SubCategory['SubCategory_Name'] !== 'Unspecified'): ?>
                                            <a href="#"
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

                    <a class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium" href="#">Currently
                        Logged in As : <br><span><?php echo $Emoji . ' ' . $User_FullName ?> -
                            <?php echo $User_Role ?></span></a>

                    <a href="User/User_Logout.php"
                        class="text-gray-300 hover:bg-red-700 px-4 py-4 rounded-md text-sm font-medium">Logout</a>
                <?php else: ?>
                    <a href="User/User_SignIn.php"
                        class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="User/User_SignUp.php"
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
                            <a href="Product/Products_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Product List</a>
                            <a href="Product/Products_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Product</a>
                        </div>
                        <div class="space-y-1">
                            <a href="Category/Categories_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Category List</a>
                            <a href="Category/Categories_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Category</a>
                        </div>
                        <div class="space-y-1">
                            <a href="Category/SubCategories/SubCategories_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Subcategory List</a>
                            <a href="Category/SubCategories/SubCategories_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Subcategory</a>
                        </div>
                        <div class="space-y-1">
                            <a href="Manufacturer/Manufacturers_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Manufacturer List</a>
                            <a href="Manufacturer/Manufacturers_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New Manufacturer</a>
                        </div>
                        <?php if ($User['User_Role'] === 'Owner') { ?>
                            <div class="space-y-1">
                                <a href="User/User_Management.php"
                                    class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑
                                    Users Dashboard</a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </nav>






    <div class="outer-container">
        <div class="container">
            <div class="content-wrapper pt-16">
                <section id="Content">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-12">
                        <?php foreach ($GeneralProducts as $Product):
                            $Visibility = '';
                            if ($Product['Product_Visibility'] === 'Invisible') {
                                $Visibility = 'Status : OFF';
                            }


                            ?>
                            <div
                                class="bg-white rounded-lg overflow-hidden shadow-lg card w-full sm:w-full md:w-full lg:w-full">
                                <?php
                                if ($Visibility) { ?>
                                    <span
                                        class="visibility-status bg-red-500 text-white px-2 py-1 rounded"><?php echo $Visibility ?></span>
                                <?php } ?>
                                <a href=""><img class="w-full h-32 object-cover object-center"
                                        style="width : auto ; height : auto"
                                        src="Product/<?php echo $Product['Product_Picture']; ?>" alt="Product Image"></a>
                                <div class="p-2">
                                    <h2 class="product-name font-bold text-base mb-1">
                                        <?php echo $Product['Product_Name']; ?>
                                    </h2>

                                    <p class="text-gray-700 mb-1"><?php echo formatNumber($Product['Selling_Price']); ?> Dhs
                                    </p>
                                    <p class="text-gray-700 mb-1">In Stock (<?php echo $Product['Product_Quantity']; ?>)</p>

                                    <?php if (isset($_SESSION['User_ID']) && $User['User_Role'] !== 'Client') {


                                        ?>

                                        <div class="flex justify-between items-center space-x-2">
                                            <a href="Product/Add_To_Cart.php?id=<?php echo $Product['Product_ID']; ?>"
                                                class="block bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600 text-sm flex-grow">Add
                                                to Cart</a>
                                            <div class="flex space-x-2">
                                                <a href="Product/Products_Modify.php?id=<?php echo $Product['Product_ID']; ?>"
                                                    class="bg-green-500 text-white py-1 px-2 rounded hover:bg-green-600 text-sm">Edit</a>

                                                <a href="Product/Products_Delete.php?id=<?php echo $Product['Product_ID']; ?>"
                                                    class="bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600 text-sm"
                                                    onclick="return confirm('Are you sure you want to delete this product?\n*Disclaimer* : This action is irreversible')">Delete</a>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <a href="Product/Add_To_Cart.php?id=<?php echo $Product['Product_ID']; ?>"
                                            class="block bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600 text-sm w-full">Add
                                            to Cart</a>

                                    <?php } ?>
                                </div>



                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>
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
    </script>
</body>

</html>