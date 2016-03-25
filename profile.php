<?php
session_start();
$json_user_id = $_SESSION['user_id'];
$json_email = $_SESSION['email'];
$json_user_info_array = array('user_id' => $json_user_id, 'email' => $json_email);
echo json_encode($json_user_info_array);
echo "<br>";
$hostname = "localhost";
$username = "root";
$dbname = "grupr";
$password = "password";
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
$class_subject = $row['class_subject'];
$class_number = $row['class_number'];
$json_classes_array = array('class_subject' => $class_subject, 'class_number' => $class_number);
echo json_encode($json_classes_array);
// array_push($json_classes_array, 'class_subject' => $class_subject);
// array_push($json_classes_array, 'class_number' => $class_number);

echo '</tr>';
}

echo '</table>';

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
