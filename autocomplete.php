<?php
$path = "";
include("includes/default.php");

if (isset($_GET['q'])) {
	$results = mysql_query("(SELECT `bureau-name` AS `name`, `agency-code`, `bureau-code` FROM `outlays` WHERE `bureau-name` LIKE '%".mysql_real_escape_string($_GET['q'])."%' AND `2010` != 0 GROUP BY `bureau-code`, `agency-code`) UNION (SELECT `agency-name` AS `name`, `agency-code`, '-1' AS `bureau-code` FROM `outlays` WHERE `agency-name` LIKE '%".mysql_real_escape_string($_GET['q'])."%' AND `2010` != 0 GROUP BY `agency-code`);");

	while ($result = mysql_fetch_assoc($results)) {
		echo $result['name']."|lines.php?agency=".$result['agency-code']."&bureau=".$result['bureau-code']."\r\n";
	}
}
?>