<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Shopping Cart</title>
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
        $User_FullName = $row['User_FirstName']. ' ' .  $row['User_LastName'];

        if ($User_Role === 'Owner') {
            $showUserManagement = true ;
        } else {
            $showUserManagement = false ;
        }


        if ($User_Role !== 'Owner' && $User_Role !== 'Admin' && $User_Role !== 'Client') {
            header("Location: ../User/User_Unauthorized.html");
            exit;
        }
    };

    $Shopping_Query = " SELECT sc.CartItem_ID, sc.Product_ID, sc.Quantity, p.Product_Name, p.Selling_Price, p.Product_Picture
                        FROM ShoppingCart sc
                        JOIN Products p ON sc.Product_ID = p.Product_ID
                        WHERE sc.User_ID = :User_ID";
    $pdostmt = $connexion->prepare($Shopping_Query);
    $pdostmt->execute([':User_ID' => $User_ID]);
    $Shopping_Cart = $pdostmt->fetchAll(PDO::FETCH_ASSOC);

    ?>

    <style>
        #User_Management {
            margin-left: 90%;
            justify-content: space-evenly;
            border: black 1px solid;
        }
    </style>

</head>

<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-6">Your Shopping Cart</h1>
    <div id="User_Management" class="mb-6">
        <span class="mr-2">Currently Logged as :
            <span class="font-bold"><?php echo $User_FullName ?></span> (<span class="font-bold"><?php echo $User_Role ?></span>)
        </span>
        <?php if ($showUserManagement): ?>
            <div class="mt-2">
                <a href="../User/User_Management.php" class="text-blue-500 hover:underline">User Management</a>
            </div>
        <?php endif; ?>
        <a href="../User/User_Logout.php" class="text-blue-500 hover:underline">Logout</a>
    </div>
    <a href="../Product/Products_List.php" class="text-blue-500 hover:underline mb-4 inline-block">Products List</a>
    <section class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-200 text-left">
                    <th class="px-4 py-2">Product ID</th>
                    <th class="px-4 py-2">Product Name</th>
                    <th class="px-4 py-2">Price per Unit</th>
                    <th class="px-4 py-2">Quantity</th>
                    <th class="px-4 py-2">Product Picture</th>
                    <th class="px-4 py-2">Options</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalAmount = 0;
                foreach ($Shopping_Cart as $item):
                    $totalAmount += $item['Selling_Price'] * $item['Quantity']; // Calculate total amount ?>
                    <tr class="hover:bg-gray-100">
                        <td class="px-4 py-2"><?php echo $item['Product_ID']; ?></td>
                        <td class="px-4 py-2"><?php echo $item['Product_Name']; ?></td>
                        <td class="px-4 py-2"><?php echo $item['Selling_Price']; ?></td>
                        <td class="px-4 py-2"><?php echo $item['Quantity']; ?></td>
                        <td class="px-4 py-2">
                            <img src="../Product/<?php echo $item['Product_Picture']; ?>" alt="Product Image"
                                class="h-20 w-20 object-cover">
                        </td>
                        <td class="px-4 py-2">
                            <a href="User_Delete_Item_ShoppingCart.php?id=<?php echo $item["CartItem_ID"]; ?>"
                                class="text-blue-500 hover:underline">Remove</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-200">
                    <td class="px-4 py-2" colspan="5">Total Amount:</td>
                    <td class="px-4 py-2 font-bold"><?php echo $totalAmount; ?> Dhs</td>
                </tr>
            </tfoot>
        </table>
        <div class="mt-4">
        <a href="User_Delete_Item_ShoppingCart.php?id=clearAllCart" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-700">Clear Cart</a>

        </div>
    </section>
</body>


</html>