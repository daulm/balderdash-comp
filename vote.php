<!DOCTYPE html>
<html>
<head>

</head>
<body>
<?php
include 'db_config.php';
session_start();
if (!isset($_SESSION['Player_ID'])){
	die('Session lost, please reload the app.');
}
$con = mysqli_connect($db_host, $db_username, $db_pw, 'spyfall');
if (!$con){
	die('DB connection failed: '.mysqli_error($con));
}


mysqli_close($con);
?>
</div>
<div id="footer"><button onclick="mainMenu()">Leave the Game</button></div>
</body>
</html>
