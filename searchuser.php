<?php
session_start();
if(isset($_POST['submit']))
{

    $hostname = "localhost";
    $username = "grupr";
    $dbname = "grupr";
    $password = "hunter2";
    $dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
    connect to database! Please try again later.");
    mysqli_select_db($dbc, $dbname);
    $post_first_name = $_POST['first_name'];
    $post_last_name = $_POST['last_name'];

    if(empty($_POST['first_name']) && !empty($_POST['last_name']))
    {
        $last_name = trim($_POST['last_name']);
        $stmt = mysqli_prepare($dbc, "SELECT first_name, last_name FROM user_info WHERE last_name = ?");
        mysqli_stmt_bind_param($stmt, 's', $last_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $first_names, $last_names);
        while(mysqli_stmt_fetch($stmt))
        {
            printf("%s %s<br>", $first_names, $last_names);
        }
        mysqli_stmt_close($stmt);
    }
    if(!empty($_POST['first_name']) && empty($_POST['last_name'])) {
        $first_name = trim($_POST['first_name']);

        $stmt = mysqli_prepare($dbc, "SELECT first_name, last_name FROM user_info WHERE first_name = ?");
        mysqli_stmt_bind_param($stmt, 's', $first_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $first_names, $last_names);
        while(mysqli_stmt_fetch($stmt))
        {
            printf("%s %s<br>", $first_names, $last_names);
        }
        mysqli_stmt_close($stmt);
    }
    if (!empty($_POST['first_name']) && !empty($_POST['last_name'])){
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);

        $stmt = mysqli_prepare($dbc, "SELECT first_name, last_name FROM user_info WHERE first_name = ? AND last_name = ?");
        mysqli_stmt_bind_param($stmt, 'ss', $first_name, $last_name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $first_names, $last_names);
        while(mysqli_stmt_fetch($stmt))
        {
            printf("%s %s<br>", $first_names, $last_names);
        }
        mysqli_stmt_close($stmt);
    }

}
mysqli_close($dbc);
 ?>
