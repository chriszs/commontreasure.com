<?php
$path = "";
include("includes/default.php");
include("models/filter.php");
include("models/yeartotals.php");
include("models/lines.php");
include("widgets/yeargraph.php");
include("widgets/linetable.php");
include("widgets/piegraph.php");

$filter = new Filter();
$filter->setTruncate(7);
$filter->setStartYear(1979);
$filter->setQuery($_GET);

$lines = new Lines($filter);

$yeartotals = new YearTotals($filter);

$filter2 = new Filter($filter);
$filter2->setTable("receipts");

$lines2 = new Lines($filter2);

$yeartotals2 = new YearTotals($filter2);

include("includes/header.php");
?>
<div style="border-left:1px solid rgb(235,235,235);border-right:1px solid rgb(235,235,235)" class="index">
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

<div style="padding-left:20px;padding-right:20px;padding-top:15px;padding-bottom:15px">

<?php
$yeargraph = new YearGraph($yeartotals);
$yeargraph->setYearTotals2($yeartotals2);
$yeargraph->setHeight(170);
echo $yeargraph;
?>
</div>

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td valign="middle" style="padding:8px;padding-left:7px">
<h2 style="margin:0px"><a href="lines.php">Spending</a></h2>
</td>
<td style="padding-right:7px;text-align:right">
<span style="color:#666;font-size:9px"><?php if ($filter->getYear() >= date("Y")) { ?>PROJECTED <?php } ?>TOTAL:</span>
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
<td valign="top" width="200" align="center">

<?php
$piegraph = new PieGraph($lines);
echo $piegraph->render();
?>

</td>
<td width="25">&nbsp;</td>
<td valign="top">

<?php
$linetable = new LineTable($lines);
echo $linetable->render();
?>

</td>
</tr>
</tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td style="padding:8px;padding-left:7px;vertical-align:middle">
<h2 style="margin:0px"><a href="lines.php?type=receipts">Income</a></h2>
</td>
<td style="padding-right:7px;text-align:right">
<span style="color:#666;font-size:9px"><?php if ($filter->getYear() >= date("Y")) { ?>PROJECTED <?php } ?>TOTAL:</span>
<?php
echo "<span style=\"color:".redgreenblack($lines2->getTotal()).";font-size:18px\">$".number_format(abs($lines2->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tbody>
<tr>
<td valign="top" width="200" align="center">

<?php
echo (new PieGraph($lines2));
?>

</td>
<td width="25">&nbsp;</td>
<td valign="top">

<?php
echo (new LineTable($lines2));
?>

</td>
</tr>
</tbody>
</table>

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
<span style="color:#666;font-size:9px"><?php if ($filter->getYear() >= date("Y")) { ?>PROJECTED <?php } ?>TOTAL:</span>
<?php
echo "<span style=\"color:".redgreenblack($lines2->getTotal()+$lines->getTotal()).";font-size:18px\">$".number_format(abs($lines2->getTotal()+$lines->getTotal()))."</span>";
?>
</td>
</tr>
</tbody>
</table>

<div class="note source">
Source: <a href="http://www.whitehouse.gov/omb/budget/Supplemental/">President's Budget for 2011</a>
</div>

</div>

<?php
include("includes/footer.php");
?>