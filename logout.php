<?php
session_start();
unset($_SESSION['username']); 	//remove userid from session
session_destroy();		//destroy the session
header("Location:index.php");	//redirect user to index/login page
?>