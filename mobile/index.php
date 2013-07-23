<?php
$path = "../";
include($path."includes/default.php");

$filter = new Filter();
$filter->setTruncate(7);
$filter->setQuery($_GET);
$filter->setStartYear($filter->getYear()-6);
$filter->setEndYear($filter->getYear()+3);

$lines = new Lines($filter);

$yeartotals = new YearTotals($filter);

$filter2 = new Filter($filter);
$filter2->setTable("receipts");

$lines2 = new Lines($filter2);

$yeartotals2 = new YearTotals($filter2);

$filter3 = new Filter($filter);
$filter3->setTable("mspd");

$lines3 = new Lines($filter3);

$yeartotals3 = new YearTotals($filter3);

include("header.php");
?>
<div class="index">
<?php
if (count($lines->fetchOnce()) == 0) {
	echo "<br><strong>No results found.</strong>";
	include("includes/footer.php");
	exit();
}
?>
<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td valign="top"  style="padding-left:10px">
<h2><?php echo htmlentities($filter->getYear()); ?></h2>
</td>
</tr>
</tbody>
</table>

<div style="padding:15px;margin-left:auto;margin-right:auto;width:290px">

<?php
$yeargraph = new YearGraph($yeartotals);
$yeargraph->setYearTotals2($yeartotals2);
$yeargraph->setHeight(130);
echo $yeargraph;
?>
</div>

<div style="margin-bottom:15px">

<?php
echo (new LineGraph($lines));
?>

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td valign="middle" style="padding:8px;padding-left:7px">
<h2 style="margin:0px"><a href="lines.php">Spending</a></h2>
</td>
<td style="padding-right:7px;text-align:right">
<span style="color:#666;font-size:9px"><?php if ($filter->getYear() >= date("Y") && $filter->getTable() == "mspd") { ?>PARTIAL:<?php } else if ($filter->getYear() >= date("Y")) { ?>PROJECTED:<?php } else { ?>TOTAL:<?php } ?></span>
<?php
echo "<span style=\"font-size:15px;font-weight:bold\" class=\"".redgreenblack($lines->getTotal())." transparent\">$".number_abbreviate(abs($lines->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

<div style="border-bottom:1px solid rgb(230,230,230);">
<?php
$linetable = new LineTable($lines);
$linetable->setCompact(true);
echo $linetable;
?>
</div>

</div>

<div style="margin-top:15px;margin-bottom:15px">

<?php
echo (new LineGraph($lines2));
?>

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td style="padding:8px;padding-left:7px;vertical-align:middle">
<h2 style="margin:0px"><a href="lines.php?type=receipts">Income</a></h2>
</td>
<td style="padding-right:7px;text-align:right">
<span style="color:#666;font-size:9px"><?php if ($filter2->getYear() >= date("Y") && $filter2->getTable() == "mspd") { ?>PARTIAL:<?php } else if ($filter2->getYear() >= date("Y") && $filter->getTable() == "mspd") { ?>PARTIAL:<?php } else if ($filter->getYear() >= date("Y")) { ?>PROJECTED:<?php } else { ?>TOTAL:<?php } ?></span>
<?php
echo "<span style=\"font-size:15px;font-weight:bold\" class=\"".redgreenblack($lines2->getTotal())." transparent\">$".number_abbreviate(abs($lines2->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

<?php
$linetable = new LineTable($lines2);
$linetable->setCompact(true);
echo $linetable;
?>

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td style="padding:8px;padding-left:7px;vertical-align:middle">
<h2 style="margin:0px"><?php 
if ($lines2->getTotal()+$lines->getTotal() > 0) {
	echo "Surplus";
}
else if ($lines2->getTotal()+$lines->getTotal() < 0) {
	echo "Deficit";
}
else {
	echo "Balance";
}
?></h2>
</td>
<td style="padding-right:7px;text-align:right">
<span style="color:#666;font-size:9px"><?php if ($filter2->getYear() >= date("Y") && $filter2->getTable() == "mspd") { ?>PARTIAL:<?php } else if ($filter2->getYear() >= date("Y")) { ?>PROJECTED:<?php } else { ?>TOTAL:<?php } ?></span>
<?php
echo "<span style=\"font-size:15px;font-weight:bold\" class=\"".redgreenblack($lines2->getTotal()+$lines->getTotal())." transparent\">$".number_abbreviate(abs($lines2->getTotal()+$lines->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

</div>

<div style="margin-top:15px;margin-bottom:4px">

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td style="padding:8px;padding-left:7px;vertical-align:middle">
<h2 style="margin:0px"><a href="lines.php?type=mspd">Debt</a></h2>
</td>
<td style="padding-right:7px;text-align:right">
<span style="color:#666;font-size:9px"><?php if ($filter3->getYear() >= date("Y") && $filter3->getTable() == "mspd") { ?>PARTIAL:<?php } else if ($filter3->getYear() >= date("Y")) { ?>PROJECTED:<?php } else { ?>TOTAL:<?php } ?></span>
<?php
echo "<span style=\"font-size:15px;font-weight:bold\" class=\"".redgreenblack($lines3->getTotal())." transparent\">$".number_abbreviate(abs($lines3->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

</div>

<div class="note source">
Sources: <a href="http://www.whitehouse.gov/omb/budget/Supplemental/">President's Budget</a>, <a href="http://www.treasurydirect.gov/govt/reports/pd/mspd/mspd.htm">MSPD</a>
</div>

</div>

<?php
include($path."includes/footer.php");
?>