<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <link href="../output.css" rel="stylesheet">
    <title>Edit Profile </title>

    <style>
        input {
            border: 1px black solid;
        }

        body {
            background-color: grey;
        }
        .formInput {
            background-color: white;
        }
    </style>

    <?php

    include_once ("../DB_Connexion.php");


    session_start(); // Start or resume existing session
    
    // Check if user is logged in and has the appropriate role
    if (!isset($_SESSION['User_ID']) || !isset($_SESSION['User_Role'])) {
        // User is not logged in, redirect to login page
        header("Location: ../User/User_SignIn.php");
        exit; // Ensure script stops after redirection
    }

    // Retrieve the user's role from the database based on User_ID stored in session
    $userId = $_SESSION['User_ID'];
    $query = "SELECT User_Role FROM Users WHERE User_ID = :userId";
    $pdostmt = $connexion->prepare($query);
    $pdostmt->execute([':userId' => $userId]);

    if ($row = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
        $userRole = $row['User_Role'];

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
    }
    ;

    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $userId = $_GET['id'];

        // Retrieve user details based on User_ID
        $query = "SELECT * FROM Users WHERE User_ID = :userId";
        $pdostmt = $connexion->prepare($query);
        $pdostmt->execute([':userId' => $userId]);

        if ($ligne = $pdostmt->fetch(PDO::FETCH_ASSOC)) {
            // Assign user data to variables
    
            $username = $ligne['User_Username'];
            $firstName = $ligne['User_FirstName'];
            $lastName = $ligne['User_LastName'];
            $phone = $ligne['User_Phone'];
            $country = $ligne['User_Country'];
            $address = $ligne['User_Address'];
            $email = $ligne['User_Email'];
            $userRole = $ligne['User_Role'];
            $registrationDate = $ligne['User_RegisterationDate'];
            echo '<title>Edit User ' . $username . '</title>';
        } else {
            echo 'Error';
        }
    }
    ;

    $UpdateStatus = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $User_ID = $_GET['id'];
    $User_Username = $_POST['User_Username'];
    $User_FirstName = $_POST['User_FirstName'];
    $User_LastName = $_POST['User_LastName'];
    $User_Phone = $_POST['User_Phone'];
    $User_Country = $_POST['User_Country'];
    $User_Address = $_POST['User_Address'];
    $User_Email = $_POST['User_Email'];
    $User_Role = $_POST['User_Role'];

    // Update user details in the database
    $Update_User_Query = "UPDATE Users SET User_Username = :User_Username, User_FirstName = :User_FirstName, User_LastName = :User_LastName, User_Phone = :User_Phone, 
                          User_Country = :User_Country, User_Address = :User_Address, User_Email = :User_Email, User_Role = :User_Role WHERE User_ID = :User_ID";

    $pdostmtquery = $connexion->prepare($Update_User_Query);

    // Bind parameters
    $pdostmtquery->bindParam(':User_Username', $User_Username, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_FirstName', $User_FirstName, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_LastName', $User_LastName, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_Phone', $User_Phone, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_Country', $User_Country, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_Address', $User_Address, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_Email', $User_Email, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_Role', $User_Role, PDO::PARAM_STR);
    $pdostmtquery->bindParam(':User_ID', $User_ID, PDO::PARAM_INT);

    // Execute the prepared statement
    $result = $pdostmtquery->execute();

    // Update $UpdateStatus based on the result of the query
    if ($result) {
        $UpdateStatus = true;
    } 
}
    ?>


</head>

<body>
    <h1>Edit User Details</h1><br>
    <form action="" method="POST">
        <table>

            <tr>
                <td>
                    <label for="User_ID">User ID</label>
                </td>
                <td>
                    <span>
                        <?php echo $userId ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="User_Username">Username</label>
                </td>
                <td>
                    <input class="formInput" type="text" name="User_Username" value="<?php echo $username ?>" required>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="User_FirstName">First Name</label>
                </td>
                <td>
                    <input class="formInput" type="text" name="User_FirstName" value="<?php echo $firstName ?>"
                        required>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="User_LastName">Last Name</label>
                </td>
                <td>
                    <input class="formInput" type="text" name="User_LastName" value="<?php echo $lastName ?>" required>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="User_UserPhone">Phone Number</label>
                </td>

                <td>
                    <input class="formInput" type="tel" name="User_Phone" pattern="^([0-9]{2}){4}[0-9]{2}$"
                        value="<?php echo $phone ?>">
                </td>
            </tr>

            <tr>
                <td>
                    <label for="country">Your Country</label>
                </td>

                <td>
                    <select class="formInput" id="User_Country" name="User_Country" placeholder="Select Your Country"
                        required>
                        <option class="formInput" value="" disabled selected>Select your country</option>
                    </select>
                </td>
            </tr>

            <tr>

                <td>
                    <label for="User_Address">Your Address</label>
                </td>
                <td>
                    <input class="formInput" type="text" name="User_Address" value="<?php echo $address ?>" required>
                </td>
            </tr>


            <tr>
                <td>
                    <label for="User_Email">Email</label>
                </td>
                <td>
                    <input class="formInput" type="email" name="User_Email" value="<?php echo $email ?>" required>
                </td>
            </tr>

            <tr>
                <td>
                    <label for="User_Role">User Privilege Level</label>
                </td>
                <td>
                    <select name="User_Role" id="User_Role">
                        <?php
                        $roles = ['Client', 'Admin', 'Owner']; // Define available roles
                        
                        // Loop through each role and generate options
                        foreach ($roles as $role) {
                            if ($userRole === $role) {
                                // If the user's role matches this option, mark it as selected
                                echo '<option selected>' . $role . '</option>';
                            } else {
                                echo '<option>' . $role . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>

            </tr>

            <tr>
                <td>
                    <label for="User_RegisterationDate">Account Created On</label>
                </td>

                <td>
                    <span>
                        <?php echo $registrationDate ?>
                    </span>
                </td>

            </tr>

        </table>
        <input class="formInput" type="submit" name="submit" value="Save Changes">
        <input type="reset" value="Reset">
        <a href="User_Management.php" class="formInput">User Management Dashboard</a>
    </form>
    <?php
    if ($UpdateStatus === true) {
        echo '<span style="color: green;">Changes saved successfully</span>';
    } 
    ?>
</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const defaultCountry = "<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>"; // or use the embedded script's value

    fetch('https://api.first.org/data/v1/countries')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const countrySelect = document.getElementById('User_Country');

            if (!countrySelect) {
                throw new Error('Select element with ID "User_Country" not found.');
            }

            // Extract the countries object from the data
            const countries = data.data;

            // Filter countries based on name length (less than 30 characters)
            const filteredCountries = Object.values(countries).filter(country => country.country.length < 30);

            // Create and append options for filtered countries
            filteredCountries.forEach(country => {
                const option = document.createElement('option');
                option.value = country.country;
                option.textContent = country.country;
                
                // Set the selected attribute if the country matches the default country
                if (country.country === defaultCountry) {
                    option.selected = true;
                }

                countrySelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching country data:', error);
        });
});


</script>





<?php



// Check if the form was submitted

?>

</html>


</html>