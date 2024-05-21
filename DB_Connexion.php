<?php
$host = 'localhost';
$dbname = 'pc_vendor';
$username = 'root';
$password = 'Junkyard010'; // le mot de passe dépant de votre Mot de passe dans votre MYSQL

$DB_message_erreur = '';

try {
    $connexion = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $DB_message_erreur = "Database is working as intended";

} catch (PDOException $e) //
{

    die("Database $dbname is not working correctly  :" . $e->getMessage());
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tailwind CSS Example</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg">


</body>

</html>
