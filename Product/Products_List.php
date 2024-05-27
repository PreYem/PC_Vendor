<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="Product_List.js"></script>
    <link href="../output.css" rel="stylesheet">
    <link rel="icon" href="../Logo.png" type="image/x-icon">

    <title>List of Products</title>
    <?php



    include_once ("../DB_Connexion.php"); // Include database connection at the beginning
    
    session_start(); // Start or resume existing session
    

    // Check if user is logged in and has the appropriate role
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
        // User is not logged in, redirect to login page
        header("Location: ../User/User_SignIn.php");
        exit; // Ensure script stops after redirection
    }

    // Retrieve the user's role from the database based on User_ID stored in session
    $userId = $_SESSION['User_ID'];
    $query = "SELECT User_Role, User_Username , User_FirstName , User_LastName FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);



    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];
        $User_Username = $row['User_Username'];
        $User_FullName = $row['User_FirstName'] . ' ' . $row['User_LastName'];

        if ($userRole === 'Owner') {
            $showUserManagement = true; // Flag to show the "User Management" button/link
        } else {
            $showUserManagement = false; // Hide the "User Management" button/link for other roles
        }

        // Check if the user has the required role (Owner or Admin) to access this page
        if ($userRole !== 'Owner' && $userRole !== 'Admin') {
            // User does not have sufficient permissions, redirect to unauthorized page
            header("Location: ../User/User_Unauthorized.html");
            exit; // Ensure script stops after redirection
        }
    }
    ;

    $pdostmt = null; // Initialize $pdostmt to null
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            if ($_POST['action'] == 'toggle_visibility') {
                if (isset($_POST['selected_products'])) {
                    $selectedProducts = $_POST['selected_products'];
                    $visibility = ($_POST['visibility'] == 'Visible') ? 'Visible' : 'Invisible';
                    if (!empty($selectedProducts)) { // Check if there are selected products
                        $query = "UPDATE Products SET Product_Visibility = :visibility WHERE Product_ID IN (" . implode(",", $selectedProducts) . ")";
                        $pdostmt = $connexion->prepare($query);
                        $pdostmt->execute(array(':visibility' => $visibility));
                    }
                    header("Location: Products_List.php");
                    exit;
                }
            } elseif ($_POST['action'] == 'delete') {
                if (isset($_POST['selected_products'])) {
                    $selectedProducts = $_POST['selected_products'];
                    if (!empty($selectedProducts)) {
                    }
                    header("Location: Products_List.php");
                    exit;
                }
            }
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action']) && $_POST['action'] == 'delete') {
            if (isset($_POST['selected_products'])) {
                $selectedProducts = json_decode($_POST['selected_products']);
                if (!empty($selectedProducts)) {
                    $placeholders = implode(',', array_fill(0, count($selectedProducts), '?'));
                    $query = "DELETE FROM Products WHERE Product_ID IN ($placeholders)";
                    $pdostmt = $connexion->prepare($query);
                    $pdostmt->execute($selectedProducts);
                }
                header("Location: Products_List.php");
                exit;
            }
        }
    }

    $Order_Status = 'Cancelled by User';
    $Order_Pending = "SELECT Order_ID FROM Orders WHERE User_ID = :userId AND Order_Status != :Order_Status ";
    $pdostmt = $connexion->prepare($Order_Pending);
    $pdostmt->execute([':userId' => $userId, ':Order_Status' => $Order_Status]);

    // Fetch the number of rows
    $Order_Count = $pdostmt->rowCount();

    // If you need to fetch the data as well
    $orders = $pdostmt->fetchAll(PDO::FETCH_ASSOC);


    $query = "SELECT P.Product_ID, P.Date_Created, P.Product_Name, C.Category_Name, S.SubCategory_Name, M.Manufacturer_Name, P.Selling_Price, P.Buying_Price , 
                P.Product_Quantity, P.Product_Picture, P.Product_Desc, P.Product_Visibility
                FROM Products P 
                JOIN Categories C ON P.Category_ID = C.Category_ID 
                JOIN Manufacturers M ON P.Manufacturer_ID = M.Manufacturer_ID
                JOIN SubCategories S ON P.SubCategory_ID = S.SubCategory_ID

                ORDER BY P.Product_ID";

    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute();

    $current_time = time(); // This will give you the current time as a Unix timestamp
    
    $Shopping_Query = "SELECT sc.CartItem_ID, sc.Product_ID, sc.Quantity, p.Product_Name, p.Selling_Price, p.Product_Picture
    FROM ShoppingCart sc
    JOIN Products p ON sc.Product_ID = p.Product_ID
    WHERE sc.User_ID = :User_ID";

    $pdostmt_shopping = $connexion->prepare($Shopping_Query);
    $pdostmt_shopping->execute([':User_ID' => $userId]);
    $Shopping_Cart = $pdostmt_shopping->fetchAll(PDO::FETCH_ASSOC);

    $Cart_Count = $pdostmt_shopping->rowCount();



    ?>


    <style>
        tr td {
            border: 2px black solid;
            text-align: center;
        }

        a,
        button {
            border: 3px solid purple
        }

        .Adjust {
            visibility: visible;
        }

        #UserFirstLastName {
            color: lightskyblue;
        }

        #User_Management {
            margin-left: 90%;
            justify-content: space-evenly;
        }

        #HeaderPage {
            border: 2px solid black;
            padding: 20px;
            position: fixed;
            width: 100%;
            /* Add top padding to push elements down */
            top: 0;
            /* Adjust z-index if necessary */
            z-index: 1000;
            /* Increase if needed to ensure navbar is above other content */
            background-color: #7d46a1;
        }

        #PageContent {
            /* Add margin-top equal to the height of the navbar */
            margin-top: 180px;
            /* Adjust as per your navbar height */
        }
    </style>

