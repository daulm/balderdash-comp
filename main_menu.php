<!DOCTYPE html>
<?php
include 'db_config.php';

// First check and see if the player was in a Lobby, then remove them from the Lobby
session_start();
unset($_SESSION['Host']);
unset($_SESSION['Game_ID']);

// establish connection to DB
$con = mysqli_connect($db_host, $db_username, $db_pw, 'spyfall');
if (!$con){
	die('DB connection failed: '.mysqli_error($con));
}

if (isset($_SESSION['Lobby_ID']) AND isset($_SESSION['Player_ID'])) {
	// query to purge player from Lobby
	$sql = "UPDATE players SET LobbyID=NULL WHERE PlayerID=".$_SESSION['Player_ID'];
	if(!mysqli_query($con, $sql)){
		echo('Unable to clear player from Lobby.');
	}
	unset($_SESSION['Lobby_ID']);
}

$player_name = "";

if (isset($_SESSION['Player_ID'])) {
	//query to pull player name
	$sql = "SELECT PlayerName FROM players WHERE PlayerID=".$_SESSION['Player_ID'];
	if(!$result = mysqli_query($con, $sql)){
		echo('Unable to find player name');
	}
	while($row = mysqli_fetch_row($result)){
		$player_name = $row[0];
	}
} else {
	$sql = "INSERT INTO players (PlayerName, LobbyID) VALUES ('Newbie', NULL)";
	if(!mysqli_query($con, $sql)){
		echo('Unable to add new players');
	}
	$sql = "SELECT MAX(PlayerID) FROM players";
	if(!$result = mysqli_query($con, $sql)){
		echo('Unable to find new userID');
	}
	while($row = mysqli_fetch_row($result)){
		$_SESSION['Player_ID'] = $row[0];
	}
}
mysqli_close($con);
?>
<html>
<head>

</head>

<body>

<h2 id="banner">Spyfall</h2>

<form name="entergame" id="entergame" method="post" action="" onSubmit="">
Name:<br>
<input type="text" name="playername" id="playername" maxlength="20" size="12" value="<?php echo htmlspecialchars($player_name) ?>"><br>
Room Code:<br>
<input type="text" name="code" id="code" maxlength="4" size="4"></form><br>
<button id="join" onclick="enterLobby()">Join Game</button>
<br>
<button id="host" onclick="hostGame()">Host Game</button>

</body>
</html>