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
	case "update":
		
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
		// calculate the scores
		//players who submitted the correct answer
		$sql = "UPDATE players p SET p.Score=p.Score+3";
		$sql .= " WHERE p.PlayerID IN (SELECT a1.PlayerID FROM answers a1, answers a2";
		$sql .= " WHERE a2.PlayerID =".$_SESSION['Player_ID']." AND a1.BindAnswerID = a2.AnswerID AND a1.GameID=".$_SESSION['Game_ID'].")";
		if(!mysqli_query($con, $sql)){
			echo('Unable to score correct answers');
		}
		//players who voted for the correct answer
		
		
		//players who received votes
		
		
		//pull all answers and their votes
		$sql = "SELECT a.AnswerID, a.AnswerText, p.PlayerName, p.PlayerID, p.Score, vp.PlayerName, vp.PlayerID";
		$sql .= " FROM answers a, players p, players vp LEFT JOIN votes v ON v.AnswerID = a.AnswerID";
		$sql .= " WHERE a.PlayerID = p.PlayerID AND a.GameID=".$_SESSION['Game_ID'];
		$sql .= " AND vp.PlayerID = v.PlayerID";
		$sql .= " ORDER BY ISNULL(vp.PlayerName), a.AnswerID"; 
		if(!$result = mysqli_query($con, $sql)){
			echo('Cant find list of answers/votes.');
		}
		$previd = 0;
		echo '<div><div>';  //these divs won't contain anything, but each row <div> must close out last row
		while($row = mysqli_fetch_row($result)){
			// 0-ansid 1-anstxt 2-name 3-playerid 4-score 5-votername 6-voterid
			if($previd == $row[0]){
				// we already created the table row, just add the new voter
				echo '<div class="row"><span class="label label-'.$rstyle.'">'.$row[5].'</span></div>';
			} else {
				$rstyle = "active";
				if($row[3] == $_SESSION['Player_ID']){
					//this is the correct answer
					$rstyle = "success";
				}
				if(!is_null($row[5])){
					//this answer got votes	
					$rstyle = "info";
				}
				echo '</div></div><div class="row">';
				echo '	<div class="col-xs-3 text-center alert alert-'.$rstyle.'">'.$row[2].'<span class="badge">'.$row[4].'</span></div>';
				echo '	<div class="col-xs-6 alert alert-'.$rstyle.'">'.$row[1].'</div>';
				echo '	<div class="col-xs-3"><div class="row"><span class="label label-'.$rstyle.'">'.$row[5].'</span></div>';
				$previd = $row[0];
			}
			
		}
	} else {
		//show a waiting message
		?>
		<div class="container alert alert-danger"> Please wait for players to complete voting</div>
		<div id="footer" class="container text-center"><button type="button" class="btn btn-danger" onclick="endVoting()">Close voting</button></div>
		<?php
	}
} else {
	//check the game state
	
}
	



mysqli_close($con);
?>

<div id="footer" class="container text-center"><button type="button" class="btn btn-warning" onclick="returnLobby()">Return to the Lobby</button></div>
</body>
</html>
