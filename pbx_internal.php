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
	."	p.description,"
	."	p.registered, "	
	."	p.reachable, "	
	."	p.last_state, "	
	." 	p.dnd, "
	."	p.is_operator,"
	."	b.time_begin AS bridge_begin, "
	."	d.time_begin AS dial_begin, "

	// для music on hold

	." 	CASE "
	." 	WHEN (c.num = p.num) AND (c.event <> '') THEN " 
	."		c.event "
	." 	ELSE '' "
	." 	END AS channel_event, "


	."	CASE "
	."	WHEN NOT ISNULL(d.time_begin) THEN "
	."		UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(d.time_begin) "
	."	WHEN NOT ISNULL(b.time_begin) THEN "
	."		UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(b.time_begin) "
	."	WHEN NOT ISNULL(c.time_begin) THEN "
	."		UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(c.time_begin) "
	."	ELSE NULL "
	."	END as dial_time, "

	."	c.channelstate, "
	."	c.channelstatedesc, "
	//."	c.uniqueid, "
	//."	c.calleridnum, "
	//."	c.calleridname, "

	."	CASE "
	."	WHEN (c.uniqueid = d.destuniqueid) THEN "
	."		c.connectedlinenum "
	."	ELSE d.connectedlinenum "
	." 	END as connectedlinenum, "

	//."	c.connectedlinenum, "

	."	CASE "
	."	WHEN (c.uniqueid = d.destuniqueid) THEN "
	."		c.connectedlinename "
	."	ELSE d.connectedlinename "
	." 	END as connectedlinename, "


	//."	c.connectedlinename, "

	."	CASE "
	."	WHEN c.uniqueid = d.uniqueid THEN "
	."		'OUTCOMING' "
	."	WHEN c.uniqueid = d.destuniqueid THEN "
	."		'INCOMING' "
	//."	WHEN (c.num = p.num) AND CHAR_LENGTH(c.exten) = 3 THEN "
	."	WHEN (c.num = p.num && c.exten != '') THEN "
	."		CONCAT('DIAL: ', c.exten) "
	."	WHEN (c.num = p.num && c.connectedlinename != '') THEN "
	."		CONCAT('DIAL: ', c.connectedlinename) "
	."	WHEN ISNULL(d.time_begin)  THEN "
	."		'IDLE' "
	."	END AS is_incoming "

	." FROM peers AS p "

	." LEFT JOIN channels AS c "
	." ON p.num = c.num AND c.channel_type = 'SIP' AND c.channelstate <> 0"

	." LEFT JOIN bridges AS b "
	." ON c.uniqueid = b.uniqueid1 OR "
	."    c.uniqueid = b.uniqueid2 "

	." LEFT JOIN dials AS d "
	." ON  c.uniqueid = d.uniqueid OR "
	." c.uniqueid = d.destuniqueid "

	." WHERE p.is_trunk = 0 ";

//error_log($strq);

$results = $DB->getAll($strq);

if (!$results) {
    //exit;
}

$dnd_icon = "<i class='fa fa-volume-mute'></i>";

echo "<table class='table table-int'>";
echo "<thead><tr><th style='width:10%;'>Номер</th><th style='width:5%;'>DND</th><th style='width:70%;'>Состояние</th></tr></thead>";
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
              		$time = " (" . gmdate("H:i:s", $result['dial_time']) . ")";
		}

		$talking = "";
       		$tr_class = 'idle_bk';

		if ($result['registered'] == 0) {
			$tr_class = 'unregistered_bk';
		} else if ($result['reachable'] == 0) {
			$tr_class = 'unreachable_bk';
		} else {
			if ($result['channelstate'] == 6) { // TALKING
				$talking = "CONNECTED";
				$tr_class = 'green_bk';
			} else if ($result['channelstate'] == 4) { // ring
				$talking = "DIALING";
				$tr_class = 'blue_bk';
			} else if ($result['channelstate'] == 5) { // ringing
				$talking = "RINGING";
				$tr_class = 'red_bk';
			} else if ($result['channelstate'] == 3) { //DIALED
				$tr_class = 'blue_bk';
				$talking = "DIALING";
			} else if ($result['channelstate'] == 7) { //BUSY
				$tr_class = 'yellow_bk';
				$talking = "BUSY";
			}
		}

               	echo "<tr class='" . $tr_class . "'>";

		$operator_style = '';

		if ($result['is_operator']) {
			$operator_style = 'font-weight: bold;';
		}

		echo "<td title='". $result['description'] . " (" . $result['last_state'] . ")". "' id='peer_" . $result['num'] . "_num' style='" . $operator_style . "'>" . $result['num'] . "</td>";

		$dnd = '';

		if ($result['dnd'] == 1) {
	                $dnd = "<span style='font-weight: bold; color: red;'>DND</span>";
		}

		//if ($result['registered'] == 0) {
		//	$incoming = $result['last_state'];
		//} else if ($result['reachable'] == 0) {
		//	$incoming = $result['last_state'];
		//} else {
	                $incoming = $result['is_incoming'];
		//}


		$name_from = "";

		//$arrow_down = "&#8681;"; // толстая стрелка
		//$arrow_up = "&#8679;";

		$arrow_down = "&#8595;"; // тонкая стрелка
		$arrow_up = "&#8593;";

		$pref = $arrow_up . " "; // вверх
		$suff = " " . $arrow_up;

		if ($result['is_incoming'] == "INCOMING" && isset($result['connectedlinename'])) {
			$pref = $arrow_down . " ";
			$suff = " " . $arrow_down;

			if ($result['connectedlinenum'] != $result['connectedlinename']) {
				$name_from = "<br />" . $result['connectedlinename'] . "";
			}
		}

		$status_text = "";

		if ($result['channelstatedesc']) {
                 	//$status_text = "status: " . $result['channelstatedesc'] . ", ";
		}

		$event = "";

		$event_icon = "";

		if ($result['channel_event'] != '') {
			if ($result['channel_event'] == "Announce" || $result['channel_event'] == "Music On Hold") {
                        	$event_icon = "<i class='fa fa-volume-up'></i>";
			}

			if ($result['channel_event'] == "NO ANSWER" || $result['channel_event'] == "BUSY") {
                        	$event_icon = "<i class='fa fa-exclamation-triangle'></i>";
			}

			$event = " (" . $event_icon . " " . $result['channel_event'] . ")";
		}

		echo "<td id='peer_" . $result['num'] . "_dnd' class='large' style='text-align: center; color: red;'>" . $dnd . "</td>";

		if ($result['connectedlinenum'] == '') {
			echo "<td id='peer_" . $result['num'] . "_state' class='large' style='text-align: center;'>" . $status_text . $incoming . $time . $event . "</td>";
		} else {
	                echo "<td id='peer_" . $result['num'] . "_state' class='large' style='text-align: center;'>" . $pref . $status_text . $incoming . $time . ", " . $talking . ": " . $result['connectedlinenum'] . $suff . $event . $name_from  . "</td>";
		}
			
		echo "</tr>";
}

echo "</tbody>";
echo "</table>";
?>