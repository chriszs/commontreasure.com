<?php
$path = "../";
include($path."includes/default.php");

include($path."includes/header.php");
?>

<div class="doc">

<?php
if (isset($_GET['doc']) && in_array($_GET['doc'],array("install","api"))) {
	include($_GET['doc'].".html");
}
?>

</div>

<?php
include($path."includes/footer.php");
?>