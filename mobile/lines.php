<?php
$path = "../";
include($path."includes/default.php");

$filter = new Filter();
$filter->setQuery($_GET);

$filter->setStartYear($filter->getYear()-9);
$filter->setEndYear($filter->getYear()+3);

$lines = new Lines($filter);
$spendinglines = new SpendingLines($filter);

$lines_result = $lines->fetchOnce();

if ($filter->getAgency() !== false && $filter->getBureau() === false && count($lines_result) == 1 && $lines_result[0]['agency-name'] == $lines_result[0]['bureau-name']) {
	$url_filter = new Filter($filter);
	$url_filter->setBureau($lines_result[0]['code']);
	header("Location: ?level=3".($url_filter->getQuery("&")));
	exit();
}
if ($filter->getAgency() !== false && $filter->getBureau() !== false && $filter->getAccount() == false && count($lines_result) == 1 && $lines_result[0]['account-name'] == $lines_result[0]['bureau-name']) {
	$url_filter = new Filter($filter);
	$url_filter->setAccount($lines_result[0]['code']);
	header("Location: ?level=4".($url_filter->getQuery("&")));
	exit();
}

include("header.php");

if (count($lines_result) == 0) {
	echo "<br><strong>No results found.</strong>";
	include("includes/footer.php");
	exit();
}

$yeartotals = new YearTotals($filter);
?>

<div class="lines">

<table cellpadding="0" cellspacing="0" border="0" class="ribbon">
<tbody>
<tr>
<td valign="top"  style="padding-left:10px">

<?php
$breadcrumb = new Breadcrumb($lines);
if (isset($_GET['level'])) {
	$breadcrumb->setLevel($_GET['level']);
}
echo $breadcrumb->render();
?>

</td>
</tr>
</tbody>
</table>

<div style="padding:15px;margin-left:auto;margin-right:auto;width:290px">

<?php
$yeargraph = new YearGraph($yeartotals);
$yeargraph->setHeight(130);
echo $yeargraph;
?>

</div>

<?php
if (!($filter->getAccount() !== false && $filter->getAgency() !== false && $filter->getBureau() !== false)) {
	echo (new LineGraph($lines));
}
?>
    
    <table cellpadding="0" cellspacing="0" border="0" class="ribbon">
    <tbody>
    <tr>
    <td style="padding:8px;padding-left:7px;vertical-align:middle;height:16px">
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
    <?php
	$linetable = new LineTable($lines);
	$linetable->setCompact(true);
	echo $linetable;
	
	
	if ($filter->getTable() != "mspd") {
		?>
		
		<div class="note source">
		Source: <a href="http://www.whitehouse.gov/omb/budget/Supplemental/">President's Budget for 2011</a>
		</div>
	
		<?php
	}
	else {
		?>
		
		<div class="note source">
		Source: <a href="http://www.treasurydirect.gov/govt/reports/pd/mspd/mspd.htm">Monthly Statement of the Public Debt</a>
		</div>
	
		<?php
	}
?>

</td>
</tr>
</tbody>
</table>

<?php
include($path."includes/footer.php");
?>