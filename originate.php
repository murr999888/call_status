<?php

if (!isset($_GET['num_from']) || !isset($_GET['num_to'])) {
    return;
}

$num_from = trim($_GET['num_from']);
$num_to = trim($_GET['num_to']);

// Replace with your port if not using the default.
// If unsure check /etc/asterisk/manager.conf under [general];
$port = 5038;

// Replace with your username. You can find it in /etc/asterisk/manager.conf.
// If unsure look for a user with "originate" permissions, or create one as
// shown at http://www.voip-info.org/wiki/view/Asterisk+config+manager.conf.
$username = "orig";

// Replace with your password (refered to as "secret" in /etc/asterisk/manager.conf)
$password = "orig";

// Context for outbound calls. See /etc/asterisk/extensions.conf if unsure.
$context = "from-internal";

$socket = stream_socket_client("tcp://127.0.0.1:$port");
if ($socket) {
    echo "Соединение с сервером установлено. Отправлена аутентификация.\r\n";

    // Prepare authentication request
    $authenticationRequest = "Action: Login\r\n";
    $authenticationRequest .= "Username: $username\r\n";
    $authenticationRequest .= "Secret: $password\r\n";
    $authenticationRequest .= "Events: off\r\n\r\n";

    // Send authentication request
    $authenticate = stream_socket_sendto($socket, $authenticationRequest);
    if ($authenticate > 0) {
        // Wait for server response
        usleep(200000);

        // Read server response
        $authenticateResponse = fread($socket, 4096);

        // Check if authentication was successful
        if (strpos($authenticateResponse, 'Success') !== false) {
            echo "Аутентификация успешна.\r\nПытаемся звонить.\r\n";

            // Prepare originate request
            $originateRequest = "Action: Originate\r\n";
            $originateRequest .= "Channel: SIP/$num_from\r\n";
            $originateRequest .= "Callerid: Click 2 Call\r\n";
            $originateRequest .= "Exten: $num_to\r\n";
            $originateRequest .= "Context: $context\r\n";
            $originateRequest .= "Priority: 1\r\n";
            $originateRequest .= "Async: yes\r\n\r\n";

            // Send originate request
            $originate = stream_socket_sendto($socket, $originateRequest);
            if ($originate > 0) {
                // Wait for server response
                usleep(200000);

                // Read server response
                $originateResponse = fread($socket, 4096);

                // Check if originate was successful
                if (strpos($originateResponse, 'Success') !== false) {
                    echo "Звонок начат. Сейчас должен зазвонить телефон с номером " . $num_from . ".\r\n";
                    echo "Снимите трубку и ожидайте соединения. Теперь эту форму можно закрыть.\r\n";
                } else {
                    echo "Ошибка начала звонка.\r\n";
                }
            } else {
                echo "Ошибка отправки аутентификации серверу.\r\n";
            }
        } else {
            echo "Ошибка аутентификации на сервере.\r\n";
        }
    } else {
        echo "Could not write authentication request to socket.\r\n";
    }
} else {
    echo "Unable to connect to socket.\r\n";
}
