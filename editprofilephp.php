<?php
session_start();
?>
<html>
<head>
<title>Edit Profile</title>
</head>
<body>
<?php
echo "user id = ";
echo $_SESSION['user_id'];
echo "<br>";
echo "session_id = ";
echo $_SESSION['session_id'];
echo "<br>";
echo "email = ";
echo $_SESSION['email'];
echo "<br>";
if(isset($_POST['submit'])){

    $data_missing = array();

    if(empty($_POST['class_subject'])){

        // Adds name to array
        $data_missing[] = 'Class subject';

    } else {

        // Trim white space from the name and store the name
        $class_subject = trim($_POST['class_subject']);

    }

    if(empty($_POST['class_number'])){

        // Adds name to array
        $data_missing[] = 'Class number';

    } else{

        // Trim white space from the name and store the name
        $class_number = trim($_POST['class_number']);

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

        $stmt = mysqli_prepare($dbc, "DELETE FROM classes WHERE class_subject = ? AND class_number = ? AND user_id = ?");
        mysqli_stmt_bind_param($stmt, 'sss', $class_subject, $class_number, $_SESSION['user_id']);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo 'Class Deleted';

        $profile_user_id = $_SESSION['user_id'];
        $quote = "'";
        $profile_user_id = $quote . $profile_user_id . $quote;
        echo "<br>";
        $query = "SELECT class_subject, class_number FROM classes WHERE user_id = $profile_user_id";
        $result = mysqli_query($dbc, $query);

        if($result){

        echo '<table align="left"
        cellspacing="5" cellpadding="8">

        <tr><td align="left"><b>Class Subject</b></td>

        <td align="left"><b>Class Number</b></td></tr>';

        // mysqli_fetch_array will return a row of data from the query
        // until no further data is available
        // while($row = mysqli_fetch_array($response)){
        while($row = mysqli_fetch_array($result)) {

        echo '<tr><td align="left">' .
        $row['class_subject'] . '</td><td align="left">' .
        $row['class_number'] . '</td><td align="left">';

        echo '</tr>';
        }

        echo '</table>';

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


    <form action="editprofilephp.php" method="post">

    <p>Class Subject to delete:
    <input type="text" name="class_subject" size="4" value="" />
    </p>

    <p>Class Number to delete:
    <input type="text" name="class_number" size="4" value="" />
    </p>

    <p>
    <input type="submit" name="submit" value="Add another" />
    </p>

    </form>

    <form action="profile.php" method="post">

    <p>
    <input type="submit" name="submit" value="Go to profile!" />
    </p>

    </form>

</body>
</html>