</head>

<body style="padding: 0px; background-color : gray">

    <div id="HeaderPage" style="">
        <a href="../index.php" style="background-color: Green">Main Page</a>
        <a href="Products_Add.php" style="background-color: Blue; padding: 0px 4px; display: inline-block; ">+</a>
        <a href="../User/User_ShoppingCart.php">Shopping Cart [<?php echo $Cart_Count ?>]</a>

        <?php if ($Order_Pending) { ?> <a href="../User/User_OrderStatus.php">You have
                <?php echo $Order_Count ?> Pending Order(s)</a> <?php } ?>





        <select id="sortSelect" onchange="sortProducts()">
            <option value="none">Sort By</option>
            <option value="name_asc">Name (A to Z)</option>
            <option value="name_desc">Name (Z to A)</option>
            <option value="price_asc">Price (Low to High)</option>
            <option value="price_desc">Price (High to Low)</option>
        </select>

        <input type="text" id="searchInput" onkeyup="searchProducts()" placeholder="Search for products...">
        <div id="User_Management">
            <span>Currently Logged as :
                <span id="UserFirstLastName"><b><?php echo $User_FullName ?></b></span>
                <b>(<?php echo $userRole ?>)</b>
            </span>
            <?php

            if ($showUserManagement) {
                echo '<div>';
                echo '<a href="../User/User_Management.php">User Management</a>';
                echo '</div>';
            }
            ?>
            <a href="../User/User_Logout.php">Logout</a>
        </div>
    </div>


    <br><br>

    

    <div id="PageContent">
    <h1> List of Products</h1><br>
        <div class="Adjust">
            <button type="button" onclick="toggleVisibilityForSelected('Visible')"
                class="bg-blue-500 text-white px-4 py-2 rounded mr-2" id="showButton">Show Selected</button>
            <button type="button" onclick="toggleVisibilityForSelected('Invisible')"
                class="bg-gray-500 text-white px-4 py-2 rounded mr-2" id="hideButton">Hide Selected</button>


            <button type="button" onclick="deleteSelectedProducts()"
                class="bg-red-500 text-white px-4 py-2 rounded mr-2 disabled:opacity-50 cursor-pointer"
                id="deleteButton">Delete
                Selected</button>

        </div>

        <form id="productForm" method="POST">
            <table id="productTable" style="width: 99%; border: 2px solid black;">
                <tr>
                    <td>Select All <input type="checkbox" id="masterCheckbox" onclick="toggleMasterCheckbox()">
                    <td><b>Product ID</b></td>
                    <td>Product Name</td>
                    <td>Category</td>
                    <td>Sub Category</td>
                    <td>Manufacturer Name</td>
                    <td>Buying Price</td>
                    <td>Selling Price</td>
                    <td>Quantity</td>
                    <td>Spec Count</td>
                    <td>Product Picture</td>
                    <td>Product Description</td>
                    <td>Visibility</td>
                    <td>Added On (GMT +1)</td>
                    <td>Options</td>
                    <br>
                    </td>
                </tr>
                <?php
                if ($pdostmt) {
                    while ($ligne = $pdostmt->fetch(PDO::FETCH_ASSOC)):
                        $queryCountSpecs = "SELECT COUNT(*) FROM ProductSpecifications WHERE Product_ID = :productId";
                        $pdostmtCountSpecs = $connexion->prepare($queryCountSpecs);
                        $pdostmtCountSpecs->execute(["productId" => $ligne["Product_ID"]]);
                        $specCount = $pdostmtCountSpecs->fetchColumn();
                        $product_status = '';


                        $currentDateTimeUTC = new DateTime();
                        $currentDateTimeUTC->setTimezone(new DateTimeZone('UTC')); // Set initial time zone to UTC
                

                        $date_created_time = new DateTime($ligne['Date_Created'], new DateTimeZone('UTC')); // Create DateTime object from Date_Created in UTC
                

                        $time_difference = $currentDateTimeUTC->getTimestamp() - $date_created_time->getTimestamp(); // Difference in seconds
                        $time_difference_minutes = round($time_difference / 60) + 60; // Convert to minutes
                

                        // 1 Hour = 60 Minutes
                        // 1 Day = 720 Minutes
                        // 1 Week = 10080 Minutes
                        // 1 Month = 43800 Minutes
                

                        // Determine product status based on time difference
                        if ($time_difference_minutes < 1) {
                            $product_status = 'NEW PRODUCT <br>'; // Product is considered new
                        } else {
                            $product_status = ''; // Product is not considered new
                        }





                        ?>
                        <tr>
                            <td><input type="checkbox" id="productCheckbox" name="selected_products[]"
                                    value="<?php echo $ligne["Product_ID"] ?>"></td>
                            <td>
                                <?php echo $ligne["Product_ID"] ?>
                            </td>
                            <td>
                                <span>
                                    <?php echo $product_status ?>
                                </span>
                                <?php echo $ligne["Product_Name"] ?>


                            </td>
                            <td>
                                <?php echo $ligne["Category_Name"] ?>
                            </td>
                            <td>
                                <?php echo $ligne["SubCategory_Name"] ?>
                            </td>
                            <td>
                                <?php echo $ligne["Manufacturer_Name"] ?>
                            </td>
                            <td>
                                <?php echo $ligne["Buying_Price"] ?>


                            </td>
                            <td>
                                <?php echo $ligne["Selling_Price"] ?>
                            </td>
                            <td>
                                <?php echo $ligne["Product_Quantity"] ?>
                            </td>
                            <td>
                                <?php echo $specCount ?>
                            </td>
                            <td>
                                <?php
                                $imagePath = $ligne["Product_Picture"];
                                if ($imagePath && file_exists($imagePath)) {
                                    echo '<img src="' . $imagePath . '" alt="Product Image" style="width: 100px; height: auto;">';
                                } else {
                                    echo 'No Image Found';
                                }
                                ?>
                            </td>

                            <td>
                                <?php echo $ligne["Product_Desc"] ?>
                            </td>
                            <td>
                                <?php echo ($ligne["Product_Visibility"] == 'Visible') ? 'ON' : 'OFF'; ?>
                            </td>
                            <td>
                                <?php echo date('Y-m-d', strtotime($ligne["Date_Created"])) . '<br><b> at </b><br>' . date('H:i:s', strtotime($ligne["Date_Created"])); ?>
                            </td>

                            <td style="width : 5%">
                                <a href="Products_Modify.php?id=<?php echo $ligne["Product_ID"] ?>">Edit</a>
                                <a href="Products_Delete.php?id=<?php echo $ligne["Product_ID"] ?>"
                                    onclick="return confirm('Are you sure you want to delete this product?\n*Disclaimer* : This action is irreversible')">Delete</a><br>
                                <a href="Add_To_Cart.php?id=<?php echo $ligne["Product_ID"]; ?>">Add to Cart
                                </a>


                        </tr>
                    <?php endwhile;
                } ?>
            </table>
            <br>





        </form>

    </div>

</body>

</html>