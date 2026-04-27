<?php
require 'vendor/autoload.php';
require 'vendor/minishlink/web-push/src/VAPID.php';
require 'vendor/minishlink/web-push/src/Utils.php';

use Minishlink\WebPush\VAPID;

try {
    $keys = VAPID::createVapidKeys();
    echo "PUBLIC_KEY=" . $keys['publicKey'] . "\n";
    echo "PRIVATE_KEY=" . $keys['privateKey'] . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
