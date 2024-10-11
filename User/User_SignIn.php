<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Login 🔑 | PC Vendor</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">



    <?php

    session_start();
    include_once ("../DB_Connexion.php");

    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
    }

    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);

    $loginError = '';


    if (isset($_SESSION['User_ID'])) {

        header("Location: ../.");
        exit();
    }
    ;


    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $User_Username = $_POST['User_Username'];
        $password = $_POST['User_Password'];


        $query = "SELECT * FROM Users WHERE User_Username = :User_Username";
        $stmt = $connexion->prepare($query);
        $stmt->execute(['User_Username' => $User_Username]);

        $User = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($User && password_verify($password, $User['User_Password'])) {

            if ($User['Account_Status'] !== '🔒 Locked') {
                $_SESSION['User_ID'] = $User['User_ID'];
                $_SESSION['User_Username'] = $User['User_Username'];
                $_SESSION['User_Role'] = $User['User_Role'];

                $_SESSION['Product_Add/Update'] = "Welcome Back " . $User['User_FirstName'];
                header("Location: ../.");
                exit;

            } else {
                $loginError = "Login Failed, Account is locked.";
            }


        } else {

            $loginError = "Invalid username or password.";
        }
    }



    ?>
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

                <a href="../User/User_SignIn.php"
                    class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                <a href="../User/User_SignUp.php"
                    class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>

            </div>

        </div>


    </nav>


    <div class="container">
        <div class="content-wrapper">
            <h1 class="text-2xl font-bold mb-6 text-center">Sign in to your account</h1>
            <form action="" method="POST" class="max-w-md mx-auto bg-white p-8 rounded shadow-md space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                    <label for="User_Username" class="block text-sm font-medium text-gray-700">Username:</label>
                    <input type="text" name="User_Username" placeholder="Your Username"
                        class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                    <label for="User_Password" class="block text-sm font-medium text-gray-700">Password:</label>
                    <input type="password" name="User_Password" id="password" placeholder="Your Password"
                        class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                    <span id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                <div class="flex justify-between items-center">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                        Sign In
                    </button>
                    <button type="reset"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Reset
                    </button>
                </div>

                <span class="text-red-600 block"><?php echo $loginError ?></span>

                <div class="text-center">
                    <?php if ($loginError === "Invalid username or password.") { ?>
                        <span class="text-sm"></span><a href="User_ForgotPassword.php"
                            class="text-purple-500 hover:underline">Forgotten Password?</a><br>
                    <?php } ?>
                    <span class="text-sm">Don't have an account? </span><a href="User_SignUp.php"
                        class="text-blue-500 hover:underline">Sign Up</a><br>
                    <?php if (isset($_SESSION['Password_Reset'])) { ?>
                        <span id="Password_Reset" ><?php echo $_SESSION['Password_Reset'];
                        unset($_SESSION['Password_Reset']) ?></span>
                    <?php } ?>
                </div>
            </form>

            <!-- Include Font Awesome -->


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

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            // Toggle the type attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle the eye / eye-slash icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    </script>
</body>

</html>
<style>
    #Password_Reset{
        color : green;
    }


    /* Remove the .content-wrapper margin-top and padding-top */
    .content-wrapper {
        margin-top: 0;
        padding-top: 0;
    }

    .outer-container {
        display: flex;
        justify-content: center;
        width: 100%;
        padding-top: 16px;
        /* Adjust the padding if needed */
    }

    .container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        width: 100%;
        max-width: 1500px;
        justify-content: space-between;

        margin-left: 11%;
        padding-top: 10%;

    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
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
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        max-width: 100%;

        display: inline-block;
        max-height: 1.2em;

    }
</style>