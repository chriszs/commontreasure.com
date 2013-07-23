<?php
mysql_connect($config['mysql']['host'],$config['mysql']['user'],$config['mysql']['pass']);
mysql_select_db($config['mysql']['db']);
?>