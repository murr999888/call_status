<?php

error_reporting(E_ALL);

require_once "dblib/safemysql.class.php";

// параметры соединения с базой VOIP шлюза GOIP
$opts_goip = array(
	'host'    => 'localhost',
	'user'    => 'goip',
	'pass'    => 'goip',
	'db'      => 'pbx',
	'charset' => 'utf8'
);

$DB = new SafeMySQL($opts_goip); // with some of the default settings overwritten

$strq = " SELECT "
	."	p.num,"
	."	p.did, "
	." 	p.description, "
	."	p.registered,  	"
	."	p.is_registry, 	"
	."	p.reachable,  	"
	."	p.last_state, "	
	."	c.channelstate, "
	."	c.channelstatedesc, "
	." 	g.imei, "
	." 	g.signal, "
	." 	g.bal, "
	." 	g.bal_time, "
	." 	g.cellinfo, "
	." CASE "
	."	WHEN NOT ISNULL(d.calleridnum) AND (d.calleridnum <> p.num) AND (d.calleridname <> '<unknown>') THEN "
	."		d.calleridnum "
	."	WHEN NOT ISNULL(d_c_from.num) AND (d_c_from.num <> p.num) THEN "
	."		d_c_from.calleridnum "
	." 	WHEN c.num = p.num AND c.calleridnum <> '' THEN "
	."		c.calleridnum "
	." 	WHEN c.num = p.num AND c.exten <> '' THEN "
	."		c.exten "
	." ELSE "
	."	d_c_from.num "
	." END AS from_num, "


	." CASE "
	."	WHEN NOT ISNULL(d.calleridnum) AND (d.calleridnum <> p.num) AND (d.calleridname <> '<unknown>') THEN "
	."		1 "
	."	WHEN NOT ISNULL(d_c_from.num) AND (d_c_from.num <> p.num) THEN "
	."		2 "
	." 	WHEN c.num = p.num AND c.calleridnum <> '' THEN "
	."		3 "
	." 	WHEN c.num = p.num AND c.exten <> '' THEN "
	."		4 "
	." ELSE "
	."	5 "
	." END AS from_num_pr, "


	." CASE "
	."	WHEN NOT ISNULL(d.time_begin) THEN "
	."		UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(d.time_begin) "
	."	WHEN NOT ISNULL(b.time_begin) THEN "
	."		UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(b.time_begin) "
	."	WHEN NOT ISNULL(c.time_begin) THEN "
	."		UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(c.time_begin) "
	." 	ELSE NULL "
	." END as dial_time, "

	// для music on hold

	." CASE "
	." 	WHEN (c.num = p.num) AND (c.event <> '') THEN " 
	."		c.event "
	." ELSE '' "
	." END AS channel_event, "

	." CASE "
	."	WHEN NOT ISNULL(q.uniqueid) AND (c.uniqueid = q.uniqueid) THEN "
	." 		CONCAT(q.queue,' (очередь)') "
	."	WHEN NOT ISNULL(b.callerid2) AND (b.callerid2 <> p.num) THEN "
	."		b.callerid2 "
	."	WHEN NOT ISNULL(d_c_to.num) AND (d_c_to.num <> p.num) THEN "
	."		d_c_to.calleridnum "
	." 	WHEN d_c_to.num = p.num THEN "
	."		d_c_to.calleridnum "
	." ELSE  "
	."	c.connectedlinenum "
	." END AS to_num "

	." FROM peers AS p  "

	." LEFT JOIN channels AS c "
	." ON p.num = c.num  AND c.channel_type = 'SIP'"

	." LEFT JOIN bridges AS b "
	." ON c.uniqueid = b.uniqueid1 OR c.uniqueid = b.uniqueid2 "

	." LEFT JOIN dials AS d "
	." ON  c.uniqueid = d.uniqueid OR c.uniqueid = d.destuniqueid "

	." LEFT JOIN channels AS d_c_from "
	." ON d.uniqueid = d_c_from.uniqueid "

	." LEFT JOIN channels AS d_c_to "
	." ON d.destuniqueid = d_c_to.uniqueid "

	." LEFT JOIN queues AS q "
	." ON c.uniqueid = q.uniqueid "

	." LEFT JOIN goip.goip AS g "
	." ON p.goip_id = g.NAME "
	." COLLATE utf8_general_ci "
	." WHERE p.is_trunk = 1 "
	." ORDER BY p.num ";
//
//error_log($strq);

$results = $DB->getAll($strq);

if (!$results) {
    //exit;
}

$signal_icon = "<i class='fa fa-signal'></i>";
$paid_icon = "<i class='fa fa-hryvnia'></i>";

echo "<table class='table table-ext'>";
echo "<thead><tr><th style='width:15%;'>Линия</th><th style='width:15%;'>Номер</th><th style='width:12%;'>Название</th><th title='Уровень сигнала GSM'>" . $signal_icon . "</th><th title='Баланс'>" . $paid_icon . "</th><th style='width:55%;'>Состояние</th></tr></thead>";
echo "<tbody>";

