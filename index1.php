<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="output.css" rel="stylesheet" />
  <link rel="icon" href="Logo.png" type="image/x-icon">
  <title>Home</title>
  <?php include_once ("DB_Connexion.php"); ?>

</head>

<body>

<nav class="bg-gray-800 text-white">
    <!-- Top Navigation Bar -->
    <div class="max-w-screen-xl flex justify-between items-center mx-auto p-4">

        <!-- Main Menu -->
        <div class="flex space-x-4 md:order-2">
            <a href="#" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Home</a>
            <a href="#" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">About</a>
            <!-- Services with Dropdown -->
            <div class="relative">
                <a href="#" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Services</a>
                <div class="absolute left-0 mt-2 bg-gray-800 rounded shadow-md p-2 hidden">
                    <a href="#" class="block px-2 py-1 hover:bg-gray-700">Service 1</a>
                    <a href="#" class="block px-2 py-1 hover:bg-gray-700">Service 2</a>
                    <a href="#" class="block px-2 py-1 hover:bg-gray-700">Service 3</a>
                </div>
            </div>
            <a href="#" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Contact</a>
        </div>
        <!-- User Links -->
        <div class="flex-shrink space-x-4 md:order-3">
            <?php if (isset($_SESSION['User_ID'])): ?>
                <a href="User/User_Logout.php" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Logout</a>
            <?php else: ?>
                <a href="User/User_SignIn.php" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                <a href="User/User_SignUp.php" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>



</body>

</html>