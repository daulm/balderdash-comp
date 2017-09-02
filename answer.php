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




mysqli_close($con);
?>
  
<div class="container">
  <div class="well well-sm">BABBLEGANOOF</div>
    <div class="input-group">
      <textarea class="form-control custom-control" rows="3" placeholder="Enter your answer" name="answertxt" id="answertxt"></textarea>
     
        <span class="input-group-addon btn btn-primary" type="button" onclick="submitAnswer(1)">Submit</span>
   
    </div>
</div>	
	
<div id="footer"><button type="button" class="btn" onclick="mainMenu()">Leave the Game</button></div>
</body>
</html>
