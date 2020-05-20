<?php

error_reporting(E_ALL);

require_once "dblib/safemysql.class.php";

$opts_goip = array(
    'host' => 'localhost',
    'user' => 'goip',
    'pass' => 'goip',
    'db' => 'pbx',
    'charset' => 'utf8',
);

$DB = new SafeMySQL($opts_goip); // with some of the default settings overwritten

$strq = "SELECT * FROM queue_abandon WHERE DATE(abandon_time) = CURDATE() ORDER BY abandon_time DESC LIMIT 5";

$results = $DB->getAll($strq);

echo "<table class='table table-queue-abandon'>";
echo "<thead><tr style='table-layout: auto;'><th style='width:10%;'>Номер</th><th style='width:18%;'>Время ухода</th><th style='width:12%;'>Ожидал</th><th>Абонент</th></tr></thead>";
echo "<tbody >";

foreach ($results as $result) {
    echo "<tr class=''>";
    echo "<td style='text-align: center;' class='large'>" . $result['queue'] . "</td>";
    echo "<td class='large' style='text-align: center;'>" . $result['abandon_time'] . "</td>";
    echo "<td class='large' style='text-align: center;'>" . gmdate("H:i:s", $result['holdtime']) . "</td>";

    $name_from = "";

    if ($result['calleridname'] != "" && $result['calleridname'] != $result['calleridnum']) {
        $name_from = "<br />" . $result['calleridname'];
    }

    echo "<td class='large' style='text-align: center;'><a href='callto:" . $result['calleridnum'] . "'>" . $result['calleridnum'] . "</a>" . $name_from . "</td>";
    echo "</tr>";
}

echo "</tbody>";
echo "</table>";
