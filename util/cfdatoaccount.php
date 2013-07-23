<?php
$path = "";
include("includes/default.php");

set_time_limit(0);

$result = mysql_query("SELECT `program-number`, `account-identification` FROM `cfda`;");

while ($row = mysql_fetch_assoc($result)) {
	if (preg_match_all("/([0-9]{1,3})-([0-9]{1,6})-[0-9]{1}-[0-9]{1}-[0-9]{1,6}/",$row['account-identification'],$matches)) {
		foreach ($matches[1] as $i=>$treascode) {
			mysql_query("INSERT INTO `budget`.`cfdatoaccount` (`program-number`, `treasury-agency-code`, `account-code`) VALUES ('".mysql_real_escape_string($row['program-number'])."', '".mysql_real_escape_string($treascode)."', '".mysql_real_escape_string($matches[2][$i])."');");
		}
	}
}
?>