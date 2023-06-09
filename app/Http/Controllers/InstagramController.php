<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Instagram\Api;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class InstagramController extends Controller
{
    public $client;

    public function __construct()
    {
        $this->client = new Client([
            // 'proxy' => [
            //     'https' => '117.2.28.235:55443' #this is a dummy example, change with your proxy
            //    #'https' => 'login:password@ip:port
            // ],
            // 'verify' => false,
            'proxy' => 'http://maxuplol:vkp9kb4e3olk@188.74.210.207:6286'
        ]);
    }

    public function user($username)
    {
        $cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
        $api = new Api($cachePool, $this->client);

        try {
            $api->login(config('services.insta.username'), config('services.insta.password')); // mandatory
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Service is down!'
            ];
        }

        try {
            $profile = $api->getProfile($username);
        return [
            'success' => true,
            'data' => [
                'username' => $profile->getUserName(),
                'fullname' => $profile->getFullName(),
                'biography' => $profile->getBiography(),
                'profile_pic_url' => $profile->getProfilePicture(),
                'id' => $profile->getId(),
                'followers' => $profile->getFollowers(),
                'following' => $profile->getFollowing()
            ]
        ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Username not found!'
            ];
        }
    }

    public function feed($username)
    {
        $end_cursor = false;

        if(request()->has('end_cursor')) {
            $end_cursor = request()->get('end_cursor') ?? false;
        }

        $cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
        $api = new Api($cachePool, $this->client);

        try {
            $api->login(config('services.insta.username'), config('services.insta.password')); // mandatory
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Service is down!'
            ];
        }

        try {
            $profile = $api->getProfile($username);
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Username not found!'
            ];
        }

        try {

            if($end_cursor) {
                $profile->setEndCursor($end_cursor);
                $profile = $api->getMoreMedias($profile);
            }

            $profileMedias = $profile->getMedias();

            $medias = [];

            foreach($profileMedias as $media) {
                $medias[] = [
                    'id' => $media->getId(),
                    'caption' => $media->getCaption(),
                    'thumbnail' => $media->getThumbnails(),
                    'thumbnail_src' => $media->getThumbnailSrc(),
                    'link' => $media->getLink(),
                    'short_code' => $media->getShortCode(),
                    'is_video' => $media->isVideo()
                ];
            }

        return [
            'success' => true,
            'data' => [
                'medias' => $medias,
                'has_more_medias' => $profile->hasMoreMedias(),
                'end_cursor' => $profile->getEndCursor()
            ]
        ];
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Username not found!'
            ];
        }
    }

    public function reels($username)
    {
        $max_id = null;

        if(request()->has('max_id')) {
            $max_id = request()->get('max_id') ?? null;
        }

        $cachePool = new FilesystemAdapter('Instagram', 0, __DIR__ . '/../cache');
        $api = new Api($cachePool, $this->client);

        try {
            $api->login(config('services.insta.username'), config('services.insta.password')); // mandatory
        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Service is down!'
            ];
        }

        try {
            $profile = $api->getProfile($username);

            // dd($profile);

           $user_id = $profile->getId();

        } catch (\Throwable $th) {
            return [
                'success' => false,
                'message' => 'Username not found!'
            ];
        }

        try {
            $reelsFeed = $api->getReels($user_id, $max_id);


            $reels = [];

            foreach($reelsFeed->getReels() as $reel) {
                $reels[] = [
                    'id' => $reel->getId(),
                    'caption' => $reel->getCaption(),
                    'thumbnails' => $reel->getImages(),
                    'link' => $reel->getLink(),
                    'short_code' => $reel->getShortCode()
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'reels' => $reels,
                    'max_id' => $reelsFeed->getMaxId()
                ]
            ];

        } catch (\Throwable $th) {
            dd($th);
            return [
                'success' => false,
                'error' => $th
            ];
        }
    }

    public function proxy()
    {
        // $client = new Client([
        //     'proxy' => [
        //         'https' => '64.225.8.191:9993' #this is a dummy example, change with your proxy
        //        #'https' => 'login:password@ip:port
        //     ]
        // ]);

        $data   = $this->client->request('GET', 'https://api.ipify.org?format=json');
        $dataIp = json_decode((string)$data->getBody());
        return [
            'message' => 'IP for Instagram requests : ' . $dataIp->ip . "\n"
        ];
    }
}
