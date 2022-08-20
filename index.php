<?php

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

echo \App\Kernel::run([
    ['get', '/', ['Default', 'index']],
]);
