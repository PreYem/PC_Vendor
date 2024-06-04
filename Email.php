<!DOCTYPE html>
<html>

<head>
    <title>Email Sender</title>
</head>

<body>

    <?php
    // Define variables and initialize with empty values
    $email = "";
    $email_err = "";

    // Processing form data when form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate email
        if (empty(trim($_POST["email"]))) {
            $email_err = "Please enter an email.";
        } else {
            $email = trim($_POST["email"]);
        }

        // If no errors, send the email
    
        try {

            if (empty($email_err)) {
                $to = $email;
                $subject = 'Hello World';
                $message = 'Hello World!';
                $headers = 'From: your-email@example.com' . "\r\n" .
                    'Reply-To: your-email@example.com' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                // Send the email
                if (mail($to, $subject, $message, $headers)) {
                    echo "<p>Email sent successfully to $email</p>";
                } else {
                    echo "<p>Failed to send email.</p>";
                }
            }

        } catch (PDOException $e) {
            $connexion->rollBack();
            echo "<p>Failed to send email AFTER TRY CATCH.</p>";
        }

    }
    ?>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <label>Email:</label>
        <input type="text" name="email" value="<?php echo $email; ?>">
        <span><?php echo $email_err; ?></span><br><br>
        <input type="submit" value="Send Email">
    </form>

</body>

</html>