<!DOCTYPE html>
<html>
<head>

</head>
<body>
<?php
include 'db_config.php';
session_name('bd');
session_start();
if (!isset($_SESSION['Player_ID'])){
	die('Session lost, please reload the app.');
}

$con = mysqli_connect($db_host, $db_username, $db_pw, 'balderdash');
if (!$con){
	die('DB connection failed: '.mysqli_error($con));
}

$code = "";
$action_type = $_GET['mode'];
switch ($action_type){
	case "create":
		//randomly generate a code
		$alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		while(true){
			$code = "";
			$code = substr($alphabet, rand(0,25), 1);
			$code .= substr($alphabet, rand(0,25), 1);
			$code .= substr($alphabet, rand(0,25), 1);
			$code .= substr($alphabet, rand(0,25), 1);
			
			$sql = "SELECT * FROM lobby WHERE Code='".$code."'";
			if(!$result = mysqli_query($con, $sql)){
				echo('Unable to verify the code is unique');
			}
			if(mysqli_num_rows($result) == 0){
				break;
			}
		}
		// Create the lobby
		$sql = "INSERT INTO lobby (Code, HostID, DasherID, AnswerTime, VoteTime)";
		$sql .= " VALUES ('".$code."', ".$_SESSION['Player_ID'].", ".$_SESSION['Player_ID'].", 2, 1)";
		if(!mysqli_query($con, $sql)){
			echo('Unable to create the lobby');
		}
		$sql = "SELECT MAX(LobbyID) FROM lobby";
		if(!$result = mysqli_query($con, $sql)){
			echo('Unable to find new lobby');
		}
		while($row = mysqli_fetch_row($result)){
			$_SESSION['Lobby_ID'] = $row[0];
		}
		$ply_name = mysqli_real_escape_string($con, $_POST['pname']);
		//update the current lobby the player is in and their name
		$sql = "UPDATE players SET LobbyID=".$_SESSION['Lobby_ID'].", PlayerName='".$ply_name."'";
		$sql .= " WHERE PlayerID=".$_SESSION['Player_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}		
		
		$_SESSION['Host'] = true;
		$_SESSION['Dasher'] = true;
		break;
	case "join":
		// check that the code is correct and the lobby was created in the past 24 hours
		$sq_code = mysqli_real_escape_string($con, $_POST['code']);
		$sql = "SELECT MAX(LobbyID) FROM lobby WHERE Code=UPPER('".$sq_code."') AND CreationTime > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
		if(!$result = mysqli_query($con, $sql)){
			echo('Cant find a Lobby with the given code.');
		}		
		if(mysqli_num_rows($result) == 0){
			echo ('<frame onload="alert(\'Cant find a Lobby with the given code\')">wrong code</frame>');
			exit('<br><frame onload="mainMenu()">Return to Main</frame>');
			break;
		}		
		while($row = mysqli_fetch_row($result)){
			$_SESSION['Lobby_ID'] = $row[0];
		}
		$code = mysqli_real_escape_string($con, $_POST['code']);
		$pname = mysqli_real_escape_string($con, $_POST['pname']);
		//If a player already exists with the chosen name, add [the number of current players] to the end of their name
		$sql = "SELECT p.PlayerName, (SELECT COUNT(*) FROM players WHERE LobbyID =".$_SESSION['Lobby_ID'].") as num";
		$sql .= " FROM players p, lobby l";
		$sql .= " WHERE p.LobbyID = l.LobbyID AND l.LobbyID=".$_SESSION['Lobby_ID'];
		$sql .= " AND p.LastCheck > NOW() - INTERVAL 15 SECOND";
		$sql .= " AND p.PlayerName ='".$pname."'";
		if(!$result = mysqli_query($con, $sql)){
			echo('Cant find players in this game');
		}
		if(mysqli_num_rows($result) > 0){
			//There was a player who already has that name
			while($row = mysqli_fetch_row($result)){
				$pname .= $row[1];	
			}
		}
		
		// Add player to the lobby and update their name
		$sql = "UPDATE players SET LobbyID=".$_SESSION['Lobby_ID'].", PlayerName='".$pname."'";
		$sql .= " WHERE PlayerID=".$_SESSION['Player_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}		
				
		break;
	case "update":
		//update last time player checked in
		$sql = "UPDATE players SET LastCheck=NOW()";
		$sql .= " WHERE PlayerID=".$_SESSION['Player_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to check in player');
		}
		break;
	case "settings":
		$sq_anstime = mysqli_real_escape_string($con, $_POST['anstime']);
		$sq_votetime = mysqli_real_escape_string($con, $_POST['votetime']);
		$sql = "UPDATE lobby SET AnswerTime=".$sq_anstime.", VoteTime=".$sq_votetime;
		$sql .= " WHERE HostID=".$_SESSION['Player_ID']." AND LobbyID=".$_SESSION['Lobby_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}		
		break;
	case "clue":
		$sq_clue = mysqli_real_escape_string($con, $_POST['clue']);
		$sql = "UPDATE lobby SET Clue='".$sq_clue."'";
		$sql .= " WHERE DasherID=".$_SESSION['Player_ID']." AND LobbyID=".$_SESSION['Lobby_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}
		break;
	case "dasher":
		$sq_dasher = mysqli_real_escape_string($con, $_POST['dasherid']);
		$sql = "UPDATE lobby SET DasherID=".$sq_dasher;
		$sql .= " WHERE HostID=".$_SESSION['Player_ID']." AND LobbyID=".$_SESSION['Lobby_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}
		break;
	case "dasherscore":
		$sq_dscore = mysqli_real_escape_string($con, $_POST['dasherscore']);
		$sql = "UPDATE players SET Score=".$sq_dscore;
		$sql .= " WHERE PlayerID= (SELECT DasherID FROM lobby";
		$sql .= "  WHERE HostID=".$_SESSION['Player_ID']." AND LobbyID=".$_SESSION['Lobby_ID'].")";
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}
		break;
	case "return":
		if(isset($_SESSION['Host'])){
			//This resets the GameID and Clue and then increments the dasher to be the next person in line
			$sql = "UPDATE lobby lob SET lob.GameID=0, lob.Clue='', lob.DasherID=(SELECT IF(ISNULL(MIN(p.PlayerID)),";
			$sql .= " (SELECT MIN(f.PlayerID) FROM players f WHERE f.LobbyID=".$_SESSION['Lobby_ID']."), MIN(p.PlayerID))";
			$sql .= "FROM players p, games g WHERE g.GameID = (SELECT MAX(n.GameID) FROM games n WHERE n.LobbyID=".$_SESSION['Lobby_ID'].")";
			$sql .= "AND p.LobbyID =".$_SESSION['Lobby_ID']." AND p.PlayerID > g.DasherID)";
			$sql .= " WHERE lob.LobbyID=".$_SESSION['Lobby_ID'];
			if(!mysqli_query($con, $sql)){
				echo('Unable to return to the lobby');
			}
		}
	default:			
}

//query to pull room code, game state, clue, dasher score, and time limits
$sql = "SELECT l.Code, l.GameState, l.AnswerTime, l.VoteTime, dp.Score, l.Clue, l.DasherID FROM lobby l, players p, players dp";
$sql .= " WHERE p.LobbyID = l.LobbyID AND p.PlayerID=".$_SESSION['Player_ID'];
$sql .= " AND l.DasherID = dp.PlayerID";
if(!$result = mysqli_query($con, $sql)){
	echo('Cant find code for this lobby');
	exit('<button type="button" class="btn btn-warning" onclick="mainMenu()">Return to Main</button>');
}	
while($row = mysqli_fetch_row($result)){
	$code = $row[0];
	$gamestate = $row[1];
	$anstime = $row[2];
	$votetime = $row[3];
	$dasherscore = $row[4];
	$clue = $row[5];
	if($row[6] == $_SESSION['Player_ID']){
		$_SESSION['Dasher']=true;
	} else {
		unset($_SESSION['Dasher']);
	}
}

//query to pull player list
$sql = "SELECT p.PlayerName, p.PlayerID, l.HostID, l.DasherID, p.Score";
$sql .= " FROM lobby l, players p";
$sql .= " WHERE p.LobbyID = l.LobbyID AND l.LobbyID=".$_SESSION['Lobby_ID'];
$sql .= " AND p.LastCheck > NOW() - INTERVAL 30 SECOND ORDER BY p.PlayerID";
if(!$playerlist = mysqli_query($con, $sql)){
	echo('Cant find players in this game');
}


?>
<span id="msglist" data-gamestate="<?php echo $gamestate ?>"></span>
<div id="titleback">
	<div class="text-center" id="title">BALDERDASH</div>
</div>
<div class="container-fluid">
	<div class="col-xs-6"><h2 class="text-left">Room Code:<b> <?php echo $code ?></b></h2></div>
	<div class="col-xs-6 text-right"><div class="input-group settings">
		<?php
		//if you are host show form for changing scores
		if(isset($_SESSION['Host'])){
			echo '<input id="player_score" type="text" class="form-control" value="'.$dasherscore.'" size="4">';
    			echo '<div class="input-group-btn">';
      			echo '	<button class="btn btn-success" type="submit" onclick="updateDasherScore()">';
        		echo '	<i class="glyphicon glyphicon-floppy-disk"></i>';
      			echo '  </button>';
    			echo '</div>';
		}
		?>
	</div></div>
</div>
<div id="players" class="container-fluid">
<?php
$clicky = "";
if(isset($_SESSION['Host'])){
	$clicky = " clicky";
}
while($row = mysqli_fetch_row($playerlist)){
	//[0]-playername [1]-PlayerID [2]-HostID [3]-DasherID [4]-Score
	if($row[1] == $row[2]){
		// this player should be identified as the host
		$note = "<i>(H)</i>";
	}else {
		$note = "";
	}
	if($row[1] == $row[3]){
		// this player should be identified as the dasher
		$dasher = " label label-success";
	}else {
		$dasher = "";
	}
	echo '<div class="col-xs-6 col-sm-4 col-md-3 text-center'.$clicky.$dasher.'" data-playerid="'.$row[1].'">'.$row[0].$note;
	echo '<span class="badge">'.$row[4].'</span></div>';
}
echo '</div><br>';

if(isset($_SESSION['Host'])){
	//show form for settings and button for kick off
	?>
	<div class="container-fluid well well-sm row settings" id="settings">
		<div class="col-xs-5 col-sm-4">
			<div class="input-group">
				<span class="input-group-addon">AnsTime</span>
				<input type="text" name="anstime" id="anstime" class="form-control" maxlength="4" size="4" value="<?php echo htmlspecialchars($anstime) ?>">
			</div>
		</div>
		<div class="col-xs-5 col-sm-4">
			<div class="input-group">
				<span class="input-group-addon">Vote Time</span>
				<input type="text" name="votetime" id="votetime" class="form-control" maxlength="4" size="4" value="<?php echo htmlspecialchars($votetime) ?>">
			</div>
		</div>
		<div class="col-xs-2 col-sm-2">
			<button type="submit" class="btn btn-default" onclick="lobbySettings()"><span class="glyphicon glyphicon-floppy-disk"></span></button>
		</div>
		<div class="col-xs-2 col-sm-2">
			<button type="submit" class="btn btn-info" onclick="launchGame()">Start Round</span></button>
		</div>
	</div>
	<?php
} else {
	//show settings
	echo '<div class="container-fluid well well-sm text-center">Answer Time: '.$anstime;
	echo 'min | Vote Time: '.$votetime.'min</div>';
}

	echo '<div class="panel panel-info">';
	echo '    <div class="panel-heading">'.htmlspecialchars($clue).'</div>';
	echo '</div>';

if(isset($_SESSION['Dasher'])){
	?>
	<div class="container">
    		<div class="input-group">
      			<span class="input-group-addon primary">Clue:</span>
      			<textarea class="form-control custom-control settings" rows="3" placeholder="Enter the clue" name="cluetxt" id="cluetxt"></textarea>
        		<span class="input-group-addon btn btn-primary" type="button" onclick="updateClue()">Submit</span>
    		</div>
	</div>
	<?php
}

mysqli_close($con);
?>

<div id="footer" class="container text-center"><button type="button" class="btn btn-warning" onclick="if(confirm('You want to Quit?')){mainMenu(1)}">Quit to Main</button></div>


</body>
</html>
