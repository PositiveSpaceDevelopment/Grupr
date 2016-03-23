<?php
session_start();

$hostname = "localhost";
$username = "root";
$dbname = "grupr";
$password = "password";
$dbc = mysqli_connect($hostname, $username, $password) OR DIE ("Unable to
connect to database! Please try again later.");
mysqli_select_db($dbc, $dbname);
echo "Connected! <br>";

echo "user_id = ";
echo $_SESSION['user_id'];
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

 ?>

 <html>
 <head>
 <title>Edit Profile</title>
 </head>
 <body>

     <form action="editprofilephp.php" method="post">

     <p>Class Subject to delete:
     <input type="text" name="class_subject" size="4" value="" />
     </p>

     <p>Class Number to delete:
     <input type="text" name="class_number" size="4" value="" />
     </p>

     <p>
     <input type="submit" name="submit" value="Delete class" />
     </p>

     </form>

     <form action="profile.php" method="post">

     <p>
     <input type="submit" name="submit" value="Go to profile!" />
     </p>

     </form>

 </body>
 </html>
