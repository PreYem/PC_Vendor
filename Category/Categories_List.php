<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category List | PC Vendor</title>
    <link rel="icon" href="../Logo.png" type="image/x-icon">


    <?php
    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
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

    if ($User = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $User_Role = $User['User_Role'];
        $User_Username = $User['User_Username'];
        $User_FullName = $User['User_FirstName'] . ' ' . $User['User_LastName'];

        if ($User_Role === 'Owner') {
            $showUserManagement = true;
        } else {
            $showUserManagement = false;
        }


        if ($User_Role == 'Client') {
            header("Location: ../User/User_Unauthorized.html");
            exit;
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
    ;

    $Categories = "SELECT Category_ID, Category_Name, Category_Desc FROM Categories";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);

    ?>


</head>


<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href=".././"><img src="../Logo.png" alt="Logo" id="Logo"></a>

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
                                    class="block bg-gray-700 hover:bg-blue-600 px-3 py-2 rounded-md text-sm font-medium text-gray-300 transition duration-300">🔑
                                    Users Dashboard</a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php endif; ?>
    </nav>



    <div class="container mx-auto p-6">
        <div class="content-wrapper">
            <h1 class="text-xl font-semibold mb-4">List of Categories</h1>
            <?php if (isset($_SESSION['Category_Add/Update'])) { ?>
                <span class="rounded-full" id="Category_Message"><?php echo $_SESSION['Category_Add/Update'];
                unset($_SESSION['Category_Add/Update']) ?></span>
            <?php } ?>
            <?php if (isset($_SESSION['Category_Delete'])) { ?>
                <span class="rounded-full bg-red-600" style="background-color : red" id="Category_Message">
                    <?php echo $_SESSION['Category_Delete'];
                    unset($_SESSION['Category_Delete']) ?>
                </span>

            <?php } ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-200">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-2 px-4 border-b text-left cursor-pointer" style="width : 5%"
                                onclick="sortTable(0)">ID
                                <span>🠻</span>
                            </th>
                            <th class="py-2 px-4 border-b text-left cursor-pointer" style="width : 15%"
                                onclick="sortTable(1)">Name
                                <span>🠻</span>
                            </th>
                            <th class="py-2 px-4 border-b text-left">Category Description</th>
                            <th class="py-2 px-4 border-b" style="width : 9% ">⚙️ Settings</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($Categories as $Category):

                            $Category_Desc = strlen($Category['Category_Desc']) > 100 ? substr($Category['Category_Desc'], 0, 100) . "..." : $Category['Category_Desc'];
                            ?>
                            <tr class="border-b hover:bg-gray-100">
                                <td class="py-2 px-4 text-left"><?php echo $Category['Category_ID']; ?></td>
                                <td class="py-2 px-4 text-left"><?php echo $Category['Category_Name']; ?></td>
                                <td class="py-2 px-4 text-left limited-text">
                                    <?php if (strlen($Category['Category_Desc'] > 100)) {
                                        echo $Category_Desc;
                                    } else {
                                        echo $Category['Category_Desc'];
                                    } ?>
                                </td>

                                <td class="py-2 px-4 text-left">
                                    <?php if ($Category['Category_Name'] !== 'Unspecified') { ?>

                                        <a href="Categories_Modify.php?id=<?php echo $Category['Category_ID']; ?>&Category_Name=<?php echo $Category['Category_Name']; ?>"
                                            class="bg-green-500 text-white py-1 px-2 rounded hover:bg-green-600 text-sm">⚙️</a>

                                        <a href="Categories_Delete.php?id=<?php echo $Category['Category_ID']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this Category?\n*Disclaimer* : This action is irreversible')"
                                            class="bg-red-500 text-white py-1 px-2 rounded hover:bg-red-600 text-sm">🗑️</a>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>


            </div>
        </div>
    </div>




    <script>
        window.addEventListener('DOMContentLoaded', function () {
            adjustContentMargin();
            resetForm();
        });

        window.addEventListener('resize', function () {
            adjustContentMargin();
        });

        function adjustContentMargin() {
            var navHeight = document.querySelector('nav').offsetHeight;
            document.querySelector('.content-wrapper').style.marginTop = navHeight + 'px';
        }
        function sortTable(columnIndex) {
            var table, rows, switching, i, x, y, shouldSwitch, direction, switchcount = 0;
            table = document.querySelector("table");
            switching = true;
            direction = "asc"; // Default sorting direction

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("td")[columnIndex];
                    y = rows[i + 1].getElementsByTagName("td")[columnIndex];

                    if (direction === "asc") {
                        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (direction === "desc") {
                        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }

                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                    switchcount++;
                } else {
                    if (switchcount === 0 && direction === "asc") {
                        direction = "desc";
                        switching = true;
                    }
                }
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            setTimeout(function () {
                var updatedProductSpans = document.querySelectorAll("#Category_Message");
                updatedProductSpans.forEach(function (span) {
                    span.classList.add("fade-out");
                });
            }, 2000);


            setTimeout(function () {
                var updatedProductSpans = document.querySelectorAll("#Category_Message");
                updatedProductSpans.forEach(function (span) {
                    span.classList.add("hidden");
                });
            }, 2700);
        });
    </script>

</body>

</html>
<style>
    #Category_Message {
        margin-left: 44%;
        border: round;
        background-color: Green;
        padding-bottom: 10%;
        opacity: 70%;
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
</style>