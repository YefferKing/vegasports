<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    */

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
    'api_key' => env('CLOUDINARY_API_KEY'), 
    'api_secret' => env('CLOUDINARY_API_SECRET'),
    'secure_url' => env('CLOUDINARY_SECURE_URL', true),
    'upload_folder' => env('CLOUDINARY_UPLOAD_FOLDER'),
];