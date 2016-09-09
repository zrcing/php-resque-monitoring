<?php
/**
 * @author Liao Gengling <liaogling@gmail.com>
 */

require '../vendor/autoload.php';

use \Deer\QueueMonitoring\QueueMonitoring;

$dsn = "redis://user:llAbcSpace123@127.0.0.1:6379";

$o = new QueueMonitoring();
$o->init($dsn, 7);
$o->display();