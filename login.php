<?php
@ob_start();
session_start();
$id = session_id();
$_SESSION['session_id'] = $id;
echo $_SESSION['session_id'];
echo "<br>";
//usernames: 'user' and 'user2'
//passwords: 'passoword' and 'coolcats'
 ?>
<html>
<head>
<title>Login</title>
</head>
<body>

<form action="login2.php" method="post">

<p>Email:
<input type="text" name="email" size="255" value="" />
</p>

<p>Password:
<input type="password" name="password" size="255" value="" />
</p>

<p>
<input type="submit" name="submit" value="Send" />
</p>

</form>

<form action = "register.php" method="post">
<p>
<input type="submit" name="submit" value="Register" />
</p>

</form>
</body>
</html>
