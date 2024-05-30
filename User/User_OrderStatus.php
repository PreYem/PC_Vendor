<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Order Status</title>
    <?php

    function formatNumber($number)
    {
        return number_format($number, 0, '.', ' ');
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


        if ($User_Role !== 'Owner' && $User_Role !== 'Admin' && $User_Role !== 'Client') {
            header("Location: ../User/User_Unauthorized.html");
            exit;
        }
    }
    ;

    $bigQuery = "SELECT p.Product_ID, p.Product_Name, p.Product_Picture, o.Order_ID, o.Order_Date, o.Order_TotalAmount, o.Order_Status, oi.OrderItem_Quantity, 
                oi.OrderItem_UnitPrice, u.User_ID
                FROM Orders o
                INNER JOIN OrderItems oi ON o.Order_ID = oi.Order_ID
                INNER JOIN Products p ON oi.Product_ID = p.Product_ID
                INNER JOIN Users u ON o.User_ID = u.User_ID;";
    $pdostmt = $connexion->prepare($bigQuery);
    $pdostmt->execute();
    $results = $pdostmt->fetchAll(PDO::FETCH_ASSOC);

    // Group the results by Order_ID
    $orders = [];
    foreach ($results as $row) {
        $orders[$row['Order_ID']]['Order_Date'] = $row['Order_Date'];
        $orders[$row['Order_ID']]['Order_TotalAmount'] = $row['Order_TotalAmount'];
        $orders[$row['Order_ID']]['Order_Status'] = $row['Order_Status'];
        $orders[$row['Order_ID']]['User_ID'] = $row['User_ID'];
        $orders[$row['Order_ID']]['Products'][] = [
            'Product_Name' => $row['Product_Name'],
            'Product_Picture' => $row['Product_Picture'],
            'OrderItem_Quantity' => $row['OrderItem_Quantity'],
            'OrderItem_UnitPrice' => $row['OrderItem_UnitPrice']
        ];
    }
    ?>
    <style>
        .order-details {
            opacity: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
        }

        .order-details.active {
            opacity: 1;
            max-height: 1000px;
            /* Adjust to a value larger than your content's height */
        }
    </style>

<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">Your Pending Orders</h1>
    <a href="../Product/Products_List.php" class="text-blue-500 hover:underline mb-4 inline-block">Products List</a>
    <section>
    <?php foreach ($orders as $orderId => $order):
        $Date = date('Y-m-d', strtotime($order['Order_Date'])) . ' <b>at</b> ' . date('H:i:s', strtotime($order['Order_Date']));
        ?>
        <div class="bg-white shadow-md rounded-lg p-6 mb-6 order-container <?php echo ($order['Order_Status'] === 'Cancelled by User') ? 'bg-red-100' : ''; ?>">
            <h4 class="text-lg font-semibold mb-4"><b>Order Number : </b><?php echo $orderId; ?></h4>
            <p class="text-gray-600 mb-2"><b>Order made on : </b><?php echo $Date; ?></p>
            <!-- Details section -->
            <div class="order-details">
                <?php foreach ($order['Products'] as $product): ?>
                    <div class="flex items-center border-b py-4">
                        <div class="flex-shrink-0 mr-4">
                            <img src="../Product/<?php echo $product['Product_Picture']; ?>" alt="Product Image"
                                class="w-16 h-16 rounded-lg">
                        </div>
                        <div>
                            <p class="font-semibold"><?php echo $product['Product_Name']; ?></p>
                            <p class="text-gray-600 mb-1">Quantity : <?php echo $product['OrderItem_Quantity']; ?></p>
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
            <p class="text-gray-600 mt-4 status-paragraph"><b>Status : </b><?php echo $order['Order_Status']; ?></p>
            <p class="text-gray-600"><b>Total Amount : </b><?php echo formatNumber($order['Order_TotalAmount']); ?> Dhs</p>
            <?php if ($order['Order_Status'] == 'Pending') { ?>

                <a href="User_CancelOrder.php?id=<?php echo $orderId; ?>" class="inline-block bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out transform hover:scale-105">
Cancel Order
</a>


            <?php } ?>
        </div>


    <?php endforeach; ?>
</section>

    <!-- JavaScript for expanding/collapsing -->
    <script>
        document.querySelectorAll('.order-container').forEach(container => {
            container.addEventListener('click', function () {
                const details = this.querySelector('.order-details');
                details.classList.toggle('active');
            });
        });
    </script>
</body>



</html>