<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BaseController extends AbstractController
{

    // youtubeAPIの認証キー
    private static $API_KEY = 'AIzaSyADihyaBgjANLwi4ESmvPw3a-gUBE17cDQ';

    /*
    * 
    */
    public function getClient(){
        $httpClient = new \GuzzleHttp\Client([
            // 'proxy' => 'localhost:8888', // by default, Charles runs on localhost port 8888
            'verify' => false, // otherwise HTTPS requests will fail.
        ]);
        $client = new \Google\Client(['verify' => false]);
        $client->setHttpClient($httpClient);
        $client->setDeveloperKey(self::$API_KEY);

        return $client;
    }

    /*
    * 
    */
    public function getYoutube(){
        $client = $this->getClient();
        $youtube = new \Google\Service\YouTube($client);
        
        return $youtube;
    }
    // public static function getSubscribedServices(): array
    // {
    //     $parentArry = parent::getSubscribedServices();
    //     $array =  [
    //         'doctrine' => '?'.DoctrineHelper::class,
    //     ];
        
    //     return array_merge($parentArry, $array);
    // }
}
