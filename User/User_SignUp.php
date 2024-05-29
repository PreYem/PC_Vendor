<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <title>Register 📋 | PC Vendor</title>

    <?php
    session_start();
    include_once ("../DB_Connexion.php");


    if (isset($_SESSION['User_ID'])) {

        header("Location: ../.");
        exit();
    }
    ;

    function formatNumber($number)
    {
        return number_format($number, 0, '', ' ');
    }



    $Categories = "SELECT Category_ID, Category_Name FROM Categories ORDER BY Category_ID ASC";
    $pdoCategories = $connexion->prepare($Categories);
    $pdoCategories->execute();
    $Categories = $pdoCategories->fetchAll(PDO::FETCH_ASSOC);




    $Error_Message = '';

    if (!empty($_POST)) {
        if (!empty($_POST["User_Username"])) {
            // Sanitize and normalize inputs
            $User_Username = filter_var($_POST["User_Username"], FILTER_SANITIZE_STRING);
            $User_Username_LowerCase = strtolower($User_Username);
            $User_Email = filter_var($_POST["User_Email"], FILTER_SANITIZE_EMAIL);
            $User_Email_Lower = strtolower($User_Email);

            // Prepare and execute query to check for existing user
            $QueryCheck = "SELECT User_ID FROM Users WHERE LOWER(User_Username) = :User_Username_LowerCase OR LOWER(User_Email) = :User_Email_Lower";
            $pdostmtCheck = $connexion->prepare($QueryCheck);
            $pdostmtCheck->execute([
                "User_Username_LowerCase" => $User_Username_LowerCase,
                "User_Email_Lower" => $User_Email_Lower
            ]);

            $Existing_User = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);

            if (!$Existing_User) {
                // Hash the password
                $Password = $_POST["User_Password"];
                $User_Hashed_Password = password_hash($Password, PASSWORD_BCRYPT);

                // Set the registration date and time
                $moroccoTimezone = new DateTimeZone('Africa/Casablanca');
                $moroccoDateTime = new DateTime('now', $moroccoTimezone);
                $moroccoFormattedDateTime = $moroccoDateTime->format('Y-m-d H:i:s');

                // Prepare and execute the insert query
                $Insert_User_Query = "INSERT INTO Users (User_Username, User_FirstName, User_LastName, User_Phone, User_Country, User_Address, 
                                                      User_Email, User_Password, User_RegisterationDate)
                                      VALUES (:User_Username, :User_FirstName, :User_LastName, :User_Phone, :User_Country, :User_Address, 
                                              :User_Email, :User_Password, :User_RegisterationDate)";
                $pdostmtUser = $connexion->prepare($Insert_User_Query);

                try {
                    $pdostmtUser->execute([
                        'User_Username' => $User_Username,
                        'User_FirstName' => $_POST['User_FirstName'],
                        'User_LastName' => $_POST['User_LastName'],
                        'User_Phone' => $_POST['User_Phone'],
                        'User_Country' => $_POST['User_Country'],
                        'User_Email' => $User_Email,
                        'User_Address' => $_POST['User_Address'],
                        'User_Password' => $User_Hashed_Password,
                        'User_RegisterationDate' => $moroccoFormattedDateTime
                    ]);

                    // Fetch the newly inserted user data
                    $selectUser_ID = "SELECT User_ID, User_Username, User_Role FROM Users WHERE User_Username = :User_Username";
                    $pdostmtUser_ID = $connexion->prepare($selectUser_ID);
                    $pdostmtUser_ID->execute(["User_Username" => $User_Username]);
                    $User = $pdostmtUser_ID->fetch(PDO::FETCH_ASSOC);

                    // Start the session and set session variables
                    session_start();
                    $_SESSION['User_ID'] = $User['User_ID'];
                    $_SESSION['User_Username'] = $User['User_Username'];
                    $_SESSION['User_Role'] = $User['User_Role'];

                    // Redirect to homepage
                    header("Location: ../.");
                    exit;

                } catch (PDOException $e) {
                    // Detailed error message for debugging
                    error_log("Error inserting user: " . $e->getMessage());
                    echo "An error occurred while inserting the user. Please try again later.";
                    exit;
                }
            } else {
                $Error_Message = 'Username or Email is taken, try a different one!';
            }
        }
    }
    ?>
</head>



