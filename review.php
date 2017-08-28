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

$action_type = $_GET['mode'];
switch ($action_type){
	case "submit":
		// Add the answer
		$sql = "INSERT INTO answer (Code, HostID, SpyCount, TimeLimit) VALUES ('".$code."', ".$_SESSION['Player_ID'].", 1, 5)";
		if(!mysqli_query($con, $sql)){
			echo('Unable to submit the answer');
		}
		break;
	case "update":
		if($_SESSION['Dasher']){
			
		} else {
			//look up the game state
			
			echo '<div class="alert alert-info">';
			echo '<strong>Hang On!</strong> The Dasher is still reviewing submissions.</div>';
		}
		break;
		
	default:
}



mysqli_close($con);
?>
<div id="footer"><button onclick="mainMenu()">Leave the Game</button></div>
</body>
</html>
