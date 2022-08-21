<?php

declare(strict_types=1);

use App\Kernel;
use Dotenv\Dotenv;

include __DIR__ . '/vendor/autoload.php';

try {
    session_start();
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $kernel = new Kernel($_GET, $_POST, $_FILES, $_SERVER, $_ENV, $_SESSION);
    echo $kernel->run();
} catch (\Throwable $e) {
    echo $e->getMessage();
}
