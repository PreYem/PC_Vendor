<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="Logo.png" type="image/x-icon">
    <title>Home 🏠︎ | PC Vendor</title>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

    <?php
    session_start();
    include_once ("C:\wamp64\www\PC_Vendor_Project\PC_Vendor/DB_Connexion.php");

    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
    }

    $Visible = 'Visible';

    #--------------------------------------------------------------
    
    $thresholdMinutes = 5;
    // 1 Hour = 60 Minutes
    // 1 Day = 720 Minutes
    // 1 Week = 10080 Minutes
    // 1 Month = 43800 Minutes
    
    #--------------------------------------------------------------
    if (isset($_GET['id'])) {

        $Target_ID = $_GET['id'];
        $Target_Type = $_GET['Type']; // 'Category' or 'SubCategory'
    
        if (isset($_SESSION['User_ID'])) {
            $User_ID = $_SESSION['User_ID'];
            $Users = "SELECT User_ID, User_Role, User_FirstName, User_LastName FROM Users WHERE User_ID = :User_ID";
            $pdoUsers = $connexion->prepare($Users);
            $pdoUsers->execute([':User_ID' => $User_ID]);
            $User = $pdoUsers->fetch(PDO::FETCH_ASSOC);
            $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];
            $User_Role = $User['User_Role'];
        } else {
            $User_Role = 'Client';
        }

        if ($Target_Type === 'Category') {
            $condition = "Category_ID = :Target_ID";
            $Target_Category_Name = "SELECT Category_Name FROM Categories WHERE $condition";
            $pdoTarget_Category_Name = $connexion->prepare($Target_Category_Name);
            $pdoTarget_Category_Name->execute([':Target_ID' => $Target_ID]);
            $Target_Name = $pdoTarget_Category_Name->fetchColumn();

        } elseif ($Target_Type === 'SubCategory') {
            $condition = "SubCategory_ID = :Target_ID";
            $Target_SubCategory_Name = "SELECT SubCategory_Name FROM SubCategories WHERE $condition";
            $pdoTarget_SubCategory_Name = $connexion->prepare($Target_SubCategory_Name);
            $pdoTarget_SubCategory_Name->execute([':Target_ID' => $Target_ID]);
            $Target_Name = $pdoTarget_SubCategory_Name->fetchColumn();

        }

        $visibilityCondition = ($User_Role === 'Client') ? "AND Product_Visibility = :Visible" : '';

        $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created 
                                FROM Products WHERE $condition $visibilityCondition ORDER BY Product_ID DESC";

        $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
        $params = [':Target_ID' => $Target_ID];
        if ($User_Role === 'Client') {
            $params[':Visible'] = $Visible;
        }
        $pdoGeneralProductQuery->execute($params);
        $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if (isset($_SESSION['User_ID'])) {
            $User_ID = $_SESSION['User_ID'];
            $Users = "SELECT User_ID, User_Role, User_FirstName, User_LastName FROM Users WHERE User_ID = :User_ID";
            $pdoUsers = $connexion->prepare($Users);
            $pdoUsers->execute([':User_ID' => $User_ID]);
            $User = $pdoUsers->fetch(PDO::FETCH_ASSOC);
            $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];
            $User_Role = $User['User_Role'];

            if ($User_Role !== 'Client') {
                $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created
                                        FROM Products ORDER BY Product_ID DESC";
            } else {
                $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created
                                        FROM Products WHERE Product_Visibility = :Visible ORDER BY Product_ID DESC";
            }
        } else {
            $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created
                                    FROM Products WHERE Product_Visibility = :Visible ORDER BY Product_ID DESC";
        }

        $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);


        if (!isset($User_Role) || $User_Role === 'Client') {
            $pdoGeneralProductQuery->bindParam(':Visible', $Visible, PDO::PARAM_STR);
            $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);
        }



        if (isset($_GET['Status'])) {

            $currentDate = new DateTime();
            $Limit_Minutes = $thresholdMinutes + 60;
            $currentDate->modify("-$Limit_Minutes minutes");
            $thresholdDateString = $currentDate->format('Y-m-d H:i:s');

            if (isset($_SESSION['User_Role'])) {

                if ($_SESSION['User_Role'] !== 'Client') {
                    $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created 
                    FROM Products WHERE Date_Created > :thresholdDateString ORDER BY Product_ID DESC";


                    $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
                    $pdoGeneralProductQuery->bindParam(':thresholdDateString', $thresholdDateString);
                    $pdoGeneralProductQuery->execute();
                    $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created 
                    FROM Products WHERE Date_Created > :thresholdDateString AND Product_Visibility = 'Visible' ORDER BY Product_ID DESC";
                    $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
                    $pdoGeneralProductQuery->bindParam(':thresholdDateString', $thresholdDateString);
                    $pdoGeneralProductQuery->execute();
                    $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);
                }
                ;
            } else {
                $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created 
                FROM Products WHERE Date_Created > :thresholdDateString AND Product_Visibility = 'Visible' ORDER BY Product_ID DESC";
                $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
                $pdoGeneralProductQuery->bindParam(':thresholdDateString', $thresholdDateString);
                $pdoGeneralProductQuery->execute();
                $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);
            }


        } else {

            if (!isset($_SESSION['User_Role'])) {
                $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created
                FROM Products WHERE Product_Visibility = 'Visible' ORDER BY Product_ID DESC";
                $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
                $pdoGeneralProductQuery->execute();
                $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);

            } elseif ($_SESSION['User_Role'] === 'Client') {
                $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created
                FROM Products WHERE Product_Visibility = 'Visible' ORDER BY Product_ID DESC";
                $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
                $pdoGeneralProductQuery->execute();
                $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);

            } else {
                $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture, Date_Created
                FROM Products ORDER BY Product_ID DESC";
                $pdoGeneralProductQuery = $connexion->prepare($GeneralProductQuery);
                $pdoGeneralProductQuery->execute();
                $GeneralProducts = $pdoGeneralProductQuery->fetchAll(PDO::FETCH_ASSOC);
            }
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

    ?>
