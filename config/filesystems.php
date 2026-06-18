<?php

return [
    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'throw'  => false,
        ],

        // Disk private untuk file data
        // TIDAK ada akses langsung dari web
        'private' => [
            'driver'     => 'local',
            'root'       => storage_path('app/private'),
            'throw'      => true,
            // Tidak ada URL publik - hanya bisa diakses via controller yang terotorisasi
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
