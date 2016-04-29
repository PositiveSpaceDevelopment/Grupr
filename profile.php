<?php
session_start();

$json_user_id = $_SESSION['user_id'];
$json_email = $_SESSION['email'];
$json_user_info_array = array('user_id' => $json_user_id, 'email' => $json_email);
echo json_encode($json_user_info_array);
echo "<br>";
echo "<br>";
$hostname = "localhost";
$username = "grupr";
$dbname = "grupr";
$password = "hunter2";
$dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
connect to database! Please try again later.");
mysqli_select_db($dbc, $dbname);

// Create a query for the database
$profile_user_id = $_SESSION['user_id'];
$quote = "'";
$profile_user_id = $quote . $profile_user_id . $quote;
$query = "SELECT class_subject, class_number FROM classes WHERE user_id = $profile_user_id";
$result = mysqli_query($dbc, $query);


// If the query executed properly proceed
if($result){
// $json_classes_array = array();

// mysqli_fetch_array will return a row of data from the query
// until no further data is available
while($row = mysqli_fetch_array($result)) {

$class_subject = $row['class_subject'];
$class_number = $row['class_number'];
$json_classes_array = array('class_subject' => $class_subject, 'class_number' => $class_number);
echo json_encode($json_classes_array);
echo "<br>";

}

}
echo "<br>";

$query = "SELECT first_name, last_name FROM user_info WHERE user_id = $profile_user_id";
$result = mysqli_query($dbc, $query);
if($result)
{
    $row = mysqli_fetch_array($result);
    $first_name = $row['first_name'];
    $last_name = $row['last_name'];
    $json_name_array = array('first_name' => $first_name, 'last_name' => $last_name);
    echo json_encode($json_name_array);
}

echo "<br>";
// echo json_encode($json_classes_array);


 ?>

</form>
<form action="editprofile.php" method="post">

<p>
<input type="submit" name="submit" value="Edit profile" />
</p>

</form>

</form>
<form action="logout.php" method="post">

<p>
<input type="submit" name="submit" value="Logout" />
</p>

</form>
