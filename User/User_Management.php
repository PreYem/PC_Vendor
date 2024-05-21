<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>User Management</title>
    <link href="../output.css" rel="stylesheet">
    <?php
    include_once ("../DB_Connexion.php");

    session_start();


    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
        // User is not logged in, redirect to login page
        header("Location: ../User/User_SignIn.php");
        exit;
    }


    $userId = $_SESSION['User_ID'];
    $query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];

        if ($userRole !== 'Owner') {

            header("Location: ../User/User_Unauthorized.html");
            exit;
        }
    }
    ;

    $UserQuery = "SELECT User_ID, User_Username, User_FirstName, User_LastName, User_Phone, User_Country, User_Address, User_Email, 
                  User_RegisterationDate, User_Role FROM Users";

    $pdostmt = $connexion->prepare($UserQuery);

    $pdostmt->execute();



    ?>

    <script>
        function hideDeleteButtonForOwners() {
            // Get all table rows in the document
            var tableRows = document.querySelectorAll('tbody tr');

            // Loop through each table row
            tableRows.forEach(function (row) {
                // Find the user role cell within the current row
                var userRoleCell = row.querySelector('#User_Role');

                // Check if user role cell exists and contains "Owner"
                if (userRoleCell && userRoleCell.textContent.trim() === 'Owner') {
                    // Find the delete button in the current row
                    var deleteButton = row.querySelector('#deleteButton');

                    // Hide the delete button if found
                    if (deleteButton) {
                        deleteButton.style.display = 'none';
                    }
                }
            });
        }

        // Call the function when the document has finished loading
        document.addEventListener('DOMContentLoaded', function () {
            hideDeleteButtonForOwners();
        });

    </script>


</head>

<body class="bg-gray-100">

    <div class="container mx-auto mt-10 p-5 bg-white shadow-md rounded-lg">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">User Management</h1>
        <a href="../Product/Products_List.php" class="text-blue-500 hover:underline mb-4 inline-block">Product List</a>

        <!-- Display existing users in a table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">User ID</th>
                        <th class="py-2 px-4 border-b">Username</th>
                        <th class="py-2 px-4 border-b">First and Last Name</th>
                        <th class="py-2 px-4 border-b">Phone</th>
                        <th class="py-2 px-4 border-b">Country</th>
                        <th class="py-2 px-4 border-b">Address</th>
                        <th class="py-2 px-4 border-b">Email</th>
                        <th class="py-2 px-4 border-b">Registration Date<br>(GMT+1)</th>
                        <th class="py-2 px-4 border-b">Role</th>
                        <th class="py-2 px-4 border-b">Options</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($pdostmt) {
                        while ($ligne = $pdostmt->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="hover:bg-gray-100">
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_ID'] ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_Username'] ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_FirstName'] . ' ' . $ligne['User_LastName'] ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_Phone'] ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_Country'] ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_Address'] ?></td>
                                <td class="py-2 px-4 border-b"><?php echo $ligne['User_Email'] ?></td>
                                <td class="py-2 px-4 border-b">
                                    <?php echo date('Y-m-d', strtotime($ligne["User_RegisterationDate"])) . '<br><b> at </b><br>' . date('H:i:s', strtotime($ligne["User_RegisterationDate"])); ?>
                                </td>
                                <td class="py-2 px-4 border-b" id="User_Role" ><?php echo $ligne['User_Role'] ?></td>
                                <td class="py-2 px-4 border-b">
                                    <a href="User_Modify.php?id=<?php echo $ligne["User_ID"] ?>" class="text-blue-500 hover:underline">Edit</a>
                                    <a href="User_Delete.php?id=<?php echo $ligne["User_ID"] ?>" 
                                    onclick="return confirm('Are you sure you want to delete this user account?\n*Disclaimer* : This action is irreversible')" class="text-red-500 hover:underline ml-4"
                                     id="deleteButton" >Delete</a>
                                </td>
                            </tr>
                        <?php endwhile;} ?>
                </tbody>
            </table>
        </div>
    </div>
    
</body>



</html>