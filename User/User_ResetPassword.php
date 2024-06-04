<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password | PC Vendor</title>
    <?php
    session_start();
    include_once ("../DB_Connexion.php");

    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);

    if (isset($_SESSION['User_ID']) || !isset($_SESSION['User_ID_Reset'])) {

        header("Location: ../.");
        exit();
    }
    ;






    
    $loginError = '';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['User_Password']) && !empty($_POST['ConfirmPassword'])) {
            if ($_POST['User_Password'] === $_POST['ConfirmPassword']) {

                $User_ID_Reset = $_SESSION['User_ID_Reset'];
                $New_Password_Hashed = password_hash($_POST['User_Password'], PASSWORD_BCRYPT);
    
                $Update_Password = "UPDATE Users SET User_Password = :User_Password WHERE User_ID = :User_ID";
                $pdoUpdatePassword = $connexion->prepare($Update_Password);
                $pdoUpdatePassword->execute(['User_Password' => $New_Password_Hashed, 'User_ID' => $User_ID_Reset]);
    

                session_unset();
    

                $_SESSION['Password_Reset'] = "Password has been reset successfully. Log in.";
                header('Location: User_SignIn.php');
                exit();
            } else {
                $loginError = 'Passwords do not match.';
            }
        } else {
            $loginError = 'Please fill in both password fields.';
        }
    }
    ?>
    

    
</head>

<body>
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
            <h1 class="text-2xl font-bold mb-6 text-center">Reset Your Password :</h1>
            <form action="" method="POST" class="max-w-md mx-auto bg-white p-8 rounded shadow-md space-y-4">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                    <label for="User_Password" class="block text-sm font-medium text-gray-700 mt-2">Your New Password
                        :</label>
                    <input type="password" name="User_Password" id="Password" placeholder="Your Password"
                        class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        oninput="checkForm()" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                    <label for="Reset_Code" class="block text-sm font-medium text-gray-700 mt-2">Confirm Your
                        Password</label>
                    <input type="password" id="ConfirmPassword" name="ConfirmPassword"
                        placeholder="Confirm Your Password"
                        class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        oninput="checkForm()" required>
                    <span id="Password_Error" class="text-red-600"></span>
                </div>

                <div class="flex justify-between items-center ml-28">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">Reset
                        Password
                    </button>
                </div>

                <span class="text-red-600 block"><?php echo $loginError ?></span>

            </form>

            <!-- Include Font Awesome -->


        </div>
    </div>



</body>


<script>
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

        if (password === confirmPassword && password !== '' && confirmPassword !== '') {
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
        checkForm();
    }
</script>

<style>
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

</html>