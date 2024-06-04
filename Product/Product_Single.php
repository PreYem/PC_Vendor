<?php
session_start();
include_once ("../DB_Connexion.php");

function formatNumber($number)
{
    return number_format($number, 0, '', ' ');
}

if (isset($_SESSION['User_ID']) || isset($_SESSION['User_Role'])) {
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


if (isset($_GET['id'])) {
    $Product_ID = $_GET['id'];


    if ($User_Role !== 'Client') {
        $Product_Detail = "SELECT * FROM Products WHERE Product_ID = :Product_ID";
        $pdoProduct_Detail = $connexion->prepare($Product_Detail);
        $pdoProduct_Detail->execute(['Product_ID' => $Product_ID]);
        $Product_Detail = $pdoProduct_Detail->fetch(PDO::FETCH_ASSOC);

    } else {
        $Product_Detail = "SELECT * FROM Products WHERE Product_ID = :Product_ID AND Product_Visibility = 'Visible'";
        $pdoProduct_Detail = $connexion->prepare($Product_Detail);
        $pdoProduct_Detail->execute(['Product_ID' => $Product_ID]);
        $Product_Detail = $pdoProduct_Detail->fetch(PDO::FETCH_ASSOC);
    }
    ;


    if (!$Product_Detail) {
        header('Location: ../.');
        exit();
    }



    if ($Product_Detail['Selling_Price'] > $Product_Detail['Discount_Price']) {
        $Discount = 'YES';
        $SaveAmount = $Product_Detail['Selling_Price'] - $Product_Detail['Discount_Price'];
        $SavePercent = intval($SaveAmount / $Product_Detail['Selling_Price'] * 100);
    } elseif ($Product_Detail['Selling_Price'] == $Product_Detail['Discount_Price']) {
        $Discount = 'NO';
    }



    $Select_Specs = "SELECT Specification_Name , Specification_Value FROM ProductSpecifications WHERE Product_ID = :Product_ID";
    $pdoSelect_Specs = $connexion->prepare($Select_Specs);
    $pdoSelect_Specs->execute(['Product_ID' => $Product_ID]);











} else {
    header('Location: ../.');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title><?php echo $Product_Detail['Product_Name']; ?> | PC Vendor</title>

</head>

<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href=".././"><img src="../Logo.png" alt="Logo" id="Logo"></a>

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
                <div><a href="./../?Status=New" class="px-2 py-2 hover:bg-yellow-700">✨Newest Products✨</a></div>
                <div><a href="./../?Status=Discount" class="px-2 py-2 hover:bg-yellow-700">🏷️On Sale🏷️</a></div>
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
                            <?php if ($User['User_Role'] === 'Owner') { ?>

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



    <div class="flex justify-center items-center bg-gray-100 min-h-screen py-8 total-wrapper" id="ContentContainer">



        <section id="Section01">
            <div class="SingleInfo">
                <img src="<?php echo $Product_Detail['Product_Picture']; ?>" alt="">
            </div>
            <div class="SingleInfo">
                <table class="table-auto w-full text-left text-sm font-light text-surface dark:text-white">
                    <h1>Technical Sheet :</h1>
                    <thead class="border-b border-neutral-200 font-medium dark:border-white/10">

                        <tr>
                            <th scope="col" class="px-6 py-4">Specification Name</th>
                            <th scope="col" class="px-6 py-4">Specification Value</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if ($row = $pdoSelect_Specs->fetch(PDO::FETCH_ASSOC)) {

                            while ($row = $pdoSelect_Specs->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $row['Specification_Name']; ?></td>
                                    <td class="px-6 py-4 text-ellipsis overflow-hidden">
                                        <?php echo $row['Specification_Value']; ?>
                                    </td>

                                </tr>
                            <?php endwhile;
                        } else { ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">No Names</td>
                                <td class="px-6 py-4 text-ellipsis overflow-hidden">No Values</td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>


            </div>

        </section>

        <section id="Section02">
            <div class="SingleInfo">
                <h1 class="text-3xl font-bold mb-6 text-blue-800 text-center h1-Add">
                    <?php echo $Product_Detail['Product_Name']; ?>
                </h1>
            </div>
            <div class="SingleInfo">
                <p id="Product_Desc"><?php if ($Product_Detail['Product_Desc'] != '') {

                    echo $Product_Detail['Product_Desc'];
                } else {
                    echo 'No Description Available';
                } ?></p>
            </div>
            <div class="SingleInfo">
                <?php if ($Product_Detail['Product_Quantity'] > 0) { ?>
                    <span
                        class="text-green-500 font-bold"><?php echo '(' . $Product_Detail['Product_Quantity'] . ')'; ?></span>
                    In Stock <a href="Add_To_Cart.php?id=<?php echo $Product_Detail['Product_ID']; ?>"
                        class="block bg-blue-500 text-white py-1 px-2 rounded hover:bg-blue-600 text-sm flex-grow w-28"
                        onclick="return alert('<?php echo $Product_Detail['Product_Name'] ?> has been added To Your Shopping Cart.')">Add
                        to Cart 🛒</a>


                <?php } else { ?>
                    <span class="text-red-500 font-bold">Out of stock</span>


                </div>


            <?php } ?><br>
            <div>Price :
                <?php if ($Product_Detail['Selling_Price'] == $Product_Detail['Discount_Price'] || $Product_Detail['Discount_Price'] == 0) { ?>

                    <p class="text-blue-500 mb-1 font-bold">
                        <?php echo formatNumber($Product_Detail['Selling_Price']); ?> Dhs
                    </p>
                <?php } else { ?>
                    <p class="text-gray-500 mb-1">
                        <span class="line-through italic"><?php echo formatNumber($Product_Detail['Selling_Price']); ?>
                            Dhs</span>
                        <span
                            class="text-purple-500 font-bold"><?php echo formatNumber($Product_Detail['Discount_Price']); ?>
                            Dhs</span>
                    </p>

                <?php } ?>

                <?php if ($User_Role !== 'Client') { ?>
                    <div class="flex space-x-2">
                        <a href="Products_Modify.php?id=<?php echo $Product_Detail['Product_ID']; ?>&Name=<?php echo $Product_Detail['Product_Name']; ?>"
                            class="bg-green-500 text-white py-1 px-2 rounded hover:bg-green-600 text-sm">
                            ⚙️</a>
                        <a href="Products_Delete.php?id=<?php echo $Product_Detail['Product_ID']; ?>"
                            class="bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600 text-sm"
                            onclick="return confirm('Are you sure you want to delete this product?\n*Disclaimer* : This action is irreversible.')">
                            🗑️</a>
                    </div>
                <?php } ?>


            </div>


    </div>
    <div class="SingleInfo">


        </section>
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

<style>
    table {
        border: 1px solid black;
    }

    #Product_Desc {
        font-size: 18px;
        border: 2px solid black;
        padding: 10px;
    }

    #Section01 {
        /* background-color: #d582e8; */
        width: 48%;
        height: 100vh;
        z-index: 1;
        position: absolute;
        margin-left: -50%;

    }

    #Section02 {
        /* background-color: #9dd182; */
        width: 10%;
        z-index: 1;
        width: 48%;
        margin-left: 50%;
        height: 100vh;
        z-index: 1;
        position: absolute;

    }

    .SingleInfo {
        position: relative;
        margin-top: 30px;
        margin-left: 60px;

        margin-right: 60px
    }

    img {
        height: auto;
        max-width: auto;
    }

    #ContentContainer {
        height: 1500px;
        width: 70%;
        position: absolute;
        margin-top: 0%;
        background-color: #ededed;
        margin-left: 13%;

    }



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
</style>

</html>