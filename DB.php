<?php
$host = 'localhost';
$dbname = 'pc_vendor';
$username = 'root';
$password = 'Junkyard010';


try {
    
    $connexion = new PDO("mysql:host = $host;dbname=$dbname", $username, $password);

} catch (PDOException $e) //
{

    die("Database $dbname is not working correctly  :" . $e->getMessage());
}







