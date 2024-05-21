<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>New Manufacturer</title>
    <link href="../output.css" rel="stylesheet">
    <style>
        .Btn {
            background-color: blue;
        }
    </style>

    <?php
    $Error_Message = '';
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
    $query = "SELECT User_Role, User_Username FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);

    

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];
        $User_Username = $row['User_Username'] ;

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
    } ;

    if (!empty($_POST["Manufacturer_Name"]) && !empty($_POST["Manufacturer_Desc"])) {
        $manufacturerName = $_POST["Manufacturer_Name"];
        $manufacturerDesc = $_POST["Manufacturer_Desc"];

        // Convert the manufacturer name to lowercase
        $lowercaseManufacturer = strtolower($manufacturerName);

        // Check if a similar manufacturer already exists
        $queryCheck = "SELECT Manufacturer_ID FROM Manufacturers WHERE LOWER(Manufacturer_Name) = :LowercaseManufacturer";
        $pdostmtCheck = $connexion->prepare($queryCheck);
        $pdostmtCheck->execute(["LowercaseManufacturer" => $lowercaseManufacturer]);

        $existingManufacturer = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existingManufacturer) {
            // If no similar manufacturer found, proceed with the insert
            $queryInsert = "INSERT INTO Manufacturers (Manufacturer_Name, Manufacturer_Desc) VALUES (:Manufacturer_Name, :Manufacturer_Desc)";
            $pdostmtInsert = $connexion->prepare($queryInsert);

            $pdostmtInsert->execute([
                "Manufacturer_Name" => $manufacturerName,
                "Manufacturer_Desc" => $manufacturerDesc
            ]);

            $pdostmtInsert->closeCursor();
            header("Location: Manufacturers_List.php");
            exit();
        } else {
            $Error_Message = 'A Manufacturer with that name already exists, try again!';
            // You may choose to redirect or handle this case as needed
        }
    }
    ?>


</head>

<body class="bg-gray-200">
    <section class="max-w-4xl mx-auto py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-4">Add Manufacturer</h1>
            <form action="" method="POST" enctype="multipart/form-data">

                <div class="mb-4">
                    <label for="Manufacturer_Name" class="block text-sm font-medium text-gray-700">Manufacturer Name:</label>
                    <input type="text" name="Manufacturer_Name" id="Manufacturer_Name" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Your Manufacturer's Name" required>
                </div>
                <div class="mb-4">
                    <label for="Manufacturer_Desc" class="block text-sm font-medium text-gray-700">Manufacturer Description:</label>
                    <textarea name="Manufacturer_Desc" id="Manufacturer_Desc" rows="4" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Write a few lines describing the manufacturer"></textarea>
                </div>
                <div class="mb-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Add Manufacturer</button>
                    <button type="reset" class="px-4 py-2 bg-gray-400 text-white rounded-md ml-2 hover:bg-gray-500">Clear</button>
                </div>
                <?php if (!empty($Error_Message)) : ?>
                    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded-md">
                        <?php echo $Error_Message; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </section>
</body>

</body>

</html>