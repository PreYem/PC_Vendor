<?php


 include_once('../DB_Connexion.php'); 


if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['Category_Name'])) {

    $Category_N = $_POST['Category_Name'];


    $querySubCategories = "SELECT sub.SubCategory_Name FROM SubCategories sub JOIN Categories cat ON sub.Category_ID = cat.Category_ID
                            WHERE cat.Category_Name = :Category_N";

    $pdostmtSubCategories = $connexion->prepare($querySubCategories);
    $pdostmtSubCategories->execute(['Category_N' => $Category_N]);
    $subCategories = $pdostmtSubCategories->fetchAll(PDO::FETCH_COLUMN);


    foreach ($subCategories as $subCategory) {
        

        echo "<option value=\"$subCategory\">$subCategory</option>";
    }
} else {

    echo "Error: Category name not provided.";
}


    