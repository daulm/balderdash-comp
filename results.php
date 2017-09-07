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
$con = mysqli_connect($db_host, $db_username, $db_pw, 'balderdash');
if (!$con){
	die('DB connection failed: '.mysqli_error($con));
}
$donevote = FALSE;
$action_type = $_GET['mode'];
switch ($action_type){
	case "skip":
		//The user did not submit a vote insert a blank vote anyway
		
		break;
	case "submit":
		
		break;
	case "updeate":
		
		break;
}

if(isset($_SESSION['Dasher'])){
	//check if the time is up
	$sql = "SELECT l.VoteTime*60 - TIME_TO_SEC(TIMEDIFF(NOW(), g.LaunchVoteTime))";
	$sql .= " FROM lobby l, games g WHERE l.GameID = g.GameID AND l.LobbyID =".$_SESSION['Lobby_ID'];
	if(!$result = mysqli_query($con, $sql)){
		echo('Cant find voting timeout');
	}
	$row = mysqli_fetch_row($result);
	if($row[0] < -10){
		$donevote = TRUE;
	}
	// check if all players have submitted votes
	$sql = "SELECT p.PlayerID FROM players p";
	$sql .= " WHERE p.LobbyID = l.LobbyID AND l.LobbyID=".$_SESSION['Lobby_ID'];
	$sql .= " AND NOT EXISTS (SELECT v.VoteID FROM votes v WHERE v.PlayerID = p.PlayerID";
	$sql .= " AND v.GameID = l.GameID)";
	if(!$result = mysqli_query($con, $sql)){
		echo('Cant check whether voting is done.');
	}		
	if(mysqli_num_rows($result) == 0){
		$donevote = TRUE;	
	}
	if($donevote){
		// calculate and show the scores/results
		$sql = "SELECT v.AnswerID, v.PlayerID FROM votes v, lobby l";
		$sql .= " WHERE v.GameID = l.GameID AND  l.LobbyID=".$_SESSION['Lobby_ID'];
		if(!$votelist = mysqli_query($con, $sql)){
			echo('Cant findlist of votes.');
		}
		// pull all the answers with playernames
		$sql = "SELECT";
	} else {
		//show a waiting message
		
	}
} else {
	//check the game state
	
}
	



mysqli_close($con);
?>

<div id="footer" class="container text-center"><button type="button" class="btn btn-warning" onclick="returnLobby()">Return to the Lobby</button></div>
</body>
</html>
