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
	
if(isset($_SESSION['Dasher'])){
	// change game state 
	$sql = "UPDATE lobby SET GameState='vote'";
	$sql .= " WHERE LobbyID=".mysql_real_escape_string($_SESSION['Lobby_ID']);
	$sql .= " AND GameState = 'answer' AND DasherID=".$_SESSION['Player_ID'];
	if(!mysqli_query($con, $sql)){
		echo('Unable update the gamestate');
	}
	$sql = "UPDATE games SET LaunchVoteTime = NOW()";
	$sql .= " WHERE GameID = (SELECT l.GameID FROM lobby l";
	$sql .= " WHERE l.LobbyID =".$_SESSION['Lobby_ID'].")";
	if(!mysqli_query($con, $sql)){
		echo('Unable update the gamestate');
	}
	echo '<div class="alert alert-info">';
	echo '<strong>No need to vote</strong> Just click the button below and wait for the voting to finish.</div>';
} else {
	//look to see if they have already voted
	$sql = "SELECT v.VoteID FROM votes v, lobby l";
	$sql .= " WHERE v.PlayerID=".mysql_real_escape_string($_SESSION['Player_ID']);
	$sql .= " AND v.GameID = l.GameID AND l.LobbyID =".$_SESSION['Lobby_ID'];
	if(!$result = mysqli_query($con, $sql)){
		echo('Unable check if I voted');
	}
	if(mysqli_num_rows($result) == 0){
		//Look up all the available options 
		$sql = "SELECT a.AnswerText, a.AnswerID FROM answers a, lobby l";
		$sql .= " WHERE l.GameID = a.GameID AND a.BindAnswerID = 0 AND l.LobbyID=".$_SESSION['Lobby_ID'];
		$sql .= " AND a.PlayerID !=".$_SESSION['Player_ID'];
		if(!$result = mysqli_query($con, $sql)){
			echo('Unable check if I voted');
		}
		while($row = mysqli_fetch_row($result)){
			//Show the Answer Text and voting button
			
		}
	} else {
		// We found a vote from this player for this game, no more voting
		echo '<div class="alert alert-info">';
		echo '<strong>Looks like you already voted</strong> Just click the button below and wait for the voting to finish.</div>';
	}
}


mysqli_close($con);
?>
<br>
<div id="footer" class="text-center"><button type="button" class="btn btn-info" onclick="skipVote()">Skip voting and wait for results</button></div>

</body>
</html>
