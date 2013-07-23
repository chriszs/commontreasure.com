<?php
$path = "";
include("includes/default.php");
include("models/filter.php");
include("models/yeartotals.php");
include("models/lines.php");
include("models/spendinglines.php");
include("models/description.php");
include("widgets/yeargraph.php");
include("widgets/linetable.php");
include("widgets/spendinglinetable.php");
include("widgets/piegraph.php");
include("widgets/breadcrumb.php");
include("widgets/percentchange.php");
include("widgets/descriptions.php");

$filter = new Filter();
$filter->setQuery($_GET);

$filter->setStartYear($filter->getYear()-9);
$filter->setEndYear($filter->getYear()+5);

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

include("includes/header.php");

if (count($lines_result) == 0) {
	echo "<br><strong>No results found.</strong>";
	include("includes/footer.php");
	exit();
}

$yeartotals = new YearTotals($filter);
?>

<div style="border-left:1px solid rgb(235,235,235);border-right:1px solid rgb(235,235,235)" class="lines">

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
<td style="padding-right:10px" align="right">
<!--<a href="#" class="dooda">whee!</a>-->
</td>
</tr>
</tbody>
</table>

<table cellpadding="0" cellspacing="0" border="0" width="100%" class="flip">
<tbody>
<tr>
<tr>
<td valign="top" align="center">

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-left:15px;margin-right:15px;height:190px">
<tbody>
<tr>
<td width="5">
</td>
<td valign="middle" align="center" width="190">
<?php
if (!($filter->getAccount() !== false && $filter->getAgency() !== false && $filter->getBureau() !== false)) {
	echo (new PieGraph($lines));
}
?>
</td>
<td width="20">
</td>
<td valign="middle" align="center">
<?php
$yeargraph = new YearGraph($yeartotals);
$yeargraph->setHeight(130-3);
echo $yeargraph;
?>
</td>
<td>
</td>
<td valign="middle" align="center">

<?php
echo (new PercentChange($yeartotals));
?>

</td>
</tr>
</tbody>
</table>

<?php
if ($filter->getAccount() !== false && $filter->getAgency() !== false && $filter->getBureau() !== false) {
	?>
    <table cellpadding="0" cellspacing="0" border="0" class="ribbon">
    <tbody>
    <tr>
    <td valign="middle" style="padding:8px;padding-left:7px" width="5%">
    <h3 style="font-size:13px;color:#666;margin:0px">Transactions</h3>
    </td>
    <td valign="middle" align="center" class="loading">
    <img src="images/loading.gif" width="16" height="16" style="margin-top:2px" />
    </td>
    <td class="loading">
    Out of date. Refreshing.
    </td>
    <td align="right" style="padding-right:7px;color:#666;font-size:10px" class="count">
    <?php
    echo "top ".count($spendinglines->fetchOnce())." of ".$spendinglines->getNumFound()." disclosed";
    ?>
    </td>
    </tr>
    </tbody>
    </table>
    
    <?php
	echo (new SpendingLineTable($spendinglines));
	?>
    
    <div class="note source">
    Source: <a href="http://www.usaspending.gov/">USA Spending</a>
    </div>
    
    <?php
}
else {
	?>
    
    <table cellpadding="0" cellspacing="0" border="0" class="ribbon">
    <tbody>
    <tr>
    <td style="padding:8px;padding-left:7px;vertical-align:middle;height:16px">
    <!--<h3 style="font-size:13px;color:#666;margin:0px">Divisions</h3>-->
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
    
    <?php
	echo (new LineTable($lines));
	?>
	
	<div class="note source">
	Source: <a href="http://www.whitehouse.gov/omb/budget/Supplemental/">President's Budget for 2011</a>
	</div>

	<?php
}
?>

</td>
<td class="sidebar"<?php
//if ($filter->getAgency() === false) {
	echo " style=\"background-color:rgb(250,250,250)\"";
//}
?>>
<?php
/*
if ($filter->getAgency() !== false) {
?>

<div class="ribbon top">Description</div>

<?php
echo (new Descriptions(new Description($filter)));
?>

<div class="ribbon">Tag</div>

<div class="widget tags">
<table cellpadding="0" cellspacing="0" cellpadding="0" class="tag">
<tbody>
<tr>
<td class="front">
</td>
<td class="middle">
health
</td>
<td class="back">
</td>
</tr>
</tbody>
</table>

<table cellpadding="0" cellspacing="0" cellpadding="0" class="tag">
<tbody>
<tr>
<td class="front">
</td>
<td class="middle">
human services
</td>
<td class="back">
</td>
</tr>
</tbody>
</table>
<input type="text" size="26" value="What type of thing is this?" />
</div>

<!--
<div class="ribbon">Rate</div>

<div class="widget rate">
<div class="note">This is a...</div>
<table cellpadding="0" cellspacing="0" cellpadding="0" class="scale">
<tbody>
<tr>
<td style="height:13px;background-color:rgb(5,86,207);width:61%">
</td>
<td style="height:13px;background-color:black;width:39%">
</td>
</tr>
</tbody>
</table>
<table cellpadding="0" cellspacing="0" cellpadding="0" class="scale labels">
<tbody>
<tr>
<td style="font-size:11px" class="note">
<a href="#">good</a>
</td>
<td style="font-size:11px;text-align:right" class="note">
<a href="#">poor</a>
</td>
</tr>
</tbody>
</table>

<table cellpadding="0" cellspacing="0" cellpadding="0" class="scale">
<tbody>
<tr>
<td style="height:13px;background-color:#FC3;width:30%">
</td>
<td style="height:13px;background-color:#999;width:70%">
</td>
</tr>
</tbody>
</table>
<table cellpadding="0" cellspacing="0" cellpadding="0" class="scale labels">
<tbody>
<tr>
<td class="note">
<a href="#">surprising</a>
</td>
<td style="text-align:right" class="note">
<a href="#">boring</a>
</td>
</tr>
</tbody>
</table>
<div style="text-align:right;width:180px" class="note">...use of money.</div>
</div>

<div class="ribbon">Comment</div>

<div style="padding-left:15px;padding-right:10px">
Blah blah blah. Blah blah. Blah.<br /> <div style="float:right;margin-right:30px">- Ted</div><br /><br />
Lorem ipsum delore sit amet.<br /> <div style="float:right;margin-right:30px">- Bob</div><br /><br />
<textarea cols="28" rows="2">What's your take?</textarea>
</div>
-->
<br />
<?php
}*/
?>

</td>
</tr>
</tbody>
</table>

<?php
include("includes/footer.php");
?>