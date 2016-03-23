<?php

return function () {
    $server = League\Glide\ServerFactory::create(
        [
            'source' => new League\Flysystem\Filesystem(
                new League\Flysystem\Adapter\Local(
                    __DIR__ . '/../assets/image'
                )
            ),
            'cache' => new League\Flysystem\Filesystem(
                new League\Flysystem\Adapter\Local(
                    __DIR__ . '/../public/static/image'
                )
            ),
            'driver' => 'gd',
        ]
    );

    return $server;
};