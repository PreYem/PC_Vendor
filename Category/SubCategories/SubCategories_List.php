<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../../Logo.png" type="image/x-icon">
    <title>Sub Categories List</title>
    <?php
    include_once ("../../DB_Connexion.php");
    session_start();
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {

        header("Location: ../../User/User_SignIn.php");
        exit;
    }


    $User_ID = $_SESSION['User_ID'];
    $query = "SELECT User_Role, User_Username FROM Users WHERE User_ID = :User_ID";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':User_ID' => $User_ID]);



    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $User_Role = $row['User_Role'];
        $User_Username = $row['User_Username'];

        if ($User_Role === 'Owner') {
            $showUserManagement = true;
        } else {
            $showUserManagement = false;
        }

        if ($User_Role !== 'Owner' && $User_Role !== 'Admin') {
            header("Location: ../../User/User_Unauthorized.html");
            exit;
        }
    }
    ;

    $Query_SubCategory = "SELECT sc.SubCategory_ID, sc.SubCategory_Name, sc.SubCategory_Desc, c.Category_Name AS Category,
    COUNT(p.Product_ID) AS Product_Count
    FROM SubCategories sc
    INNER JOIN Categories c 
    ON sc.Category_ID = c.Category_ID
    LEFT JOIN Products p 
    ON sc.SubCategory_ID = p.SubCategory_ID
    GROUP BY sc.SubCategory_ID";

    $pdostmt = $connexion->prepare($Query_SubCategory);
    $pdostmt->execute();

    $result = $pdostmt->fetchAll(PDO::FETCH_ASSOC);

    ?>

<script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = document.querySelectorAll('tbody tr');
            console.log("1")
            rows.forEach(row => {
                const categoryNameCell = row.querySelector('td:nth-child(2)');
                const categoryName = categoryNameCell.textContent.trim();
                console.log("2")

                // Check if category name is "Unspecified" and disable buttons accordingly
                if (categoryName === "Unspecified") {
                    const editButton = row.querySelector('.edit-button');
                    const deleteButton = row.querySelector('.delete-button');
                    console.log("3")

                    editButton.disabled = true;

                    deleteButton.disabled = true;

                    editButton.hidden = true;

                    deleteButton.hidden= true;

                }
            });
        });
    </script>
</head>

<body class="bg-gray-100 p-8">
    <a href="SubCategories_Add.php"
        class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md inline-block mb-4">New Sub Category</a>
    <a href="../../index.php"
        class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-md inline-block mb-4">Main Page</a>
    <h1 class="text-3xl font-bold mb-4">Sub Categories</h1>
    <table class="w-full border-collapse border border-gray-300">
        <thead>
            <tr class="bg-gray-200">
                <th class="px-4 py-2">Sub Category ID</th>
                <th class="px-4 py-2">Sub Category Name</th>
                <th class="px-4 py-2">Main Category</th>
                <th class="px-4 py-2">Sub Category Description</th>
                <th class="px-4 py-2">Product Count</th>
                <th class="px-4 py-2">Options</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($result as $ligne): ?>
                <tr class="hover:bg-gray-100 transition-colors duration-200">
                    <td class="border px-4 py-2"><?php echo $ligne['SubCategory_ID'] ?></td>
                    <td class="border px-4 py-2"><?php echo $ligne['SubCategory_Name'] ?></td>
                    <td class="border px-4 py-2"><?php echo $ligne['Category'] ?></td>
                    <td class="border px-4 py-2"><?php echo $ligne['SubCategory_Desc'] ?></td>
                    <td class="border px-4 py-2"><?php echo $ligne['Product_Count'] ?></td>
                    <td class="border px-4 py-2">
                        <a href="SubCategories_Modify.php?id=<?php echo $ligne["SubCategory_ID"] ?>"
                            class="edit-button text-blue-500 hover:underline mr-2">Edit</a>
                        <a href="SubCategories_Delete.php?id=<?php echo $ligne["SubCategory_ID"] ?>"
                            onclick="return confirm('Are you sure you want to delete this Sub Category?\n*Disclaimer* : This action is irreversible')"
                            class="delete-button text-red-500 hover:underline">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>