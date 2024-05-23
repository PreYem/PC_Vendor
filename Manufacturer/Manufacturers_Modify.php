<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Modify Manufacturer</title>
    <link href="../output.css" rel="stylesheet">
    <?php
    include_once("../DB_Connexion.php");

    $ligne = [];

    if (!empty($_GET["id"])) {
        $ManufacturerName = urldecode($_GET["id"]);
        $query = "SELECT Manufacturer_ID, Manufacturer_Name, Manufacturer_Desc FROM Manufacturers WHERE Manufacturer_Name = :Manufacturer_Name";
        $pdostmt = $connexion->prepare($query);
        $pdostmt->execute(["Manufacturer_Name" => $ManufacturerName]);
        $ligne = $pdostmt->fetch(PDO::FETCH_ASSOC);
        $pdostmt->closeCursor();
    }

    $Error_Message = '';
    if (!empty($_POST)) {
        $ManufacturerName = $_POST["Manufacturer_Name"];
        $Manufacturer_ID = $_POST["Manufacturer_ID"];
        $ManufacturerDesc = $_POST["Manufacturer_Desc"]; // Added Manufacturer_Desc variable
    
        $lowercaseManufacturer = strtolower($ManufacturerName);

        $queryCheck = "SELECT Manufacturer_ID FROM Manufacturers WHERE LOWER(Manufacturer_Name) = :LowercaseManufacturer AND Manufacturer_ID != :Manufacturer_ID";
        $pdostmtCheck = $connexion->prepare($queryCheck);
        $pdostmtCheck->execute([
            "LowercaseManufacturer" => $lowercaseManufacturer,
            "Manufacturer_ID" => $Manufacturer_ID
        ]);

        $existingManufacturer = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$existingManufacturer) {
            $queryUpdate = "UPDATE Manufacturers SET Manufacturer_Name=:Manufacturer_Name, Manufacturer_Desc=:Manufacturer_Desc WHERE Manufacturer_ID=:Manufacturer_ID";
            $pdostmtUpdate = $connexion->prepare($queryUpdate);

            $pdostmtUpdate->execute([
                "Manufacturer_Name" => $ManufacturerName,
                "Manufacturer_Desc" => $ManufacturerDesc, // Added Manufacturer_Desc binding
                "Manufacturer_ID" => $Manufacturer_ID
            ]);

            $pdostmtUpdate->closeCursor();
            header("Location: Manufacturers_List.php");
            exit();
        } else {
            $Error_Message = 'A Manufacturer with that name already exists, try again!';
        }
    }
    ?>



</head>

<body class="bg-gray-200">
    <section class="max-w-4xl mx-auto py-8">
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h1 class="text-2xl font-bold mb-4">Edit Manufacturer</h1>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label for="Manufacturer_ID" class="block text-sm font-medium text-gray-700">Manufacturer ID:</label>
                    <span class="block mt-1 p-2 bg-gray-100 border border-blue-300 rounded-md"><?php echo $ligne["Manufacturer_ID"]; ?></span>
                    <input type="number" name="Manufacturer_ID" value="<?php echo $ligne["Manufacturer_ID"]; ?>" hidden>
                </div>
                <div class="mb-4">
                    <label for="Manufacturer_Name" class="block text-sm font-medium text-gray-700">Manufacturer Name:</label>
                    <input type="text" name="Manufacturer_Name" value="<?php echo $ligne["Manufacturer_Name"]; ?>" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Your Manufacturer Name" required>
                </div>
                <div class="mb-4">
                    <label for="Manufacturer_Desc" class="block text-sm font-medium text-gray-700">Manufacturer Description:</label>
                    <textarea name="Manufacturer_Desc" id="Manufacturer_Desc" cols="30" rows="10" class="mt-1 p-2 w-full border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Write a few lines describing the manufacturer"><?php echo $ligne["Manufacturer_Desc"]; ?></textarea>
                </div>
                <div class="mb-4">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save Changes</button>
                    <a href="Manufacturers_List.php" class="ml-4 px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Close</a>
                </div>
                <?php if (!empty($Error_Message)): ?>
                    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-2 rounded-md">
                        <?php echo $Error_Message; ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </section>
</body>


</html>