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
	
//Look up the clue and time limit
$sql = "SELECT Clue, AnswerTime, DasherID FROM lobby";
$sql .= " WHERE LobbyID=".$_SESSION['Lobby_ID'];
if(!$result = mysqli_query($con, $sql)){
	echo('Cant find code for this lobby');
}	
while($row = mysqli_fetch_row($result)){
	$clue = $row[0];
	$timeleft = $row[1]*60;
	$dasherid = $row[2];
}

//if host, set up new game and change game state of lobby
if(isset($_SESSION['Host'])){
	//create new game 
	$sql = "INSERT INTO games (LobbyID, Clue, DasherID) VALUES (";
	$sql .= $_SESSION['Lobby_ID'].", '".$clue."', ".$dasherid.")";
	if(!mysqli_query($con, $sql)){
		echo('Unable to create Game');
	}
	$sql = "SELECT MAX(GameID) FROM games";
	if(!$result = mysqli_query($con, $sql)){
		echo('Unable to find newest Game');
	}
	$row = mysqli_fetch_row($result);
	$_SESSION['Game_ID'] = $row[0];
	
	$sql = "UPDATE lobby SET GameID=".$_SESSION['Game_ID'].", GameState='answer' WHERE LobbyID=".$_SESSION['Lobby_ID'];
	if(!mysqli_query($con, $sql)){
		echo('Unable to sync Game to Lobby');
	}
	
	//set a random order for player names/answers/scores to appear for this game
	$sql = "UPDATE players SET OrderVal=RAND() WHERE LobbyID=".$_SESSION['Lobby_ID'];
	if(!mysqli_query($con, $sql)){
		echo('Unable to sync Game to Lobby');
	}
}

//set the gameid to the session
$sql = "SELECT GameID FROM lobby";
$sql .= " WHERE LobbyID=".$_SESSION['Lobby_ID'];
if(!$result = mysqli_query($con, $sql)){
	echo('Cant find code for this lobby');
}	
while($row = mysqli_fetch_row($result)){
	$_SESSION['Game_ID'] = $row[0];
}
	
mysqli_close($con);
?>
  
<div class="container">
	<div class="well well-sm">
		<div class="col-xs-9"><?php echo htmlspecialchars($clue) ?></div>
	  	<div class="col-xs-3">Time Left: <span id="countdown" data-timeleft="<?php htmlspecialchars($timeleft) ?>"></span></div>
  	</div>
	
    	<div class="input-group">
      		<textarea class="form-control custom-control" rows="3" placeholder="Enter your answer" name="answertxt" id="answertxt"></textarea>
        	<span class="input-group-addon btn btn-primary" type="button" onclick="submitAnswer(1)">Submit</span>
    	</div>
</div>	
	
</body>
</html>
