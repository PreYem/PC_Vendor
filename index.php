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


    if (!empty($_SESSION['User_ID'])) {
        $User_ID = $_SESSION['User_ID'];
        $Users = "SELECT User_ID, User_Role, User_FirstName , User_LastName , User_Role FROM Users WHERE User_ID = :User_ID";
        $pdoUsers = $connexion->prepare($Users);
        $pdoUsers->execute([':User_ID' => $User_ID]);
        $User = $pdoUsers->fetch(PDO::FETCH_ASSOC);

        $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];
    }
    ;



    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);








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
        height: auto
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
                <?php if (isset($_SESSION['User_ID'])): ?>
                    <a class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium" href="#">Currently
                        Logged in As : <span style="Color : Purple"><?php echo $User_FullName ?></span></a>
                    <a href="User/User_Logout.php"
                        class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
                <?php else: ?>
                    <a href="User/User_SignIn.php"
                        class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                    <a href="User/User_SignUp.php"
                        class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Management Links -->
        <?php if (!empty($_SESSION['User_ID']) || !empty($_SESSION['User_Role'])): ?>
            <?php if ($User['User_Role'] !== 'Client') { ?>
                <div class="bg-gray-800 text-white p-4">
                    <h6 class="text-sm font-medium text-gray-300 mb-2">Management</h6>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="Category/Categories_List.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">Category
                            List</a>
                        <a href="Category/Categories_Add.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">New
                            Category</a>
                        <a href="Category/SubCategories/SubCategories_List.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">Sub
                            Category List</a>
                        <a href="Category/SubCategories/SubCategories_Add.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">New
                            Sub Category</a>
                        <a href="Manufacturer/Manufacturers_Add.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">New
                            Manufacturer</a>
                        <a href="Manufacturer/Manufacturers_List.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">Manufacturer
                            List</a>
                        <a href="Product/Products_Add.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">New
                            Product</a>
                        <a href="Product/Products_List.php"
                            class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">Product
                            List</a>

                        <?php if ($User['User_Role'] === 'Owner') { ?>

                            <a href="User/User_Management.php"
                                class="block bg-gray-700 hover:bg-gray-600 px-4 py-3 rounded-md text-sm font-medium text-gray-300 transition duration-300">Users Dashboard</a>
                        <?php } ?>

                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>


    </nav>

</body>




</html>