<?php
$path = "../";
include($path."includes/default.php");
include($path."models/filter.php");
include($path."models/yeartotals.php");

$filter = new Filter();
$filter->setQuery($_GET);

if (isset($_GET['json-callback'])) {
	if (preg_match("/[0-9a-z_-]+/i",$_GET['json-callback'])) {
		echo $_GET['json-callback']."(";
	}
	else {
		header("HTTP/1.0 400 Bad Request");
		echo "Invalid json_callback. Should consist of just letters, numbers, underscores, and dashes.";
	}
}

$yeartotals = new YearTotals($filter);
$result = $yeartotals->fetch();
foreach ($result as $key=>$value) {
	$result[$key] *= $filter->getMultiplier();
}
echo json_encode($result);

if (isset($_GET['json-callback']) && preg_match("/[0-9a-z_-]+/i",$_GET['json-callback'])) {
		echo ");";
}

?>