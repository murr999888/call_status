<?php

error_reporting(E_ALL);

require_once "dblib/safemysql.class.php";

$opts_goip = array(
	'host'    => 'localhost',
	'user'    => 'goip',
	'pass'    => 'goip',
	'db'      => 'pbx',
	'charset' => 'utf8'
);

$DB = new SafeMySQL($opts_goip); // with some of the default settings overwritten

$strq = "SELECT queue, time, calleridnum, calleridname, UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(time) as wait  FROM queues ORDER BY queue,time";

$results = $DB->getAll($strq);

echo "<table class='table table-queue'>";
echo "<thead><tr style='table-layout: auto;'><th style='width:10%;'>Номер</th><th style='width:18%;'>Начало</th><th style='width:12%;'>Ожидает</th><th>Абонент</th></tr></thead>";
echo "<tbody >";

foreach ($results as $result) {
	echo "<tr class='queue'>";
	echo "<td style='text-align: center;' class='large'>" . $result['queue'] . "</td>";
	echo "<td class='large' style='text-align: center;'>" . $result['time'] . "</td>";
	echo "<td class='large' style='text-align: center;'>" . gmdate("H:i:s", $result['wait']) . "</td>";

	$name_from = "";

	if ($result['calleridname'] != "" && $result['calleridname'] != $result['calleridnum']) {
		$name_from = "<br />" . $result['calleridname'];
	}

	echo "<td class='large' style='text-align: center;'>" . $result['calleridnum'] . $name_from . "</td>";
	echo "</tr>";
}

echo "</tbody>";
echo "</table>";
?>