<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Http::getFacadeRoot()->globalOptions([
        'timeout' => 180,
        'connect_timeout' => 15,
        ]);
        // URL::forceScheme('https');

        try {
            \Illuminate\Support\Facades\Storage::extend('google', function($app, $config) {
                $client = new \Google\Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);
                
                $service = new \Google\Service\Drive($client);
                
                $options = [];
                if (isset($config['teamDriveId'])) {
                    $options['teamDriveId'] = $config['teamDriveId'];
                }
                
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folderId'] ?? '', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);
        
                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            // Log or ignore
        }
    }
}