</head>



<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href="./"><img src="Logo.png" alt="Logo" id="Logo"></a>

            <!-- Category Links -->
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
                            <a href="./?id=<?php echo $Category['Category_ID'] ?>&Type=Category&Name=<?php echo str_replace(' ', '', $Category['Category_Name']) ?>"
                                class="px-3 py-2 hover:bg-gray-700"><?php echo $Category['Category_Name']; ?></a>
                            <?php if (!empty($SubCategories)): ?>
                                <div class="category-dropdown absolute top-full left-0 mt-1 bg-gray-800 rounded shadow-md p-2 hidden"
                                    style="min-width: 200px;">
                                    <?php foreach ($SubCategories as $SubCategory): ?>
                                        <?php if ($SubCategory['SubCategory_Name'] !== 'Unspecified'):

                                            ?>
                                            <a href="./?id=<?php echo $SubCategory['SubCategory_ID'] ?>&Type=SubCategory&Name=<?php
                                               echo str_replace(' ', '', $SubCategory['SubCategory_Name']) ?>"
                                                class="block px-2 py-1 hover:bg-blue-600"><?php echo $SubCategory['SubCategory_Name']; ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
                <div><a href="./?Status=New" class="px-2 py-2 hover:bg-yellow-700">✨Newest Products✨</a></div>
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
                    <a href="User/User_ShoppingCart.php"
                        class="flex items-center text-gray-300 hover:bg-green-700 px-3 py-2 rounded-md text-sm font-medium">
                        🛒 Shopping Cart
                        <?php if ($Cart_Count > 0) { ?>
                            (<?php echo $Cart_Count ?>)
                        <?php } ?>
                    </a>
                    <a class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium"
                        href="User/User_Modify.php?id=<?php echo $User_ID; ?>&FullName=<?php echo urlencode($User_FullName); ?>">Currently
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
        <div>
            <?php if (!empty($_SESSION['User_ID']) || !empty($_SESSION['User_Role'])): ?>
                <?php if ($User['User_Role'] !== 'Client') { ?>
                    <div class="bg-gray-800 text-white py-2 px-4">
                        <h6 class="text-sm font-medium text-gray-300 mb-1">Management Section</h6>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            <div class="space-y-1">
                                <a href="Product/Products_List.php"
                                    class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">📋
                                    Product List (Old)</a>
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
                            <div class="space-y-1">
                                <?php if ($User['User_Role'] === 'Owner') { ?>

                                    <a href="User/User_Management.php"
                                        class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑
                                        Users Dashboard</a>
                                <?php } ?>
                                <a href="User/User_GlobalOrders.php"
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
        </div>

    </nav>


    <div class="outer-container">
        <div class="container" style=>
            <div class="content-wrapper pt-16 mt-6" >
                <h1 class="text-xl text-gray-700" id="PageNotFound" style="font-size: 3rem;">
                    404 Error<br><br>Page Not Found
                </h1>


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
        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                var updatedProductSpan = document.getElementById("UpdatedProduct");
                updatedProductSpan.classList.add("fade-out");
            }, 2000);
            setTimeout(function () {
                var updatedProductSpan = document.getElementById("UpdatedProduct");
                updatedProductSpan.classList.add("hidden");
            }, 2700);
        });

    </script>
</body>

</html>
<style>
    h1 {
        margin-top: 15%;
        margin-left: 28%;
        position: absolute;
        

    }

    #UpdatedProduct {
        margin-left: 44%;
        border: round;
        background-color: Green;
        padding-bottom: 10%;
        opacity: 80%;
        padding: 5px 5px;
        margin-top: 21%;
        height: 30px;
        font-size: 13px;
        position: relative;
        z-index: 1;
    }

    .fade-out {
        animation: fadeOut 500ms ease-in-out forwards;
    }

    @keyframes fadeOut {
        0% {
            opacity: 0.8;
            margin-left: 44%;
        }

        50% {
            opacity: 0.6;

        }

        100% {
            opacity: 0;
            margin-left: 0%;
            display: none;
        }
    }

    @keyframes fade {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0;
        }
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateY(0);
        }

        25% {
            transform: translateY(-1px);
        }

        75% {
            transform: translateY(1px);
        }
    }

    .NewProduct {
        background-color: #4bc639;
        color: #FFFFFF;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        display: inline-block;
        position: absolute;
        top: 0;
        right: 0;
        z-index: 1;
        /* Keeps an upper limit on width */
        animation: fade 5s infinite, shake 1s infinite;
        /* Apply both animations */
    }




    .visibility-status {
        background-color: #EF4444;
        color: #FFFFFF;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        display: inline-block;
        position: absolute;
        top: 0;
        right: 0;
        z-index: 2;
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
        text-align: center;

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