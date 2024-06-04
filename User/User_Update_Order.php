<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Your Order 📦 | PC Vendor</title>
    <script src="https://unpkg.com/boxicons@2.1.4/dist/boxicons.js"></script>

    <?php
    include_once ("../DB_Connexion.php");
    session_start();



    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

        header("Location: ../.");
        exit;
    }

    $User_ID = $_SESSION['User_ID'];
    $query = "SELECT User_Role, User_Username , User_FirstName , User_LastName FROM Users WHERE User_ID = :User_ID";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':User_ID' => $User_ID]);

    if ($User = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $User_Role = $User['User_Role'];
        $User_Username = $User['User_Username'];
        $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];

        if ($User_Role === 'Client') {
            header("Location: ../.");
            exit;
        }
    }
    ;

    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
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
    ;

    $Current_User = $_SESSION['User_ID'];

    if (isset($_GET['id'])) {
        $Order_ID = $_GET['id'];
        $bigQuery = "SELECT p.Product_ID, p.Product_Name, p.Product_Picture, o.Order_ID, o.Order_Date, o.Order_TotalAmount, o.Order_Status, 
                    oi.OrderItem_Quantity, oi.OrderItem_UnitPrice, o.Order_ShippingAddress, o.Order_PaymentMethod, o.Order_PhoneNumber, o.Order_Notes,
                    u.User_ID , u.User_Username, u.User_FirstName , u.User_LastName , u.User_Email
                 FROM Orders o
                 INNER JOIN OrderItems oi ON o.Order_ID = oi.Order_ID
                 INNER JOIN Products p ON oi.Product_ID = p.Product_ID
                 INNER JOIN Users u ON o.User_ID = u.User_ID
                WHERE o.Order_ID = $Order_ID ";

        $pdostmt = $connexion->prepare($bigQuery);
        $pdostmt->execute([]);
        $Orders = $pdostmt->fetchAll(PDO::FETCH_ASSOC);


        // Group the Orders by Order_ID
        $orders = [];
        foreach ($Orders as $Order) {
            $orders[$Order['Order_ID']]['Order_Date'] = $Order['Order_Date'];
            $orders[$Order['Order_ID']]['Order_TotalAmount'] = $Order['Order_TotalAmount'];
            $orders[$Order['Order_ID']]['Order_Status'] = $Order['Order_Status'];
            $orders[$Order['Order_ID']]['User_ID'] = $Order['User_ID'];
            $orders[$Order['Order_ID']]['User_FirstName'] = $Order['User_FirstName'];
            $orders[$Order['Order_ID']]['User_LastName'] = $Order['User_LastName'];
            $orders[$Order['Order_ID']]['User_Username'] = $Order['User_Username'];
            $orders[$Order['Order_ID']]['Order_ShippingAddress'] = $Order['Order_ShippingAddress'];
            $orders[$Order['Order_ID']]['Order_PaymentMethod'] = $Order['Order_PaymentMethod'];
            $orders[$Order['Order_ID']]['Order_PhoneNumber'] = $Order['Order_PhoneNumber'];
            $orders[$Order['Order_ID']]['Order_Notes'] = $Order['Order_Notes'];
            $orders[$Order['Order_ID']]['User_Email'] = $Order['User_Email'];


            $orders[$Order['Order_ID']]['Products'][] = [
                'Product_Name' => $Order['Product_Name'],
                'Product_Picture' => $Order['Product_Picture'],
                'OrderItem_Quantity' => $Order['OrderItem_Quantity'],
                'OrderItem_UnitPrice' => $Order['OrderItem_UnitPrice']
            ];
        }

        if (isset($_POST['Order_Status'])) {
            $Order_Status = $_POST['Order_Status'];

            $Update_Status = "UPDATE Orders SET Order_Status = '$Order_Status' WHERE Order_ID = $Order_ID";
            $pdostmt = $connexion->prepare($Update_Status);
            $pdostmt->execute();


            require ("../script.php");


            $User_Email = $Order['User_Email'];
            $Email_Subject = "Your Order Status has been updated | PC Vendor";

            $Email_Message = "
            <html>
            <head>
            <style>
            .button {
             display: inline-block;
             padding: 10px 20px;
             background-color: #007bff;
             color: black;
             text-decoration: underline;
             border-radius: 5px;
             transition: background-color 0.3s ease;
            }
 
            .button:hover {
             background-color: #0056b3;
             }
             </style>
                 <body>
                 <h1>Your order number $Order_ID has been updated to : $Order_Status </h1>
                 <h2>You can checkout your order status <p><a class='button' href='http://localhost/PC_Vendor/User/User_PendingOrders.php'>Here.</a></p></h2
                 </body>";

                 
            $response = sendMail($User_Email, $Email_Subject, $Email_Message);



            $_SESSION['Order_Update'] = "Order number : (" . $Order_ID . ") Has been updated to " . $Order_Status;
            

            header("Location: User_GlobalOrders.php");
            exit;

        }


    } 


    #--------------------------------------------------------------
    

    ?>
</head>



