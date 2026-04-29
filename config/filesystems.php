<?php

$s3Bucket = env('AWS_BUCKET', env('LARAVEL_CLOUD_BUCKET'));
$s3DiskConfig = [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', env('AWS_REGION', 'auto')),
    'bucket' => $s3Bucket,
    'url' => env('AWS_URL'),
    'endpoint' => env('AWS_ENDPOINT'),
    'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', true),
    'throw' => false,
    'report' => false,
];

$cloudDiskConfig = env('LARAVEL_CLOUD_DISK_CONFIG');
$cloudDisks = [];

if (is_string($cloudDiskConfig) && $cloudDiskConfig !== '') {
    $decodedCloudDiskConfig = json_decode($cloudDiskConfig, true);

    if (is_array($decodedCloudDiskConfig)) {
        if (isset($decodedCloudDiskConfig['driver'])) {
            $cloudDiskName = env('FILESYSTEM_DISK', 'laravel-cloud');
            $cloudDisks[$cloudDiskName] = $decodedCloudDiskConfig;
        } elseif (isset($decodedCloudDiskConfig['disks']) && is_array($decodedCloudDiskConfig['disks'])) {
            $cloudDisks = $decodedCloudDiskConfig['disks'];
        } else {
            $cloudDisks = $decodedCloudDiskConfig;
        }
    }
}

$disks = [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'serve' => true,
        'throw' => false,
        'report' => false,
    ],

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
    ],

    's3' => $s3DiskConfig,
];

foreach ($cloudDisks as $name => $diskConfig) {
    if (is_string($name) && is_array($diskConfig) && isset($diskConfig['driver'])) {
        if ($diskConfig['driver'] === 's3') {
            $diskConfig = array_replace($s3DiskConfig, $diskConfig);
        }

        $disks[$name] = $diskConfig;
    }
}

$configuredPublicUploadsDisk = env('PUBLIC_UPLOADS_DISK', env('FILESYSTEM_DISK', 'public'));
$publicUploadsDisk = 'public';
$diskPodeSerUsado = function (string $diskName) use ($disks): bool {
    if (!isset($disks[$diskName]) || !is_array($disks[$diskName])) {
        return false;
    }

    if (($disks[$diskName]['driver'] ?? null) !== 's3') {
        return true;
    }

    return filled($disks[$diskName]['bucket'] ?? null);
};

if (
    is_string($configuredPublicUploadsDisk)
    && $diskPodeSerUsado($configuredPublicUploadsDisk)
) {
    $publicUploadsDisk = $configuredPublicUploadsDisk;
} elseif ($diskPodeSerUsado('s3')) {
    $publicUploadsDisk = 's3';
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    'public_uploads_disk' => $publicUploadsDisk,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => $disks,

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
