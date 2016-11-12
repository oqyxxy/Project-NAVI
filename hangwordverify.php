<?php

SESSION_START();

$wordinput = $_GET['alphakey'];

$skyarray = $_SESSION['headwordarr'];
$counter = 1;
$trigger = false;
$returnarr = array();
$subarray = array();

foreach ($skyarray as $value){
	if(strtoupper($value) == strtoupper($wordinput)){
		$trigger = true;
		$subarray[] = $counter;
	}
	$counter = $counter + 1;
}
$returnarr[0] = $trigger;
$returnarr[1] = $subarray;

echo json_encode($returnarr);

?>