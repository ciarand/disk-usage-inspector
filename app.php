<?php
require_once "vendor/autoload.php";

// command.php
use Ciarand\DiskUtility\DiskUtility;
use Ciarand\DiskUtility\Check;

$application = new DiskUtility();
$application->run();
