<?php
session_start();
if(!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if(strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);
            return !$ret;
        }
    }
}

function login($login_email, $login_password, $login_dbc) {
    // Using prepared statements means that SQL injection is not possible.
    $salt = "grupristhebestappever!@#%!^#!$^";
    $stmt = mysqli_prepare($login_dbc, "SELECT password
                                        FROM user_info
                                        WHERE email = ?
                                        LIMIT 1");
    mysqli_stmt_bind_param($stmt, 's', $login_email);  // Bind "$username" to parameter.
    mysqli_stmt_execute($stmt);
    // Execute the prepared query.
    // get variables from result.
    mysqli_stmt_bind_result($stmt, $db_password);
    mysqli_stmt_fetch($stmt);
    $hashed_login_password = crypt($login_password, $salt);
    echo "$hashed_login_password<br>";
    if (hash_equals($db_password, $hashed_login_password)) {
       mysqli_stmt_close($stmt);
       echo "Password verified!<br>";
       return true;
    } else {
        mysqli_stmt_close($stmt);
        echo "Password incorrect <br>";
        return false;
    }

}

if(isset($_POST['submit']))
{
    $data_missing = array();

    if(empty($_POST['email'])){
        $data_missing[] = 'Email';
    } else {
        // Trim white space from the name and store the name
        $email = trim($_POST['email']);
        $email = strip_tags($email);
    }
    if(empty($_POST['password'])){
        $data_missing[] = 'Password';
    } else{
        // Trim white space from the name and store the name
        $p_word = trim($_POST['password']);
        $p_word = strip_tags($p_word);
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
        if(login($email, $p_word, $dbc) == true)
        {
            $_SESSION['email'] = $email;
            echo $_SESSION['email'];
            echo "<br>";
            echo $_SESSION['session_id'];
            echo "<br>";
            $stmt = mysqli_prepare($dbc, "UPDATE user_info SET session_id = ? WHERE email = ?");
            mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['session_id'], $_SESSION['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $stmt = mysqli_prepare($dbc, "SELECT user_id FROM user_info WHERE session_id = ? AND email = ?");
            mysqli_stmt_bind_param($stmt, 'ss', $_SESSION['session_id'], $_SESSION['email']);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $user_id);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            $_SESSION['user_id'] = $user_id;
            ?>
            <script>
                // window.location = 'http://grupr.dev/profile.php';
                window.location = 'http://zero-to-slim.dev/profile.php';
            </script>
            <?php
        } else {
            ?>
            <script>
                window.location = 'http://zero-to-slim.dev/login.php';
                // window.location = 'http://grupr.dev/login.php';
            </script>
            <?php
        }
    } else {
        echo 'You need to enter the following data<br />';
        foreach($data_missing as $missing){
            echo "$missing<br />";
        }
    }
}
mysqli_close($dbc);
?>
