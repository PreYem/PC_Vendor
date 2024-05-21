<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Shopping Cart</title>
    <link href="../output.css" rel="stylesheet">


    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Function to populate the cart table with product details
            function populateCartTable(cartProducts) {
                // Get the table element by ID
                let table = $('#cartTable');

                // Iterate over each product in the cartProducts array
                cartProducts.forEach(function (product) {
                    // Create a new row for each product
                    let row = $('<tr></tr>');

                    // Populate the row with product details
                    row.append($('<td>' + product.productId + '</td>'));
                    row.append($('<td>' + product.quantity + '</td>')); // Display product quantity
                    row.append($('<td>' + product.name + '</td>'));
                    row.append($('<td><img src="' + product.picture + '" width="100"></td>'));
                    row.append($('<td>$' + product.price + '</td>'));

                    // Append the row to the table
                    table.append(row);
                });
            }

            // Retrieve cart data from local storage
            let cartProductsString = localStorage.getItem('cart_Products');
            let cartProducts = JSON.parse(cartProductsString);
            if (cartProductsString) {

                // Call function to populate the cart table with product details
                populateCartTable(cartProducts);


                $.ajax({
                    type: 'POST',
                    url: 'Shopping_Cart.php',
                    data: { cart_Products: cartProducts },
                    dataType: 'json',
                    success: function (response) {
                        // Handle successful response from server
                        if (response.productDetails) {
                            // Process productDetails returned from server
                            populateCartTable(response.productDetails);
                        }
                    },
                    error: function (xhr, status, error) {
                        // Log AJAX error for debugging
                        console.log('AJAX Error:', error);
                        console.log("Hello World")


                    }
                });



            }
        });
    </script>
    <?php


    include_once ("../DB_Connexion.php");

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_Products'])) {
        echo 'test';
        $cartProducts = $_POST['cart_Products'];

        $productDetails = [];


        foreach ($cartProducts as $product) {
            $productId = $product['productId'];


            $stmt = $pdo->prepare("SELECT Product_Name, Product_Picture, Selling_Price FROM Products WHERE Product_ID = :productId");
            $stmt->bindParam(':productId', $productId);
            $stmt->execute();


            $productData = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($productData) {

                $productDetails[] = [
                    'productId' => $productId,
                    'name' => $productData['Product_Name'],
                    'picture' => $productData['Product_Picture'],
                    'price' => $productData['Selling_Price'],
                    'quantity' => $product['quantity']
                ];
            }
        }



        echo json_encode(['productDetails' => $productDetails]);
    } else
        echo 'Erreur ';
    ?>
</head>

<body>
    <h1>Shopping Cart</h1>
    <a href="Products_List.php" style="border : 2px black dotted ">Product List</a>
    <table id="cartTable" border="2">
        <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Quantity</th>
                <th>Product Name</th>
                <th>Product Picture</th>
                <th>Selling Price</th>
            </tr>
        </thead>
        <tbody>
            <!-- Cart products will be dynamically populated here -->
        </tbody>
    </table>
</body>

</html>