<body class="bg-gray-100">

    <nav class="bg-blue-800 text-white">
        <div class="flex flex-wrap justify-between items-center p-4">
            <!-- Logo -->
            <a href=".././"><img src="../Logo.png" alt="Logo" id="Logo"></a>

            <!-- Category Links -->
            <div class="flex flex-wrap space-x-4">
                <?php foreach ($Categories as $Category): ?>
                    <?php if ($Category['Category_Name'] !== 'Unspecified'):
                        ?>
                        <?php
                        $Category_ID = $Category['Category_ID'];
                        $SubCategoriesQuery = "SELECT SubCategory_ID, SubCategory_Name FROM SubCategories WHERE Category_ID = :Category_ID ORDER BY SubCategory_ID ASC";
                        $pdoSubCategories = $connexion->prepare($SubCategoriesQuery);
                        $pdoSubCategories->execute([':Category_ID' => $Category_ID]);
                        $SubCategories = $pdoSubCategories->fetchAll(PDO::FETCH_ASSOC);
                        ?>

                        <div class="relative category">
                            <a href=".././?id=<?php echo $Category['Category_ID'] ?>&Type=Category&Name=<?php echo str_replace(' ', '', $Category['Category_Name']) ?>"
                                class="px-3 py-2 hover:bg-gray-700"><?php echo $Category['Category_Name']; ?></a>
                            <?php if (!empty($SubCategories)): ?>
                                <div class="category-dropdown absolute top-full left-0 mt-1 bg-gray-800 rounded shadow-md p-2 hidden"
                                    style="min-width: 200px;">
                                    <?php foreach ($SubCategories as $SubCategory): ?>
                                        <?php if ($SubCategory['SubCategory_Name'] !== 'Unspecified'):

                                            ?>
                                            <a href=".././?id=<?php echo $SubCategory['SubCategory_ID'] ?>&Type=SubCategory&Name=<?php
                                               echo str_replace(' ', '', $SubCategory['SubCategory_Name']) ?>"
                                                class="block px-2 py-1 hover:bg-blue-600"><?php echo $SubCategory['SubCategory_Name']; ?></a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- User Links -->
            <div class="flex space-x-4">

                <a href="../User/User_SignIn.php"
                    class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Login</a>
                <a href="../User/User_SignUp.php"
                    class="text-gray-300 hover:bg-blue-700 px-3 py-2 rounded-md text-sm font-medium">Register</a>



            </div>

        </div>

    </nav>



    <div class="container mx-auto p-6">
        <div class="content-wrapper">
            <h1 class="text-2xl font-bold mb-6 text-center">Account Creation</h1>
            <form action="" method="POST" onsubmit="passwordConfirmation(event)"
                class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Section -->
                <div class="space-y-4">
                    <div>
                        <label for="User_Username" class="block text-sm font-medium text-gray-700">Username:</label>
                        <input type="text" name="User_Username" placeholder="Your Username"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="User_FirstName" class="block text-sm font-medium text-gray-700">First Name:</label>
                        <input type="text" name="User_FirstName" placeholder="Your First Name"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="User_LastName" class="block text-sm font-medium text-gray-700">Last Name:</label>
                        <input type="text" name="User_LastName" placeholder="Your Last Name"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="User_Phone" class="block text-sm font-medium text-gray-700">Phone Number:</label>
                        <input type="tel" name="User_Phone" placeholder="Example: 0714876397"
                            pattern="^([0-9]{2}){4}[0-9]{2}$"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="User_Country" class="block text-sm font-medium text-gray-700">Your Country:</label>
                        <select id="User_Country" name="User_Country"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                            <option value="" disabled selected>Select your country</option>
                        </select>
                    </div>
                </div>
                <!-- Right Section -->
                <div class="space-y-4">

                    <div>
                        <label for="User_Address" class="block text-sm font-medium text-gray-700">Your Address:</label>
                        <input type="text" name="User_Address" placeholder="Your Address"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <label for="User_Email" class="block text-sm font-medium text-gray-700">Email:</label>
                        <input type="email" name="User_Email" placeholder="Your Email Address"
                            class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            required>
                    </div>
                    <div>
                        <div>
                            <label for="Password" class="block text-sm font-medium text-gray-700">Password:</label>
                            <input type="password" name="User_Password" id="Password" placeholder="Your Password"
                                class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                oninput="checkForm()" required>
                        </div>
                        <div>
                            <label for="ConfirmPassword" class="block text-sm font-medium text-gray-700">Confirm
                                Password:</label>
                            <input type="password" id="ConfirmPassword" placeholder="Confirm Your Password"
                                class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                oninput="checkForm()" required>
                            <span id="Password_Error" class="text-red-600"></span>
                        </div>
                        <div class="flex items-center mt-2" style="height: 60px">
                            <input type="checkbox" id="termsCheckBox" name="agreeTerms"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                onchange="checkForm()">
                            <label for="agreeTerms" class="ml-2 block text-sm text-gray-900">By checking this box, you
                                agree to the <a href="#" class="text-blue-600 hover:underline">Terms of
                                    Service</a></label>
                        </div>

                        <!-- Full-width Buttons Section -->
                        <div class="md:col-span-2 flex justify-center">
                            <div class="space-y-2">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 rounded buttons"
                                    id="Submit" disabled>Sign Up</button>
                                <button type="reset"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 border-b-4 border-gray-500 rounded"
                                    onclick="resetForm()">Reset</button>
                                <br>
                                <?php if ($Error_Message != "") { ?>
                                    <p class="text-red-600 text-xs">Username or Email already taken. <br>Try a different one
                                    </p>

                                <?php } ?>




                            </div>

                        </div>

            </form>

            <div class="text-center mt-4">
                <span>Already have an account? <a href="User_SignIn.php" class="text-blue-500">Login Now</a></span>
            </div>
        </div>
    </div>







    <script>
        window.addEventListener('DOMContentLoaded', function () {
            adjustContentMargin();
            resetForm();
        });

        window.addEventListener('resize', function () {
            adjustContentMargin();
        });

        function adjustContentMargin() {
            var navHeight = document.querySelector('nav').offsetHeight;
            document.querySelector('.content-wrapper').style.marginTop = navHeight + 'px';
        }

        function checkForm() {
            var password = document.getElementById('Password').value;
            var confirmPassword = document.getElementById('ConfirmPassword').value;
            var termsCheckBox = document.getElementById('termsCheckBox');
            var submitButton = document.getElementById('Submit');
            var passwordError = document.getElementById('Password_Error');

            if (password === confirmPassword || password === '') {
                passwordError.textContent = "";
            } else {
                passwordError.textContent = "Passwords do not match, please double check!";
            }

            if (password === confirmPassword && termsCheckBox.checked && password !== '' && confirmPassword !== '') {
                submitButton.disabled = false;
                submitButton.style.backgroundColor = "#0070f3";
                submitButton.style.cursor = "pointer";
                submitButton.classList.add('hover:bg-blue-400');
            } else {
                submitButton.disabled = true;
                submitButton.style.backgroundColor = "gray";
                submitButton.style.cursor = "default";
            }
        }

        function resetForm() {
            checkForm(); // Ensure the form state is updated

        }







        // Fetch country data and populate the select element
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
                    countrySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching country data:', error);
            });

    </script>
