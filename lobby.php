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
		$sql = "INSERT INTO lobby (Code, HostID, SpyCount, TimeLimit) VALUES ('".$code."', ".$_SESSION['Player_ID'].", 1, 5)";
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
		
		//update the current lobby the player is in and their name
		$sql = "UPDATE players SET LobbyID=".$_SESSION['Lobby_ID'].", PlayerName='".$_POST['pname']."'";
		$sql .= " WHERE PlayerID=".$_SESSION['Player_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}		
		
		$_SESSION['Host'] = true;
		break;
	case "join":
		// check that the code is correct and the lobby was created in the past 24 hours
		$sql = "SELECT LobbyID FROM lobby WHERE Code=UPPER('".$_POST['code']."') AND CreationTime > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
		if(!$result = mysqli_query($con, $sql)){
			echo('Cant find a Lobby with the given code.');
		}		
		if(mysqli_num_rows($result) == 0){
			echo('Cant find a Lobby with the given code');
			break;
		}		
		while($row = mysqli_fetch_row($result)){
			$_SESSION['Lobby_ID'] = $row[0];
		}
		$code = $_POST['code'];
		$pname = $_POST['pname'];
		//If a player already exists with the chosen name, add [the number of current players] to the end of their name
		$sql = "SELECT p.PlayerName, (SELECT COUNT(*) FROM players WHERE LobbyID =".$_SESSION['Lobby_ID'].") as num";
		$sql .= " FROM players p";
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
		$sql = "UPDATE lobby SET SpyCount=".$_POST['spy_count'].", TimeLimit=".$_POST['timelimit'].", AllSpy=".$_POST['allspy'];
		$sql .= " WHERE HostID=".$_SESSION['Player_ID']." AND LobbyID=".$_SESSION['Lobby_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to add player ID to the lobby');
		}		
		break;
	case "return":
		$sql = "UPDATE lobby SET GameID=0 WHERE LobbyID=".$_SESSION['Lobby_ID'];
		if(!mysqli_query($con, $sql)){
			echo('Unable to return to the lobby');
		}
	default:			
}

//query to pull room code, spy count, time limit, and see if game launched
$sql = "SELECT l.Code, l.GameID, l.SpyCount, l.TimeLimit, l.AllSpy FROM lobby l, players p";
$sql .= " WHERE p.LobbyID = l.LobbyID AND p.PlayerID=".$_SESSION['Player_ID'];
if(!$result = mysqli_query($con, $sql)){
	echo('Cant find code for this lobby');
}	
while($row = mysqli_fetch_row($result)){
	$code = $row[0];
	$gamestate = $row[1];
	$spycount = $row[2];
	$timelimit = $row[3];
	if($row[4]){
		$allspy = 'checked="checked"';
	} else {
		$allspy = "";
	}
}

//query to pull player list
$sql = "SELECT p.PlayerName, p.PlayerID, l.HostID, IF(p.LastCheck > NOW() - INTERVAL 15 SECOND, 'blue', 'red') AS Active";
$sql .= " FROM lobby l, players p";
$sql .= " WHERE p.LobbyID = l.LobbyID AND l.LobbyID=".$_SESSION['Lobby_ID'];
if(!$playerlist = mysqli_query($con, $sql)){
	echo('Cant find players in this game');
}


?>
<span id="msglist" data-gamestate="<?php echo $gamestate ?>"></span>
<div id="titleback">
	<div class="text-center" id="title">BALDERDASH</div>
</div>
<h2 class="text-center">Room Code:<b> <?php echo $code ?></b></h2>
<div id="players">
<?php
while($row = mysqli_fetch_row($playerlist)){
	//[0]-playername [1]-PlayerID [2]-HostID [3]-active?
	if($row[1] == $row[2]){
		// this player should be identified as the host
		$note = "<i>(Host)</i>";
	}else {
		$note = "";
	}
	echo '<section style="color:'.$row[3].'">'.$row[0].$note."</section>";
}
echo '</div><br><div id="settings">';

if(isset($_SESSION['Host'])){
	//show form for settings and button for kick off
	echo 'Spy Count:<input type="text" name="spy_count" id="spy_count" maxlength="2" size="1" onfocus="stopRefresh()" value="';
	echo $spycount.'"> ';
	echo 'Time Limit:<input type="text" name="timelimit" id="timelimit" maxlength="2" size="2" onfocus="stopRefresh()" value="';
	echo $timelimit.'">min ';
	echo '<input type="checkbox" name="allspy" id="allspy" onfocus="stopRefresh()" value="included" ';
	echo $allspy.'>Enable all-spy games ';
	echo '<button onclick="lobbySettings()">Update</button><br>';
	echo '<button onclick="launchGame(0)">Start Game</button>';
} else {
	//show settings
	echo 'Spy Count:'.$spycount;
	echo ' Time Limit:'.$timelimit.'min';
}
mysqli_close($con);
?>
</div>
<div id="footer"><button onclick="mainMenu()">Return to Main</button></div>
</body>
</html>
