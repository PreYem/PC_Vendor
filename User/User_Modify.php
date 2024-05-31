<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">

    <title><?php
    if (isset($_GET['FullName'])) {
        $FullName = $_GET['FullName'];
    } else {
        $FullName = 'Account Modification';
    }
    ;
    echo $FullName
        ?> | PC Vendor</title>


    <?php
    session_start();
    include_once ("../DB_Connexion.php");

    if (isset($_GET['id'])) {
        if (isset($_SESSION['User_ID'])) {
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

        if ($User_ID != $_GET['id'] && $User_Role !== 'Owner') {
            header("Location: ../.");
            exit;
        }

    } else {
        header("Location: ../.");
        exit;
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

    $User_ID = $_GET['id'];
    $User_Query = "SELECT User_FirstName , User_Role, User_LastName , User_Phone , User_Country , User_Address , User_Email , User_Password FROM Users WHERE User_ID = :User_ID";
    $User_Statement = $connexion->prepare($User_Query);
    $User_Statement->execute([':User_ID' => $User_ID]);
    $User_Data = $User_Statement->fetch(PDO::FETCH_ASSOC);


    if (empty($User_Data)) {
        header("Location: ../.");
        exit;
    }

    $Owners = "SELECT User_ID FROM Users WHERE User_Role = 'Owner'";
    $pdostmt_owners = $connexion->prepare($Owners);
    $pdostmt_owners->execute();
    $owner_count = $pdostmt_owners->rowCount();



    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $User_FirstName = $_POST['User_FirstName'];
        $User_LastName = $_POST['User_LastName'];
        $User_Phone = $_POST['User_Phone'];
        $User_Country = $_POST['User_Country'];
        $User_Address = $_POST['User_Address'];
        $User_Email = $_POST['User_Email'];
        $User_Password = password_hash($_POST['User_Password'], PASSWORD_BCRYPT);
        if (empty($_POST['User_Role'])) {
            $User_Role = 'Owner';
        } else {
            $User_Role = $_POST['User_Role'];
        }


        if (!empty($_POST['User_Password'])) {
            $User_Data_Update = "UPDATE Users SET User_FirstName = :User_FirstName, User_LastName = :User_LastName, User_Phone = :User_Phone, 
            User_Country = :User_Country, User_Address = :User_Address, User_Email = :User_Email, User_Password = :User_Password , User_Role = :User_Role
            WHERE User_ID = :User_ID";

            $User_Statement_Update = $connexion->prepare($User_Data_Update);
            $User_Statement_Update->execute([
                ':User_ID' => $User_ID,
                ':User_FirstName' => $User_FirstName,
                ':User_LastName' => $User_LastName,
                ':User_Phone' => $User_Phone,
                ':User_Country' => $User_Country,
                ':User_Address' => $User_Address,
                ':User_Email' => $User_Email,
                ':User_Password' => $User_Password,
                ':User_Role' => $User_Role
            ]);

        } else {
            $User_Data_Update = "UPDATE Users SET User_FirstName = :User_FirstName, User_LastName = :User_LastName, User_Phone = :User_Phone, 
            User_Country = :User_Country, User_Address = :User_Address, User_Email = :User_Email , User_Role = :User_Role
            WHERE User_ID = :User_ID";

            $User_Statement_Update = $connexion->prepare($User_Data_Update);
            $User_Statement_Update->execute([
                ':User_ID' => $User_ID,
                ':User_FirstName' => $User_FirstName,
                ':User_LastName' => $User_LastName,
                ':User_Phone' => $User_Phone,
                ':User_Country' => $User_Country,
                ':User_Address' => $User_Address,
                ':User_Email' => $User_Email,
                ':User_Role' => $User_Role
            ]);
        }


        header("Location: User_Management.php");
        exit();
    }

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
                                Product List</a>
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
                            <a href="Category/SubCategories/SubCategories_Add.php"
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
            <h1 class="text-2xl font-bold mb-4 text-center">Account Modification</h1>
            <form action="" method="POST" onsubmit="passwordConfirmation(event)"
                class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Left Section -->
                <div class="space-y-4">

                    <div>
                        <label for="User_FirstName" class="block text-sm font-medium text-gray-700">First Name :</label>
                        <input type="text" name="User_FirstName" value="<?php echo $User_Data['User_FirstName'] ?>"
                            placeholder="First Name"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="User_LastName" class="block text-sm font-medium text-gray-700">Last Name :</label>
                        <input type="text" name="User_LastName" value="<?php echo $User_Data['User_LastName'] ?>"
                            placeholder="Last Name"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>

                    <div>
                        <label for="User_Phone" class="block text-sm font-medium text-gray-700">Phone Number:</label>
                        <input type="tel" name="User_Phone" placeholder="Example: 0714876397"
                            pattern="^([0-9]{2}){4}[0-9]{2}$" value="<?php echo $User_Data['User_Phone'] ?>"
                            placeholder="Phone Number"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="User_Country" class="block text-sm font-medium text-gray-700">Your Country:</label>
                        <select id="User_Country" name="User_Country"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                            <option value="" disabled selected>Select your country</option>
                        </select>
                    </div>
                    <?php if ($Current_User_Role === 'Owner') { ?>

                        <div>
                            <label for="ConfirmPassword" class="block text-sm font-medium text-gray-700">User Privilege
                                Level</label>

                            <?php if ($owner_count > 1 || $User_Data['User_Role'] !== 'Owner') { ?>
                                <select name="User_Role" id="User_Role"
                                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <?php
                                    $Roles = ['Client', 'Admin', 'Owner'];

                                    foreach ($Roles as $Role) {
                                        if ($User_Data['User_Role'] === $Role) {
                                            echo '<option selected>' . $Role . '</option>';
                                        } else {
                                            echo '<option>' . $Role . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            <?php } else { ?>
                                <div class="mt-1 block bg-red-200" id="privilegeWarning">
                                    <span
                                        class="inline-block bg-yellow-200 text-yellow-800 rounded-full px-3 py-1 text-xs font-semibold mr-2">⚠️
                                        Privilige Warning :</span><br>
                                    <span class="text-yellow-800">There is currently only <b>1</b> user with <b>Owner</b> Level
                                        Privilege. To be able to modify the Privilege Level for this user, there should be at least <b>1 more</b>
                                        user with 'Owner Level Privilege'.</span>
                                </div>

                            <?php } ?>

                        </div>
                    <?php } else { ?>
                        <select name="User_Role" id="User_Role" hidden>
                            <?php
                            $Roles = ['Client', 'Admin', 'Owner'];


                            foreach ($Roles as $Role) {
                                if ($User_Data['User_Role'] === $Role) {

                                    echo '<option selected>' . $Role . '</option>';
                                } else {
                                    echo '<option>' . $Role . '</option>';
                                }
                            }
                            ?>
                        </select>

                    <?php } ?>

                </div>
                <!-- Right Section -->
                <div class="space-y-4">

                    <div>
                        <label for="User_Address" class="block text-sm font-medium text-gray-700">Your Address:</label>
                        <input type="text" name="User_Address" placeholder="Your Address"
                            value="<?php echo $User_Data['User_Address'] ?>"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="User_Email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" name="User_Email" placeholder="Your Email Address"
                            value="<?php echo $User_Data['User_Email'] ?>"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <div>
                            <label for="Password" class="block text-sm font-medium text-gray-700">Password:</label>
                            <input type="password" name="User_Password" id="Password"
                                placeholder="Your Password (Optional)"
                                class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                oninput="checkForm()">
                        </div>
                        <div>
                            <label for="ConfirmPassword" class="block text-sm font-medium text-gray-700">Confirm
                                Password:</label>
                            <input type="password" id="ConfirmPassword" placeholder="Confirm Your Password"
                                class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                oninput="checkForm()">
                            <span id="Password_Error" class="text-red-600"></span>
                        </div>



                        <!-- Full-width Buttons Section -->






                    </div>
                </div>
                <div class="md:col-span-6 block justify-center" id="SaveChanges">
                    <div class="space-y-2">
                        <!-- Your reset button -->
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 rounded buttons"
                            id="Submit" disabled>Save Changes</button>

                    </div>
                </div>
            </form>






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

                function checkForm() {
                    var password = document.getElementById('Password').value;
                    var confirmPassword = document.getElementById('ConfirmPassword').value;
                    var submitButton = document.getElementById('Submit');
                    var passwordError = document.getElementById('Password_Error');

                    if (password === confirmPassword || password === '') {
                        passwordError.textContent = "";
                    } else {
                        passwordError.textContent = "Passwords do not match, please double check!";
                    }

                    if (password === confirmPassword) {
                        submitButton.disabled = false;
                        submitButton.style.backgroundColor = "#0070f3";
                        submitButton.style.cursor = "pointer";
                        submitButton.classList.add('hover:bg-blue-400');
                    } else {
                        submitButton.disabled = true;
                        submitButton.style.backgroundColor = "gray";
                        submitButton.style.cursor = "default";
                    }
                }

                function resetForm() {
                    checkForm(); // Ensure the form state is updated

                }







                // Fetch country data and populate the select element
                document.addEventListener('DOMContentLoaded', function () {
                    const defaultCountry = "<?php echo htmlspecialchars($User_Data['User_Country'], ENT_QUOTES, 'UTF-8'); ?>"; // or use the embedded script's value

                    fetch('https://api.first.org/data/v1/countries')
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok ' + response.statusText);
                            }
                            return response.json();
                        })
                        .then(data => {
                            const countrySelect = document.getElementById('User_Country');

                            if (!countrySelect) {
                                throw new Error('Select element with ID "User_Country" not found.');
                            }

                            // Extract the countries object from the data
                            const countries = data.data;

                            // Filter countries based on name length (less than 30 characters)
                            const filteredCountries = Object.values(countries).filter(country => country.country.length < 30);

                            // Create and append options for filtered countries
                            filteredCountries.forEach(country => {
                                const option = document.createElement('option');
                                option.value = country.country;
                                option.textContent = country.country;

                                // Set the selected attribute if the country matches the default country
                                if (country.country === defaultCountry) {
                                    option.selected = true;
                                }

                                countrySelect.appendChild(option);
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching country data:', error);
                        });
                });




            </script>
</body>

</html>
<style>
    #SaveChanges {
        margin-top: 2%;
        margin-left: 42%;
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