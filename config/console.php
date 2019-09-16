<?php
// Add console commands here

$console->add(new App\Console\Command\TestCommand());
$console->add(new App\Console\Command\DeployCommand());
$console->add(new App\Console\Command\WatchCommand());
