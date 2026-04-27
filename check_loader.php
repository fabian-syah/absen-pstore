<?php
$loader = require 'vendor/autoload.php';
$prefixes = $loader->getPrefixesPsr4();
foreach ($prefixes as $prefix => $path) {
    if (strpos($prefix, 'Minishlink') !== false) {
        echo "$prefix => " . json_encode($path) . "\n";
    }
}
