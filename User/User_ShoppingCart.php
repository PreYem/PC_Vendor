<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Shopping Cart 🛒 | PC Vendor</title>


    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

    <?php

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


        if ($User_Role !== 'Owner' && $User_Role !== 'Admin' && $User_Role !== 'Client') {
            header("Location: ../User/User_Unauthorized.html");
            exit;
        }
    }
    ;

    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
    }

    $Visible = 'Visible';
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

        $GeneralProductQuery = "SELECT Product_ID, Product_Name, Selling_Price, Product_Quantity, Product_Visibility, Product_Picture 
                                FROM Products WHERE $condition $visibilityCondition";

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
    };

    $Shopping_Query = " SELECT sc.CartItem_ID, sc.Product_ID, sc.Quantity, p.Product_Name, p.Selling_Price, p.Product_Picture
                        FROM ShoppingCart sc
                        JOIN Products p ON sc.Product_ID = p.Product_ID
                        WHERE sc.User_ID = :User_ID";
    $pdostmt = $connexion->prepare($Shopping_Query);
    $pdostmt->execute([':User_ID' => $User_ID]);
    $Shopping_Cart = $pdostmt->fetchAll(PDO::FETCH_ASSOC);

    $totalAmount = 0;

    $totalAmount = 0;
    foreach ($Shopping_Cart as $item):
        $totalAmount += $item['Selling_Price'] * $item['Quantity'];
    endforeach;
    if (!empty($_POST)) {
        $Order_Insert = " INSERT INTO Orders (User_ID, Order_TotalAmount, Order_ShippingAddress, Order_PaymentMethod, Order_PhoneNumber, Order_Notes) 
                          VALUES (:User_ID, :Order_TotalAmount, :Order_ShippingAddress, :Order_PaymentMethod, :Order_PhoneNumber, :Order_Notes)";

        $pdostmt = $connexion->prepare($Order_Insert);
        $pdostmt->execute([
            ':User_ID' => $User_ID,
            ':Order_TotalAmount' => $totalAmount,
            ':Order_ShippingAddress' => $_POST['Order_ShippingAddress'],
            ':Order_PaymentMethod' => $_POST['Order_PaymentMethod'],
            ':Order_PhoneNumber' => $_POST['Order_PhoneNumber'],
            ':Order_Notes' => $_POST['Order_Notes']
        ]);

        $orderID = $connexion->lastInsertId();

        $orderItemInsertQuery = " INSERT INTO OrderItems (OrderItem_Quantity , OrderItem_UnitPrice , Order_ID , Product_ID)
                                  VALUES (:OrderItem_Quantity , :OrderItem_UnitPrice , :Order_ID , :Product_ID)";

        $pdostmt = $connexion->prepare($orderItemInsertQuery);

        foreach ($Shopping_Cart as $item) {
            $pdostmt->execute([
                ':Order_ID' => $orderID,
                ':Product_ID' => $item['Product_ID'],
                ':OrderItem_Quantity' => $item['Quantity'],
                ':OrderItem_UnitPrice' => $item['Selling_Price']
            ]);
        }

        $removeAllFromCart = "DELETE FROM ShoppingCart WHERE User_ID = :User_ID";
        $pdostmt = $connexion->prepare($removeAllFromCart);
        $pdostmt->execute([':User_ID' => $User_ID]);
        header("Location: User_OrderStatus.php");
    }


    ?>
</head>



