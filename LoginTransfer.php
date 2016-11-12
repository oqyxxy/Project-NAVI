<?php 
session_start();
if ($_GET['username'] != "") {
	$source = $_GET['sourcedata'];
	$_SESSION['userid'] = $_GET['username'];
	$_SESSION['username'] = $_GET['realname'];
	if($source != ""){
		if($source == "homepage"){
			header("Location:home.php");
		}elseif($source == "index"){
			header("Location:index.php");
		}else{
			header("Location:logout.php");
		}
	}else{
		header("Location:logout.php");
	}
}
?>