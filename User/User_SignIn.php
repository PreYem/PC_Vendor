<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <link href="../output.css" rel="stylesheet">
    <title>Sign In </title>
    <?php

    session_start();


    include_once ("../DB_Connexion.php");


    $loginError = '';


    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username = $_POST['User_Username'];
        $password = $_POST['User_Password'];


        $query = "SELECT * FROM Users WHERE User_Username = :username";
        $stmt = $connexion->prepare($query);
        $stmt->execute(['username' => $username]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($user && password_verify($password, $user['User_Password'])) {

            $_SESSION['User_ID'] = $user['User_ID'];
            $_SESSION['User_Username'] = $user['User_Username'];
            $_SESSION['User_Role'] = $user['User_Role'];


            header("Location: ../Product/Products_List.php");
            exit;
        } else {

            $loginError = "Invalid username or password.";
        }
    }
    ?>
    
</head>

<body class="bg-gray-200 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-md w-96">

        <h1 class="text-2xl font-bold mb-6 text-center">Sign in to your account</h1>

        <form action="" method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <label for="User_Username" class="block text-sm font-medium text-gray-700">Username:</label>
                <input type="text" name="User_Username" placeholder="Your Username"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <label for="User_Password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" name="User_Password" placeholder="Your Password"
                    class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
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
                <span class="text-sm">Don't have an account? </span><a href="User_SignUp.php"
                    class="text-blue-500 hover:underline">Sign Up</a>
            </div>
        </form>

    </div>

</body>



</html>