<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href="../"><img src="../Logo.png" alt="Logo" id="Logo"></a>

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
                    <a href="User_ShoppingCart.php"
                        class="flex items-center text-gray-300 hover:bg-green-700 px-3 py-2 rounded-md text-sm font-medium">🛒
                        Shopping Cart
                        <?php if ($Cart_Count > 0) { ?>
                            (<?php echo $Cart_Count ?>)
                        <?php } ?>
                    </a>
                    <a class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium"
                        href="User_Modify.php?id=<?php echo $User_ID; ?>&FullName=<?php echo urlencode($User_FullName); ?>">Currently
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
                        <?php if ($User['User_Role'] === 'Owner') { ?>
                            <div class="space-y-1">
                                <a href="../User/User_Management.php"
                                    class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑Users
                                    Dashboard</a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </nav>


    <div class="outer-container bg-gray-100 min-h-screen py-8">
        <div class="container mx-auto p-4 bg-white shadow-lg rounded-lg">
            <div class="content-wrapper pt-8">
                <h1 class="text-3xl font-bold mb-6 text-blue-800">Your Shopping Cart</h1>
                <a href="User_OrderStatus.php" class="text-blue-600 hover:underline mb-4 inline-block">Pending
                    Orders</a>
                <?php if ($Shopping_Cart) { ?>
                    <section class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                            <thead>
                                <tr class="bg-gray-200 text-left">
                                    <th class="px-6 py-3">Product</th>
                                    <th class="px-6 py-3">Price per Unit</th>
                                    <th class="px-6 py-3">Quantity</th>
                                    <th class="px-6 py-3">Picture</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($Shopping_Cart as $item): ?>
                                    <tr class="hover:bg-gray-100">
                                        <td class="px-6 py-4"><?php echo $item['Product_Name']; ?></td>
                                        <td class="px-6 py-4"><?php echo formatNumber($item['Selling_Price']); ?> Dhs</td>
                                        <td class="px-6 py-4"><b><?php echo $item['Quantity']; ?></b></td>
                                        <td class="px-6 py-4">
                                            <img src="../Product/<?php echo $item['Product_Picture']; ?>" alt="Product Image"
                                                class="h-20 w-20 object-cover rounded-lg">
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="User_Delete_Item_ShoppingCart.php?id=<?php echo $item["CartItem_ID"]; ?>"
                                                class="text-red-600 hover:underline"><box-icon type='solid' color="#dd0d1e" name='trash-alt'></box-icon></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gray-200">
                                    <td class="px-6 py-4" colspan="4">Total Amount:</td>
                                    <td class="px-6 py-4 font-bold"><?php echo formatNumber($totalAmount); ?> Dhs</td>
                                </tr>
                            </tfoot>
                        </table>
                        <div class="mt-6 flex space-x-4">
                            <?php if (!empty($Shopping_Cart)): ?>
                                <button id="orderNowBtn"
                                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">Order
                                    Now</button>
                                <a href="User_Delete_Item_ShoppingCart.php?id=clearAllCart"
                                    class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Clear
                                    Cart</a>
                            <?php endif; ?>
                        </div>
                        <section id="orderForm" class="hidden-form mt-6 bg-white rounded-lg shadow-md p-6">
                            <form action="" method="POST">
                                <div class="mb-4">
                                    <label for="Order_ShippingAddress" class="block text-gray-700 font-bold mb-2">Shipping
                                        Address</label>
                                    <textarea name="Order_ShippingAddress"
                                        class="border border-gray-300 p-3 w-full rounded-lg"
                                        placeholder="Address where you'd like your items delivered" required></textarea>
                                </div>
                                <div class="mb-4">
                                    <label for="Order_PaymentMethod" class="block text-gray-700 font-bold mb-2">Your Payment
                                        Method</label>
                                    <select name="Order_PaymentMethod" class="border border-gray-300 p-3 w-full rounded-lg">
                                        <option value="Credit Card">Credit Card</option>
                                        <option value="PayPal">PayPal</option>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cash on Delivery">Cash on Delivery</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label for="Order_PhoneNumber" class="block text-gray-700 font-bold mb-2">Your Phone
                                        Number</label>
                                    <input type="tel" name="Order_PhoneNumber"
                                        class="border border-gray-300 p-3 w-full rounded-lg"
                                        placeholder="Example: 0714876397" pattern="^([0-9]{2}){4}[0-9]{2}$" required>
                                </div>
                                <div class="mb-4">
                                    <label for="Order_Notes" class="block text-gray-700 font-bold mb-2">Notes</label>
                                    <textarea name="Order_Notes" class="border border-gray-300 p-3 w-full rounded-lg"
                                        placeholder="(Optional) : Any additional info you'd like to request/provide regarding your order."></textarea>
                                </div>
                                <div class="flex justify-end space-x-4">
                                    <button type="submit"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">Send
                                        out the order</button>
                                    <button type="reset"
                                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">Clear</button>
                                </div>
                            </form>
                        </section>
                    </section>
                <?php } else { ?>
                    <h1 class="text-xl text-gray-700 CC" id="Empty_Cart">Your Shopping Cart is Empty.<br>Check Out Our
                        Products <a class="text-blue-600 hover:underline mb-4 inline-block" href=".././">Here</a>.</h1>
                <?php } ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const orderNowBtn = document.getElementById("orderNowBtn");
            const orderForm = document.getElementById("orderForm");

            orderNowBtn.addEventListener("click", function () {
                if (orderForm.classList.contains("hidden-form")) {
                    orderForm.classList.remove("hidden-form");
                    orderForm.classList.add("visible-form");
                    orderNowBtn.textContent = "Collapse";
                } else {
                    orderForm.classList.remove("visible-form");
                    orderForm.classList.add("hidden-form");
                    orderNowBtn.textContent = "Order Now";
                }
            });
        });
    </script>

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
    .CC {
        margin-left: 40%;
        margin-top: 20%;
    }

    .hidden-form {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.5s ease-in-out, padding 0.5s ease-in-out;
        padding: 0;
    }

    .visible-form {
        max-height: 1000px;
        /* Adjust based on content */
        overflow: hidden;
        padding: 16px;
        /* Same padding as the form content */
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

    .product-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        max-width: 100%;

        display: inline-block;
        max-height: 1.2em;

    }
</style>