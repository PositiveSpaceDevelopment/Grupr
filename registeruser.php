<?php
session_start();

//found at http://stackoverflow.com/questions/4356289/php-random-string-generator
function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
}

//found at http://stackoverflow.com/questions/4356289/php-random-string-generator
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if(isset($_POST['submit'])){

    $data_missing = array();

    if(empty($_POST['first_name'])){

        // Adds name to array
        $data_missing[] = 'First Name';

    } else {

        // Trim white space from the name and store the name
        $f_name = trim($_POST['first_name']);

    }

    if(empty($_POST['last_name'])){

        // Adds name to array
        $data_missing[] = 'Last Name';

    } else{

        // Trim white space from the name and store the name
        $l_name = trim($_POST['last_name']);

    }

    if(empty($_POST['email'])){

        // Adds name to array
        $data_missing[] = 'Email';

    } else {

        // Trim white space from the name and store the name
        $email = trim($_POST['email']);

    }

    if(empty($_POST['password'])){

        // Adds name to array
        $data_missing[] = 'Password';

    } else {

        // Trim white space from the name and store the name
        $reg_password = trim($_POST['password']);
        $salt = generateRandomString();
        $reg_password = crypt($reg_password, $salt);

    }


    if(endsWith($email, "@smu.edu") == true || endsWith($email, "@mail.smu.edu") == true)
    {
        if(empty($data_missing)){

            $hostname = "localhost";
            $username = "grupr";
            $dbname = "grupr";
            $password = "hunter2";
            $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
            connect to database! Please try again later.");
            mysqli_select_db($dbc, $dbname);

            $stmt = mysqli_prepare($dbc, "INSERT INTO user_info (email, password, session_id, first_name, last_name, salt)
            VALUES (?,?,?,?,?,?)");
            mysqli_stmt_bind_param($stmt, 'ssssss', $email, $reg_password, $_SESSION['session_id'], $f_name, $l_name, $salt);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['email'] = $email;
            $stmt = mysqli_prepare($dbc, "SELECT user_id FROM user_info WHERE session_id = ? AND email = ?");
            mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['session_id'], $_SESSION['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $user_id);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['user_id'] = $user_id;

            echo 'User Entered';
            ?>
            <script>
                // window.location = 'http://grupr.dev/addclasses.php';
                window.location = 'http://zero-to-slim.dev/addclasses.php';
            </script>
            <?php


        } else {

            echo 'You need to enter the following data<br />';

            foreach($data_missing as $missing){

                echo "$missing<br />";

            }
            ?>
            <script>
                // window.location = 'http://grupr.dev/addclasses.php';
                window.location = 'http://zero-to-slim.dev/regiser.php';
            </script>
            <?php
        }
    } else {
        echo "You need to enter a SMU email address ending in @smu.edu or @mail.smu.edu<br>";
        ?>
        <script>
            // window.location = 'http://grupr.dev/addclasses.php';
            window.location = 'http://zero-to-slim.dev/register.php';
        </script>
        <?php
    }


}
mysqli_close($dbc);
?>
