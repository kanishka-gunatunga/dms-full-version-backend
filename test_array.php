<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$request = new \Illuminate\Http\Request();
$request->merge(['sector_ids' => [1]]);

$controller = new App\Http\Controllers\UserAPIController();
$response = $controller->users_by_sectors($request);
echo $response->getContent();
