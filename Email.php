<!DOCTYPE html>
<html>

<head>
    <title>Email Sender</title>
</head>

<body>

    <?php

    require ("script.php");

    $response = "" ;

    if (empty($_POST['email']) || empty($_POST['subject'] || empty($_POST['message']))) {

        $response = "All fields are required" ;
    } else {
        $response = sendMail($_POST['email'], $_POST['subject'] , $_POST['message']) ;

    }


   
    ?>

    <form action="" method="post">
        <label>Email:</label>
        <input type="text" name="email" ><br>
        <label>Subject : </label>
        <input type="text" name="subject"><br>
        <label>Message : </label>
        <input type="text" name="message"><br>

        <input type="submit" value="Send Email">
        <?php if(@$response == "success") {
            echo "<p style='color:green'>Email Sent Successfully</p>";
        } else {
            echo "<p style='color:red'>$response</p>";
        }
            ?>
    </form>

</body>

</html>