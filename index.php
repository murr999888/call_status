<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>Телефонные линии</title>
    	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
	<link href="css/main.css" rel="stylesheet">
	<script src="js/jquery.min.js"></script>

	<link rel="icon" type="image/ico" href="favicon.ico">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script> 
		setInterval(function(){
			$('#pbx_int').load('pbx_internal.php?' + Date.now());
		}, 1000);

		setInterval(function(){
			$('#pbx_ext').load('pbx_external.php?' + Date.now());
		}, 1000);

		setInterval(function(){
			$('#pbx_queue').load('pbx_queue.php?' + Date.now());
		}, 1000);

		setInterval(function(){
			$('#pbx_queue_abandon').load('pbx_queue_abandon.php?' + Date.now());
		}, 15000);
		
		$(document).ready(function(){
			$('#pbx_int').load('pbx_internal.php');
			$('#pbx_ext').load('pbx_external.php');
			$('#pbx_queue').load('pbx_queue.php');
			$('#pbx_queue_abandon').load('pbx_queue_abandon.php');
		});
	</script>
</head>

<body>
	<div class="main">
		<div class="left">
			<div style="padding: 3px 5px 0px 10px;">
				<span class="text_header">Внутренние номера</span>
				<div id="pbx_int"></div>

				<span class="text_header">Абоненты в очереди</span>
				<div id="pbx_queue"></div>

				<span class="text_header">Ушедшие из очереди сегодня (последние 5)</span>
				<div id="pbx_queue_abandon"></div>
			</div>
		</div>
		<div class="right" >
			<div style="padding: 3px 10px 0px 5px;">
				<span class="text_header">Внешние линии</span>
				<div id="pbx_ext"></div>
			</div>
		</div>
	</div>
	<div class="legend">
		<div><span style="color: red; font-weight: bold;">DND</span> - режим "не беспокоить" (*78) включен, <span style="font-weight: bold;">IDLE</span> - ожидание, <span style="font-weight: bold;">INCOMING</span> - входящий, <span style="font-weight: bold;">OUTCOMING</span> - исходящий</div>
		<div><span class="blue_bk" style="padding: 3px;">DIALING</span> - номер набран и ожидается соединение, <span class="red_bk" style="padding: 3px;">RINGING</span> - идет вызов, <span class="green_bk" style="padding: 3px;">CONNECTED</span> - соединение установлено.</div>
	</div>
</body>
</html>