<?php
session_start();

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
        $salt = "grupristhebestappever!@#%!^#!$^";
        $reg_password = crypt($reg_password, $salt);

    }



    if(empty($data_missing)){

        $hostname = "localhost";
        $username = "root";
        $dbname = "grupr";
        $password = "password";
        $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
        connect to database! Please try again later.");
        mysqli_select_db($dbc, $dbname);
        echo "Connected! <br>";
        echo "$reg_password <br>";

        $stmt = mysqli_prepare($dbc, "INSERT INTO user_info (email, password, session_id, first_name, last_name)
        VALUES (?,?,?,?,?)");
        mysqli_stmt_bind_param($stmt, 'sssss', $email, $reg_password, $_SESSION['session_id'], $f_name, $l_name);
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



    } else {

        echo 'You need to enter the following data<br />';

        foreach($data_missing as $missing){

            echo "$missing<br />";

        }

    }

}
mysqli_close($dbc);
?>
<script>
    window.location = 'http://grupr.dev/addclasses.php';
</script>
