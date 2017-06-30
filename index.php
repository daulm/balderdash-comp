<!DOCTYPE html>
<?php
session_start();
?>

<html>
<head>
<title>Balderdash</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<script src="js/jquery.min.js"></script>
<script type="text/javascript">

var refresh_lobby = true;
var refresh_review = false;
var refresh_results = false;
var mytimer;
// The rate in milliseconds at which the lobby refreshes
var refresh_speed = 3000;

$(document).ready(function(){

$(#settings).focusin(function (){
	//stop refreshing the lobby if a user clicks on the settings
	refresh_lobby=false;
	
});	
	
$(#settings).focusout(function (){
	//resume refreshing the lobby if the host clicks away from the settings
	if ($("#msglist").data("gamestate") == 0){
		refresh_lobby=true;
	}
});	
	
});
	
function mainMenu(){
	// this function loads the main menu of the game
	refresh_lobby = false;
	$.get("main_menu.php", function(result){
		$("#bd_content").html(result);
	});
}

function showLobby(){
	/* this function waits a few seconds and then refreshes the data in the lobby and then calls itself again
	it looks for a data attribute in the html to see if it is time to launch the game. */
	
	
	var myreq = $.get("lobby.php?mode=update", function(result){
		if (refresh_lobby){
			$("#bd_content").html(result);
		}
	});
	
	myreq.done(function(){
		
		if ($("#msglist").data("gamestate") > 0){
			launchGame($("#msglist").data("gamestate"));
		} else {
			setTimeout(showLobby, refresh_speed);
		}
	});
	
	
}

function hostGame(){
	// this function initiates a new game and returns the lobby with a 4 letter password
	var name = $("#playername").val();
	if (name.length > 20 || name.length < 1){
		alert("Name must be 1-20 characters");
		return 0;
	}
	var posting = $.post("lobby.php?mode=create", {pname: name}, function(result){
		$("#bd_content").html(result);
	});
	refresh_lobby = true;
	setTimeout(showLobby, refresh_speed);
}

function enterLobby(){
	// this function joins a lobby that already exists
	var name = $("#playername").val();
	var code = $("#code").val();
	if (name.length > 20 || name.length < 1){
		alert("Name must be 1-20 characters");
		return 0;
	}
	if (code.length != 4){
		alert("Code must be 4 characters");
		return 0;
	}
	var posting = $.post("lobby.php?mode=join", {pname: name, code: code}, function(result){
		$("#bd_content").html(result);
	});
	refresh_lobby = true;
	setTimeout(showLobby, refresh_speed);
}

function lobbySettings(){
	// change the game settings in the lobby
	var $allspy;
	var $timelimit = $('#timelimit').val();
	var $spy_count = $('#spy_count').val();
	if (!$.isNumeric($timelimit) || !$.isNumeric($spy_count)){
		alert("Please enter integers");
		return 0;
	}
	if($("#allspy").is(':checked')){
		$allspy=1;
	} else {
		$allspy=0;
	}
	$.post("lobby.php?mode=settings", {timelimit: $timelimit, spy_count: $spy_count, allspy: $allspy}, function(result){
		$('#bd_content').html(result);
	});
	refresh_lobby = true;
	setTimeout(showLobby, refresh_speed);
}

function stopRefresh(){
	//stop refreshing if the host is trying to change the settings
	refresh_lobby=false;	
}


function returnLobby(){
	$.get("lobby.php?mode=return", function(result){
		$("#bd_content").html(result);
	});
	refresh_lobby = true;
	setTimeout(showLobby, refresh_speed);
}
	
function timeUp(){
	// this function is called if the timer reaches 0, it checks where it is and has the user send in any completed data
	switch($("#msglist").data("gamestate")){
		case "answer":
			//if it is an answer submission submit whatever they have completed
			submitAnswer(false);
		case "vote":
			//if it is voting submit nothing and move on to the results
			refresh_results = true;
			setTimeout(showResults, refresh_speed);
		default:
			//if it is anything else, do nothin
			
	}					      
}

function startTimer(duration, display) {
	var timer = duration, minutes, seconds;
	if(mytimer){
		clearInterval(mytimer);
	}
	mytimer = setInterval(function(){
		minutes = parseInt(timer / 60, 10);
		seconds = parseInt(timer % 60, 10);
		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds : seconds;
		display.text(minutes + ":" + seconds);
		if(--timer < 0){
			clearInterval(mytimer);
			timeUp();
		}
	}, 1000);
}

function launchGame(mode){
	// kick off the game, either because of the game state, or because the host chose to launch the game
	refresh_lobby = false;
	var myreq = $.get("answer.php", function(result){
		$("#bd_content").html(result);
	});

	myreq.done(function(){
		var timeleft = $("#countdown").data("timeleft");
		startTimer(timeleft, $("#countdown"));
	});
}
	
function launchVote(mode){
	// kick off the vote menu
	refresh_review = false;
	var myreq = $.get("vote.php", function(result){
		$("#bd_content").html(result);
	});

	myreq.done(function(){
		var timeleft = $("#countdown").data("timeleft");
		startTimer(timeleft, $("#countdown"));
	});	
}
	
function showReview(){
	/* this function waits a few seconds and then refreshes the data in the review screen and then calls itself again
	it looks for a data attribute in the html to see if it is time to launch the game. */
	
	if (refresh_review){
		var myreq = $.get("review.php?mode=update", function(result){
			$("#bd_content").html(result);
		});
		
		myreq.done(function(){
			
			if ($("#msglist").data("gamestate") > 0){
				launchVote($("#msglist").data("gamestate"));
			} else {
				setTimeout(showReview, refresh_speed);
			}
		});
	}	
}
	
function submitAnswer(check){
	// this function submits the answer
	var ans = $("#answertxt").val();
	if(check){
		if (ans.length > 2000 || ans.length < 1){
			alert("Answer must be 1-2000 characters");
			return 0;
		}
	}
	var posting = $.post("review.php?mode=submit", {ans: ans}, function(result){
		$("#bd_content").html(result);
	});
	refresh_review = true;
	setTimeout(showReview, refresh_speed);
}
	
function showResults(){
	/* this function waits a few seconds and then refreshes the data in the review screen and then calls itself again
	it looks for a data attribute in the html to see if it is time to launch the game. */
	
	if (refresh_results){
		var myreq = $.get("results.php?mode=update", function(result){
			$("#bd_content").html(result);
		});
		
		myreq.done(function(){
			
			if ($("#msglist").data("gamestate") > 0){
				refresh_results = false;
			} else {
				setTimeout(showResults, refresh_speed);
			}
		});
	}	
}
	
function submitVote(){
	// this function submits the vote
	var vote = $("#voteid").val();

	var posting = $.post("results.php?mode=submit", {voteid: vote}, function(result){
		$("#bd_content").html(result);
	});
	refresh_results = true;
	setTimeout(showResults, refresh_speed);
}	

</script>
</head>

<body onload="mainMenu()">

<div id="bd_content">Loading Balderdash Companion...</div>

</body>
