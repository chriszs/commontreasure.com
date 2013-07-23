<?php
$path = "../";
include($path."includes/default.php");
include($path."models/filter.php");
include($path."models/spendinglines.php");
include($path."widgets/spendinglinetable.php");

$filter = new Filter();
$filter->setQuery($_GET);

$spendinglines = new SpendingLines($filter);

$spendinglines->setUseAPI(true);

echo "<div>";
echo "<div class=\"count\">top ".count($spendinglines->fetchOnce())." of ".$spendinglines->getNumFound()." disclosed</div>";
echo (new SpendingLineTable($spendinglines));
echo "</div>";
?>