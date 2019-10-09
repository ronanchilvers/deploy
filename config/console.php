<?php
// Add console commands here

$console->add(new App\Console\Command\TestCommand());

$console->add(new App\Console\Command\Queue\WatchCommand());
$console->add(new App\Console\Command\User\CreateCommand());
$console->add(new App\Console\Command\User\StatusCommand());
