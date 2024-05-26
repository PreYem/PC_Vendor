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

    function formatNumber($number) {
        return number_format($number, 0, '', ' ');
    }
    
  
    
    
    


    if (!empty($_SESSION['User_ID'])) {
        $User_ID = $_SESSION['User_ID'];
        $Users = "SELECT User_ID, User_Role, User_FirstName , User_LastName , User_Role FROM Users WHERE User_ID = :User_ID";
        $pdoUsers = $connexion->prepare($Users);
        $pdoUsers->execute([':User_ID' => $User_ID]);
        $User = $pdoUsers->fetch(PDO::FETCH_ASSOC);

        $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];
        $User_Role = $User['User_Role'];
    }
    ;



    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);



    $Visible = 'Visible';

    // Define the query with named parameter
    $GeneralProductQuery = "SELECT Product_ID,Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture FROM Products WHERE Product_Visibility = :Visible";

    // Prepare the query
    $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);

    // Bind the value of $Visible to the named parameter :Visible
    $pdoGeneralProductQuery->bindParam(':Visible', $Visible, PDO::PARAM_STR);

    // Execute the query
    $pdoGeneralProductQuery->execute();

    // Fetch the results
    $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);








    ?>
</head>

<style>
    /* Additional CSS for category dropdown */
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
        /* Ensure the parent uses the full width */
        padding-top: 16px;
        /* Adjust padding to make space for fixed nav */
    }

    .container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        width: 90%;
        /* You can adjust the width as needed */
        max-width: 1500px;
        /* You can adjust the max width as needed */
        justify-content: space-between;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        /* Ensure the nav bar is above other content */
    }

    .content-wrapper {
        flex: 1;
        margin-top: 0;
        padding-top: 10px;
        /* Adjust as needed based on your nav bar's height */
        overflow-y: auto;
    }

    #Content {
        /* background-color: black; */
    }

    .product-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        /* You can use `white-space: normal` with max-height and overflow for multiline ellipsis */
        max-width: 100%;
        /* Ensure it doesn't exceed the container width */
        display: inline-block;
        max-height: 1.2em;
        /* Adjust this value based on the font-size to fit a single line */
    }
</style>

<body class="bg-gray-100">

    <nav class="bg-gray-800 text-white">
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
                        Logged in As : <br><span style="border : 2 px solid Purple">
                            <?php echo $Emoji . ' ' . $User_FullName ?> -
                            <?php echo $User_Role ?></span></a>


                    <a href="User/User_Logout.php"
                        class="text-    -300 hover:bg-red-700 px-4 py-4 rounded-md text-sm font-medium">Logout</a>
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
                <div class="bg-gray-800 text-white p-4">
                    <h6 class="text-sm font-medium text-gray-300 mb-2">Management</h6>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="space-y-2">
                            <a href="Product/Products_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Product List</a>
                            <a href="Product/Products_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New
                                Product</a>
                        </div>
                        <div class="space-y-2">

                            <a href="Category/Categories_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Category List</a>
                            <a href="Category/Categories_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New
                                Category</a>
                        </div>

                        <div class="space-y-2">
                            <a href="Category/SubCategories/SubCategories_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Subcategory List</a>
                            <a href="Category/SubCategories/SubCategories_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New
                                Subcategory</a>
                        </div>

                        <div class="space-y-2">
                            <a href="Manufacturer/Manufacturers_List.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                Manufacturer List</a>
                            <a href="Manufacturer/Manufacturers_Add.php"
                                class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">➕
                                New
                                Manufacturer</a>
                        </div>



                        <?php if ($User['User_Role'] === 'Owner') { ?>
                            <div class="space-y-2">
                                <a href="User/User_Management.php"
                                    class="block bg-gray-700 hover:bg-blue-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑
                                    Users
                                    Dashboard</a>
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
                        <?php foreach ($GeneralProducts as $Product): ?>
                            <div
                                class="bg-white rounded-lg overflow-hidden shadow-lg card w-full sm:w-full md:w-full lg:w-full">
                                <a href=""><img class="w-full h-32 object-cover object-center"
                                        style="width : auto ; height : auto"
                                        src="Product/<?php echo $Product['Product_Picture']; ?>" alt="Product Image"></a>
                                <div class="p-2">
                                    <h2 class="product-name font-bold text-base mb-1">
                                        <?php echo $Product['Product_Name']; ?></h2>

                                    <p class="text-gray-700 mb-1"><?php echo formatNumber($Product['Selling_Price']); ?> Dhs</p>
                                    <p class="text-gray-700 mb-1">In Stock (<?php echo $Product['Product_Quantity']; ?>)</p>
                                    <a href="Product/Add_To_Cart.php?id=<?php echo $Product['Product_ID']; ?>"
                                        class="block bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600 text-sm">Add
                                        to Cart</a>
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