<?php
function truncate ($string,$size) {
	if (strlen($string) > $size) {
		$string = substr($string,0,$size)."...";
	}
	return $string;
}

function number_abbreviate ($num) {
	if (abs($num) >= 1000000000000) {
		$result = round($num/1000000000000,1)."T";
	}
	else if (abs($num) >= 1000000000) {
		$result = round($num/1000000000,1)."B";
	}
	else if (abs($num) >= 1000000) {
		$result = round($num/1000000,1)."M";
	}
	else {
		$result = $num;
	}
	return $result;
}

function redgreenblack ($amount,$level=0) {
	if ($amount < 0) {
		return "red";
	}
	else if ($amount > 0) {
		return "green";
	}
	else {
		return "black";
	}
}
?>