<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href="../"><img src="../Logo.png" alt="Logo" id="Logo"></a>

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
                            <a href="../?id=<?php echo $Category['Category_ID'] ?>&Type=Category&Name=<?php echo str_replace(' ', '', $Category['Category_Name']) ?>"
                                class="px-3 py-2 hover:bg-gray-700"><?php echo $Category['Category_Name']; ?></a>
                            <?php if (!empty($SubCategories)): ?>
                                <div class="category-dropdown absolute top-full left-0 mt-1 bg-gray-800 rounded shadow-md p-2"
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


    <div class="outer-container bg-gray-100 min-h-screen py-8">
        <div class="container mx-auto p-4 bg-white shadow-lg rounded-lg">
            <div class="content-wrapper pt-8">

                <?php if ($Orders) { ?>
                    <h1 class="text-3xl font-bold mb-6 text-blue-800">Updating Order Number
                        : <?php echo $Order['Order_ID']; ?></h1>
                    <section>
                        <form method="POST">
                            <?php foreach ($orders as $orderId => $order):
                                $Date = date('Y-m-d', strtotime($order['Order_Date'])) . ' <b>at</b> ' . date('H:i:s', strtotime($order['Order_Date']));
                                ?>
                                <div class="bg-white shadow-md rounded-lg p-6 mb-6 order-container <?php
                                echo ($order['Order_Status'] === 'Cancelled by User' || $order['Order_Status'] === 'Cancelled by Management') ? 'bg-red-100' : '';
                                ?>">
                                    <p class="text-gray-600"><b>Order made on : </b><?php echo $Date; ?></p>
                                    <p class="text-gray-600 mb-4 status-paragraph">Client :
                                        <b><?php echo $order['User_FirstName'] . ' ' . $order['User_LastName'];
                                        ; ?></b>
                                    </p>
                                    <!-- Details section -->
                                    <div>
                                        <p class="text-gray-600 mt-4 status-paragraph">Username :
                                            <b><?php echo $order['User_Username']; ?></b>
                                        </p>
                                        <?php foreach ($order['Products'] as $product): ?>
                                            <div class="flex items-center border-b py-4">
                                                <div class="flex-shrink-0 mr-4">
                                                    <img src="../Product/<?php echo $product['Product_Picture']; ?>"
                                                        alt="Product Image" class="w-16 h-16 rounded-lg">
                                                </div>
                                                <div>
                                                    <p class="font-semibold"><?php echo $product['Product_Name']; ?></p>
                                                    <p class="text-gray-600 mb-1">Quantity :
                                                        <?php echo $product['OrderItem_Quantity']; ?>
                                                    </p>
                                                    <p class="text-gray-600 mb-1">Price Per Unit :
                                                        <?php echo formatNumber($product['OrderItem_UnitPrice']); ?> Dhs
                                                    </p>
                                                    <p class="text-gray-600 mb-1">Total:
                                                        <?php echo formatNumber($product['OrderItem_Quantity'] * $product['OrderItem_UnitPrice']); ?>
                                                        Dhs
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>

                                    </div>

                                    <p class="text-gray-600 status-paragraph mt-4"><b>Shipping Address :
                                        </b><?php echo $Order['Order_ShippingAddress']; ?>

                                    <p class="text-gray-600 status-paragraph"><b>Payment Method :
                                        </b><?php echo $Order['Order_PaymentMethod']; ?>
                                    </p>
                                    <p class="text-gray-600 status-paragraph"><b>Phone Number :
                                        </b><?php echo $Order['Order_PhoneNumber']; ?>
                                    </p>
                                    <p class="text-gray-600 status-paragraph"><b>Client Notes :</b>
                                        <?php echo $Order['Order_Notes']; ?>

                                    </p>
                                    <p class="text-gray-600 status-paragraph flex items-center">
                                        <!-- Added 'flex items-center' for inline display -->
                                        <b>Status : </b> <!-- Moved 'Status' outside the select box -->
                                        <select name="Order_Status" id="User_Role"
                                            class="mt-1 block w-48 py-2 px-3 border border-gray-300 ml-2 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <!-- Changed width to 'w-48' for a narrower input -->
                                            <?php
                                            $Cases = [
                                                'Pending',
                                                'Processing',
                                                'Shipped',
                                                'Delivered',
                                                'On Hold',
                                                'Refunded',
                                                'Returned',
                                                'Completed',
                                                'Cancelled by User',
                                                'Cancelled by Management'
                                            ];

                                            foreach ($Cases as $Case) {
                                                if ($Order['Order_Status'] === $Case) {
                                                    echo '<option selected>' . $Case . '</option>';
                                                } else {
                                                    echo '<option>' . $Case . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </p>


                                    <p class="text-gray-600 mb-4"><b>Total Amount :
                                        </b><?php echo formatNumber($order['Order_TotalAmount']); ?> Dhs</p>


                                    <button type="submit"
                                        class="inline-block bg-blue-400 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105"
                                        id="Submit">Save Changes</button>
                                    <a href="User_CancelOrder.php?id=<?php echo $orderId; ?>"
                                        class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105">Cancel
                                        Order</a>

                                </div>


                            <?php endforeach; ?>
                    </section>
                    </form>

                <?php } else { ?>
                    <h1 class="text-xl text-gray-700 CC" id="Empty_Cart">There Are No Pending Orders At Moment, <br>Check
                        Again Later.</h1>
                <?php } ?>
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
<style>
    .order-details {
        opacity: 0;
        max-height: 0;
        overflow: ;
        transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    }

    .order-details.active {
        opacity: 1;
        max-height: 1000px;
        /* Adjust to a value larger than your content's height */
    }

    .CC {
        margin-left: 40%;
        margin-top: 20%;
    }

    .-form {
        max-height: 0;
        overflow: ;
        transition: max-height 0.5s ease-in-out, padding 0.5s ease-in-out;
        padding: 0;
    }

    .-form {
        max-height: 1000px;
        /* Adjust based on content */
        overflow: ;
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
        overflow: ;
        text-overflow: ellipsis;
        white-space: nowrap;

        max-width: 100%;

        display: inline-block;
        max-height: 1.2em;

    }
</style>