foreach ($results as $result) {
	/*
	0 Down
	1 Rsrvd
	2 OffHook
	3 Dialing
	4 Ring
	5 Ringing
	6 Up
	7 Busy
	8 Dialing Offhook
	9 Pre-ring
	Unknown
	*/		

		$time = "";

		if ($result['dial_time']) {
			$time = " (" . gmdate("H:i:s", $result['dial_time']) . ") ";
		}

		$tr_class = 'idle_bk';
		$state = '';

//$result['reachable']=0;

		if ($result['registered'] == 0 && $result['is_registry'] == 1) {
			$tr_class = 'unregistered_bk';
		} else if ($result['reachable'] == 0) {
			$tr_class = 'unreachable_bk';
		} else {
			if (isset($result['channelstate']) && $result['channelstate'] == 0) {
				$tr_class = 'blue_bk';
				$state = 'DIALING: ';
			} else if ($result['channelstate'] == 6) { // TALKING
				$tr_class = 'green_bk';
				$state = 'CONNECTED: ';
			} else if ($result['channelstate'] == 4) { // ring
				$tr_class = 'blue_bk';
				$state = 'DIALING: ';
			} else if ($result['channelstate'] == 5) { // ringing
				$tr_class = 'red_bk';
				$state = 'RINGING: ';
			} else if ($result['channelstate'] == 3) { //DIALED
				$tr_class = 'blue_bk';
				$state = 'DIALING: ';
			} else if ($result['channelstate'] == 7) { //BUSY
				$tr_class = 'yellow_bk';
				$talking = "BUSY";
			}
		}

		$balans = "";
		if (isset($result['bal'])) {
			$balans = "<span title='balans time: " . $result['bal_time'] . "'>" . number_format($result['bal'],2) . "</span>";
		}
		
		$rssi = "";
		$cellinfo = "";
		$imei = "";

		if ($result['signal']) {
			if ($result['signal'] < 15) {
				$rssi = "<span style = 'color: tomato; font-weight: bold;'>" . $result['signal'] . "</span>";
			} else if ($result['signal'] == 99){
				$rssi = "<span style = 'color: tomato; font-weight: bold;'>?</span>";
			} else {
				$rssi = $result['signal'];
			}
			$imei = "imei: " . $result['imei'];
		}

		if ($result['cellinfo']) {
			$cellinfo = "cell info:" . $result['cellinfo'];
		}
		
               	echo "<tr class='" . $tr_class . "'>";
		echo "<td title='" . $result['last_state'] . "' id='peer_" . $result['num'] . "_num' style='text-align: center;'>" . $result['num'] . "</td>";
		echo "<td id='peer_" . $result['num'] . "_did' style='text-align: center;' title='" . $imei . "'>" . $result['did'] . "</td>";
		echo "<td id='peer_" . $result['num'] . "_description' style='text-align: center;'>" . $result['description'] . "</td>";
		echo "<td id='peer_" . $result['num'] . "_rssi' style='text-align: center;' title='" . $cellinfo . "'>" . $rssi . "</td>";
		echo "<td id='peer_" . $result['num'] . "_bal' style='text-align: right;'>" . $balans . "</td>";

		$status_text = "";

		//if ($result['channelstatedesc']) {
                 	//$status_text = "status: " . $result['channelstatedesc'] . "(" . $result['channelstate'] . ")". ", ";
			//$status_text = "status: " . $result['channelstatedesc'] . ", ";
		//}

		$event = "";
		$event_icon = "";

		if ($result['channel_event'] != '') {
			if ($result['channel_event'] == "Announce" || $result['channel_event'] == "Music On Hold") {
                        	$event_icon = "<i class='fa fa-volume-up'></i>";
			}
			$event = " (" . $event_icon . " " . $result['channel_event'] . ")";
		}

		if ($result['from_num'] == '' && $result['to_num'] == '') {
			echo "<td id='peer_" . $result['num'] . "_state' class='large' style='text-align: center;'>IDLE</td>";
		} else if ($result['from_num'] != '' && $result['to_num'] == '') {
			echo "<td id='peer_" . $result['num'] . "_state' class='large' style='text-align: center;'>" . $state . $status_text . " " . $result['from_num'] . $time . $event . "</td>";
		} else {
	                //echo "<td id='peer_" . $result['num'] . "_state' class='large' style='text-align: center;'>" . $state . $status_text . " " . $result['from_num'] . " " . $result['from_num_pr'] . " &#8594; " . $result['to_num'] . $time . "</td>";
			echo "<td id='peer_" . $result['num'] . "_state' class='large' style='text-align: center;'>" . $state . $status_text . " " . $result['from_num'] . " &#8594; " . $result['to_num'] . $time . "</td>";
		}

		echo "</tr>";
}

echo "</tbody>";
echo "</table>";
?>