</body>

</html>
<style>
    .buttons {
        background-color: gray;
        border-bottom: 4px solid gray;

    }

    .buttons:hover {
        background-color: gray;
        cursor: default;
    }

    .visibility-status {
        background-color: #EF4444;
        /* Red background */
        color: #FFFFFF;
        /* White text */
        padding: 0.25rem 0.5rem;
        /* Adjust padding */
        border-radius: 0.25rem;
        /* Rounded corners */
        font-size: 0.875rem;
        /* Adjust font size */
        font-weight: 500;
        /* Medium font weight */
        margin-top: 0.5rem;
        /* Add some space at the top */
        display: inline-block;
        /* Display as inline block */
        position: absolute;
        top: 0;
        right: 0;
        z-index: 1;
        /* Ensure it's above the image */
    }

    .category-dropdown {
        display: none;
    }

    .category:hover .category-dropdown {
        display: block;
    }

    #Logo {
        width: 30px;
        height: 34px;
    }

    nav {

        height: auto;
        position: fixed;

        width: 100%;

        z-index: 1000;

        margin-bottom: auto;
        opacity: 95%;
    }

    body {
        background-color: #e4e8f3;
    }

    .content-wrapper {
        padding-top: auto;

    }

    .card {
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .outer-container {
        display: flex;
        justify-content: center;
        width: 100%;
        padding-top: 16px;
    }

    .container {
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        width: 90%;
        max-width: 1500px;
        justify-content: space-between;
    }

    .navbar {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;

    }

    .content-wrapper {
        flex: 1;
        margin-top: 0;
        padding-top: 10px;

        overflow-y: auto;
    }

    .product-name {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;

        max-width: 100%;

        display: inline-block;
        max-height: 1.2em;

    }
</style>