<?php

$privateStorageDriver = env('ZAZU_PRIVATE_STORAGE_DRIVER', 'local');

$s3Disk = [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
    'throw' => false,
    'report' => false,
];

$privateDisk = match ($privateStorageDriver) {
    'local' => [
        'driver' => 'local',
        'root' => env('ZAZU_PRIVATE_STORAGE_ROOT', storage_path('app/private')),
        'serve' => true,
        'visibility' => 'private',
        'throw' => false,
        'report' => false,
    ],
    's3' => array_merge($s3Disk, [
        'visibility' => 'private',
    ]),
    default => throw new InvalidArgumentException(
        'ZAZU_PRIVATE_STORAGE_DRIVER must be either local or s3.'
    ),
};

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | The private business-file disk is a logical application boundary.
    | Its local default preserves the standalone Windows installation.
    | Select a remote driver only after its Flysystem adapter is installed
    | and the provider-specific security and recovery checks are accepted.
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'private' => $privateDisk,

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => $s3Disk,

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when
    | the "storage:link" Artisan command is executed.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
