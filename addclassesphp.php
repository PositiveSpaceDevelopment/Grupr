<html>
<head>
<title>Add Classes</title>
</head>
<body>
<?php
session_start();
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

        $stmt = mysqli_prepare($dbc, "INSERT INTO classes (class_subject, class_number)
        VALUES (?,?)");
        mysqli_stmt_bind_param($stmt, 'ss', $class_subject, $class_number);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        echo 'Class Entered';



    } else {

        echo 'You need to enter the following data<br />';

        foreach($data_missing as $missing){

            echo "$missing<br />";

        }

    }

}
mysqli_close($dbc);
 ?>


    <form action="addclassesphp.php" method="post">

    <p>Class Subject:
    <input type="text" name="class_subject" size="4" value="" />
    </p>

    <p>Class Number:
    <input type="text" name="class_number" size="4" value="" />
    </p>

    <p>
    <input type="submit" name="submit" value="Add another" />
    </p>

    </form>

    <form action="randompage.php" method="post">

    <p>
    <input type="submit" name="submit" value="Go to profile!" />
    </p>

    </form>

</body>
</html>
