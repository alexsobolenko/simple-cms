<?php

declare(strict_types=1);

use App\Kernel;
use Dotenv\Dotenv;

include __DIR__ . '/vendor/autoload.php';

try {
    session_start();
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $kernel = new Kernel(
        get: $_GET,
        post: $_POST,
        files: $_FILES,
        server: $_SERVER,
        env: $_ENV,
        session: $_SESSION
    );
    echo $kernel->run();
} catch (\Throwable $e) {
    echo $e->getMessage();
}
