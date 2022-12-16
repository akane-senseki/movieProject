<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Liver;
use App\Entity\Video;
use App\Entity\Channel;

class VideoController extends BaseController
{

    // youtubeAPIの認証キー
    private static $API_KEY = 'AIzaSyC1ed3k0MAHGv6ciLwsChtq-pQjFbvSoq0';

    //結果データの種類
    private static $KIND_VIDEO = 'youtube#video';
    private static $KIND_CHANNEL = 'youtube#channel';
    private static $KIND_PLAYLIST = 'youtube#playlist';

    #[Route('/list', name:'index')]
    function indexAction(Environment $twig, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        
        $sql = <<<___SQL
            SELECT * FROM video WHERE JSON_CONTAINS(`member`, :liver_id, '$.member_list')
        ___SQL;
        //動くSQL
        //SELECT * FROM `video` WHERE JSON_CONTAINS(`member`, 1, '$.member_list');

        $params = ['liver_id' => 1];

        $resultObj = $em->getConnection()->prepare($sql)->execute($params)->fetchAll();
// dump($resultObj);

        return new Response($twig->render('list.html.twig', [
            'list' => [],
        ]));
    }


    #[Route('/save_data', name:'getData')]
    function saveDataAction(Environment $twig, ManagerRegistry $doctrine): Response
    {
        $client = new \Google\Client();
        $client->setDeveloperKey(self::$API_KEY);
        $youtube = new \Google\Service\YouTube($client);
        


        $em = $doctrine->getManager();
        $liverList = $em->getRepository(Liver::class)->findAll();
        $videoIdList = $em->getRepository(Video::class)->getIdList();
        $channelIdList = $em->getRepository(Channel::class)->getIdList();

        //1.channelリストとliverリストを全件取得しておく
        //2.動画のIDを全件取得しておく
        //3_1 クーロンなら. 各ライバーごとに1件だけ取得し、2.のIDリストになかったら30件ほど動画データを取得
        //3_2 requestなら、該当ライバーで1件だけ取得し、2.のIDリストになかったら30件ほど動画データを取得
        //4.取得した動画データのIDとIDリストを比較してDBになかったら保存
        //4_1.概要全文を一度に取得できないので別途取得
        //4_2.この時に参加ライバーとジャンル分けをして登録する
        //5.保存時、channelデータも確認。DBになかったら登録する

        $em->beginTransaction();
        try {
            $searchResponse = $youtube->search->listSearch('id,snippet', [
                'q' => '甲斐田晴',
                'maxResults' => 50,
                'order' => 'date',
                // 'publishedBefore' => '2020-04-10T00%3A00%3A00Z',
            ]);

            
            $list = [];
            foreach($searchResponse['items'] as $item){
                //DBに存在しなかったら保存
                if(!in_array($item['id']['videoId'], $videoIdList)){
                    $newVideo = new Video();
                    $newVideo->setId($item['id']['videoId']);
                    $newVideo->setChannelId($item['snippet']['channelId']);
                    $newVideo->setPublishedAt($item['snippet']['publishedAt']);
                    $newVideo->setTitle($item['snippet']['title']);
                    $newVideo->setPath($item['snippet']['thumbnails']['medium']['url']);
                    
                    $videoDitail = $youtube->videos->listVideos("snippet", array('id' => $item['id']['videoId']));
                    $description = $videoDitail['items'][0]['snippet']['description'];
                    $newVideo->setDescription($description);

                    //カテゴリの設定

                    //参加ライバーの設定

                    //video保存
                    $em->persist($newVideo);
                    $em->flush($newVideo);
                    
                    //channelがDBに存在しないならそれも保存する
                    if(!in_array($item['snippet']['channelId'], $channelIdList)){
                        $channelResponse = $youtube->search->listSearch('id,snippet', [
                            'channelId' => $item['snippet']['channelId'],
                            'type' => 'channel',
                        ]);
                        $channel = isset($channelResponse['items'][0]) ? $channelResponse['items'][0] : [];

                        //一応存在チェック
                        if($channel){
                            $newChannel = new Channel();
                            $newChannel->setId($channel['id']['channelId']);
                            $newChannel->setTitle($channel['snippet']['channelTitle']);
                            $newChannel->setPath($channel['snippet']['thumbnails']['medium']['url']);
                            
                            //channel保存
                            $em->persist($newChannel);
                            $em->flush($newChannel);

                            //重複登録しないようにこの時点でidリストに追加
                            $channelIdList = array_merge($channelIdList, [$channel['id']['channelId']]);
                            
                        }
                    }
                    
                }
                $list[$item['id']['videoId']] = $item['snippet']['title'];
            }

            foreach ($searchResponse['items'] as $searchResult) {
                switch ($searchResult['id']['kind']) {
                  case self::$KIND_VIDEO:
                    break;
                  case self::$KIND_CHANNEL:
                    break;
                  case self::$KIND_PLAYLIST:
                    break;
                }
              }
              $em->commit();

        } catch (\Exception $e) {
            $em->rollback();
            $em->close();
            dump($e);
        }
        throw new \Exception("9999");
        // return new Response($twig->render('base.html.twig', [
        return new Response($twig->render('list.html.twig', [
            // 'conferences' => $conferenceRepository->findAll(),
            'controllerName' => 'loginController',
            'list' => $list,
        ]));
    }
}
