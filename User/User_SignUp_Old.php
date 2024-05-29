<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../Logo.png" type="image/x-icon">
    <link href="../output.css" rel="stylesheet">
    <title>Sign Up</title>
    <?php
    include_once ("../DB_Connexion.php");

    $Error_Message = '';

    if (!empty($_POST)) {
        if (!empty($_POST["User_Username"])) {
            $User_Username = $_POST["User_Username"];
            $User_Username_LowerCase = strtolower($_POST["User_Username"]);

            // 
            $QueryCheck = "SELECT User_ID FROM Users WHERE LOWER(User_Username) = :User_Username_LowerCase";
            $pdostmtCheck = $connexion->prepare($QueryCheck);
            $pdostmtCheck->execute(["User_Username_LowerCase" => $User_Username_LowerCase]);

            $Existing_User_Username = $pdostmtCheck->fetch(PDO::FETCH_ASSOC);


            if (!$Existing_User_Username) {
                $Password = $_POST["User_Password"];

                // Set the timezone to Morocco (Western European Time - WET)
                $moroccoTimezone = new DateTimeZone('Africa/Casablanca');

                // Create a DateTime object with the current date/time in Morocco's timezone
                $moroccoDateTime = new DateTime('now', $moroccoTimezone);

                // Format the date/time for database storage (YYYY-MM-DD HH:MM:SS)
                $moroccoFormattedDateTime = $moroccoDateTime->format('Y-m-d H:i:s');

                // Hash the password using bcrypt
                $User_Hashed_Password = password_hash($Password, PASSWORD_BCRYPT);

                // Prepare and execute the INSERT query to insert the user into the database
                $Insert_User_Query = "INSERT INTO Users (User_Username, User_FirstName, User_LastName, User_Phone, User_Country, User_Address, 
                                              User_Email, User_Password, User_RegisterationDate)
                                      VALUES (:User_Username, :User_FirstName, :User_LastName, :User_Phone, :User_Country, :User_Address, :User_Email, 
                                              :User_Password, :User_RegisterationDate)";

                $pdostmtUser = $connexion->prepare($Insert_User_Query);

                try {
                    $pdostmtUser->execute([
                        'User_Username' => $_POST['User_Username'],
                        'User_FirstName' => $_POST['User_FirstName'],
                        'User_LastName' => $_POST['User_LastName'],
                        'User_Phone' => $_POST['User_Phone'],
                        'User_Country' => $_POST['User_Country'],
                        'User_Email' => $_POST['User_Email'],
                        'User_Address' => $_POST['User_Address'],
                        'User_Password' => $User_Hashed_Password,
                        'User_RegisterationDate' => $moroccoFormattedDateTime
                    ]);
                } catch (PDOException $e) {
                    // Handle database error
                    echo "Error inserting user: " . $e->getMessage();
                    exit;
                }
            } else {
                $Error_Message = 'A user with that username already exists!';
            }
        }
    }
    ?>

<body class="bg-gray-200 min-h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">

        <h1 class="text-2xl font-bold mb-6 text-center">Sign up</h1>

        <form action="" method="POST" onsubmit="passwordConfirmation(event)">
            <div class="grid grid-cols-1 gap-4">
                <label for="User_Username" class="block text-sm font-medium text-gray-700">Username:</label>
                <input type="text" name="User_Username" placeholder="Your Username"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="User_FirstName" class="block text-sm font-medium text-gray-700">First Name:</label>
                <input type="text" name="User_FirstName" placeholder="Your First Name"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="User_LastName" class="block text-sm font-medium text-gray-700">Last Name:</label>
                <input type="text" name="User_LastName" placeholder="Your Last Name"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="User_Phone" class="block text-sm font-medium text-gray-700">Phone Number:</label>
                <input type="tel" name="User_Phone" placeholder="Example: 0714876397" pattern="^([0-9]{2}){4}[0-9]{2}$"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="User_Country" class="block text-sm font-medium text-gray-700">Your Country:</label>
                <select id="User_Country" name="User_Country"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
                    <option value="" disabled selected>Select your country</option>
                </select>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="User_Address" class="block text-sm font-medium text-gray-700">Your Address:</label>
                <input type="text" name="User_Address" placeholder="Your Address"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="User_Email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="User_Email" placeholder="Your Email Address"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="Password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" name="User_Password" id="Password" placeholder="Your Password"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    oninput="passwordConfirmation()" required>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <label for="ConfirmPassword" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
                <input type="password" id="ConfirmPassword" placeholder="Confirm Your Password"
                    class="border border-gray-300 rounded-md px-3 py-2 w-full focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                    oninput="passwordConfirmation()" required>
                <span id="Password_Error" class="text-red-600"></span>
            </div>

            <div class="grid grid-cols-1 gap-4">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 rounded"
                    id="Submit" disabled>Sign Up</button>
                <button type="reset"
                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 border-b-4 border-gray-500 rounded">Reset</button>
            </div>

        </form>

        <div class="text-center mt-4">
            <span>Already have an account? <a href="User_SignIn.php" class="text-blue-500">Login</a></span>
        </div>

    </div>

    <script>
        function passwordConfirmation() {
            var password = document.getElementById('Password').value;
            var confirmPassword = document.getElementById('ConfirmPassword').value;
            var submitButton = document.getElementById('Submit');
            var passwordError = document.getElementById('Password_Error');

            if (password === confirmPassword) {
                passwordError.textContent = "";
                submitButton.disabled = false;
            } else {
                passwordError.textContent = "Passwords do not match, please double check!";
                submitButton.disabled = true;
            }
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


</html>