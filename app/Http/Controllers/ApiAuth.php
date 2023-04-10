<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;


class ApiAuth extends Controller
{
    public function index()
    {
        $cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');

        $api = new Api($cachePool);
        $api->login('Gramonlyofficial', '1234Gramonly.'); // mandatory
        $profile = $api->getProfile('sz_sujan');

        // $profile = $api->getMoreMedias($profile);
        dd($profile->getMedias());

        // dd($profile);
    }
}
