<?php
require_once 'vendor/autoload.php';

ini_set('display_errors',1);
error_reporting(E_ALL);

use Symfony\Component\Console\Application;
$application = new Application;
$application->add(new \DataStructures\Commands\Performance());
$application->run();
