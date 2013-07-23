<?php
$path = "";
include("includes/default.php");
include("models/filter.php");
include("models/yeartotals.php");
include("models/lines.php");
include("widgets/yeargraph.php");
include("widgets/linetable.php");
include("widgets/piegraph.php");
include("widgets/breadcrumb.php");

$filter = new Filter();
// $filter->setOffsets("non-offsets");
$filter->setQuery($_GET);

$filter->setStartYear($filter->getYear()-3);
$filter->setEndYear($filter->getYear()+3);

$lines = new Lines($filter);

$lines_result = $lines->fetchOnce();

if ($filter->getAgency() !== false && $filter->getBureau() === false && count($lines_result) == 1 && $lines_result[0]['agency-name'] == $lines_result[0]['bureau-name']) {
	$url_filter = new Filter($filter);
	$url_filter->setBureau($lines_result[0]['code']);
	header("Location: ?level=3".($url_filter->getQuery("&")));
	exit();
}

include("includes/header.php");

if (count($lines_result) == 0) {
	echo "<br><strong>No results found.</strong>";
	include("includes/footer.php");
	exit();
}

$yeartotals = new YearTotals($filter);
?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tbody>
<tr>
<td>
<?php
$breadcrumb = new Breadcrumb($lines);
if (isset($_GET['level'])) {
	$breadcrumb->setLevel($_GET['level']);
}
echo $breadcrumb->render();
?>
</td>
<td align="right" style="color:#666;font-size:9px">TOTAL:
<?php
echo "<span style=\"color:".redgreenblack($lines->getTotal()).";font-size:18px\">$".number_format(abs($lines->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tbody>
<tr>
<td valign="top" width="220" align="center">

<?php
$piegraph = new PieGraph($lines);
echo $piegraph->render();
?>

<br />
<br />

<?php
$yeargraph = new YearGraph($yeartotals);
echo $yeargraph->render();
?>

</td>
<td width="25">&nbsp;</td>
<td valign="top">

<?php
$linetable = new LineTable($lines);
echo $linetable->render();

if ($filter->getAccount() !== false && $filter->getAgency() !== false && $filter->getBureau() !== false) {
	$result = mysql_query("SELECT `spendingtransactions`.* FROM `outlays` JOIN `spendingcalls` ON `outlays`.`account-code` = '".mysql_real_escape_string($filter->getAccount())."' AND `outlays`.`agency-code` = '".mysql_real_escape_string($filter->getAgency())."' AND `outlays`.`bureau-code` = '".mysql_real_escape_string($filter->getBureau())."' AND `outlays`.`treasury-agency-code` = `spendingcalls`.`treasury-agency-code` AND `outlays`.`account-code` = `spendingcalls`.`account-code` JOIN `spendingtransactions` ON `spendingtransactions`.`call-id` = `spendingcalls`.`id`;");

	while ($row = mysql_fetch_assoc($result)) {
		print_r($row);
	}
}

/*
$filter2 = new Filter($lines->getFilter());
$filter2->setOffsets("offsets");
$lines2 = new Lines($filter2);
$linetable2 = new LineTable($lines2);
echo $linetable2->render();*/
?>

<!--
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:15px;margin-bottom:10px">
<tbody>
<tr>
<td>
</td>
<td align="right" style="font-size:11px;color:#666">
<em>Source: <a href="http://www.whitehouse.gov/omb/budget/Supplemental/">President's Budget for 2011</a></em>
</td>
</tr>
</tbody>
</table>
-->

</td>
</tr>
</tbody>
</table>


<?php
include("includes/footer.php");
?>