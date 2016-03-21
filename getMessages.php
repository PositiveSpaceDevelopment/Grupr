<?php
//Issue: Grup Detail GET Messages (title and text)
  //more-or-less pseudo cose 
@ob_start();
session_start();
$id = session_id();
$_SESSION['session_id'] = $id;
echo $_SESSION['session_id'];
echo "<br>";


?>
 
<html>
<head>
<title>Login</title>
</head>
<body>

<form action="getMessages2.php" method="post">




</body>
</html>
