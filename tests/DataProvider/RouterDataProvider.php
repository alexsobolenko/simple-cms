<?php

declare(strict_types=1);

namespace Test\DataProvider;

final class RouterDataProvider
{
    /**
     * @return array
     */
    public function routesNotFoundCases(): array
    {
        return [
            ['/users', 'get'],
            ['/get', 'get'],
            ['/post', 'post'],
        ];
    }
}
