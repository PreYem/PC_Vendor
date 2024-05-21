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


  <div class="bg-gray-800 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center">
        <!-- Category Links -->
        <div class="space-x-4">
          <div class="flex">
            <!-- Main Category Links -->
            <a href="Category/Categories_List.php"
              class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Category
              List</a>
            <a href="Category/Categories_Add.php"
              class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">+ New
              Category</a>
          </div>
          <div class="flex">
            <!-- Sub Category Links -->
            <a href="Category/SubCategories/SubCategories_List.php"
              class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Sub Category List</a>
            <a href="Category/SubCategories/SubCategories_Add.php"
              class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">New Sub
              Category</a>
          </div>
        </div>
        <!-- Manufacturer Links -->
        <div class="space-x-4">
          <a href="Manufacturer/Manufacturers_Add.php"
            class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">New
            Manufacturer</a>
          <a href="Manufacturer/Manufacturers_List.php"
            class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Manufacturer
            List</a>
        </div>
        <!-- Product Links -->
        <div class="space-x-4">
          <a href="Product/Products_Add.php"
            class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">New
            Product</a>
          <a href="Product/Products_List.php"
            class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Product
            List</a>
        </div>
        <!-- Authentication Links -->
        <?php
        session_start(); // Start or resume session
        
        // Debugging session variable

        // Conditional display of login/logout links
        if (isset($_SESSION['User_ID'])) {
          // Display logout link
          echo '<a href="User/User_Logout.php" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Logout</a>';
        } else {
          // Display login and register links
          echo '
        <div class="space-x-4">
            <a href="User/User_SignIn.php" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
            <a href="User/User_SignUp.php" class="text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>
        </div>
    ';
        }
        ?>


      </div>
    </div>
  </div>


</body>

</html>