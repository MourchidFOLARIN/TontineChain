<?php
function test_port($host, $port) {
    $timeout = 5;
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        echo "PORT $port on $host is OPEN\n";
        fclose($fp);
    } else {
        echo "PORT $port on $host is CLOSED ($errstr)\n";
    }
}

// Tester les ports courants et l'alternative 2525
echo "--- Testing from Render ---\n";
test_port('in-v3.mailjet.com', 587);
test_port('in-v3.mailjet.com', 2525);
test_port('smtp.sendgrid.net', 2525);
test_port('smtp.gmail.com', 587);
