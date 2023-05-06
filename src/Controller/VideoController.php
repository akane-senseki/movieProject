<?php

namespace App\Controller;

use App\Entity\Liver;
use App\Entity\Video;
use Twig\Environment;
use App\Consts\Consts;
use App\Entity\Channel;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class VideoController extends BaseController
{

    //結果データの種類
    private static $KIND_VIDEO = 'youtube#video';
    private static $KIND_CHANNEL = 'youtube#channel';
    private static $KIND_PLAYLIST = 'youtube#playlist';

    #[Route('/list', name:'index')]
    function indexAction(Request $request, Environment $twig, ManagerRegistry $doctrine): Response
    {
        $em = $doctrine->getManager();
        session_start();

        //初期設定
        if(!isset($_SESSION['is_all'])){
            $_SESSION['is_all'] = false;
            $_SESSION['is_original'] = false;
            $_SESSION['is_clip'] = false;
            $_SESSION['is_vocal'] = false;
            $_SESSION['is_hand'] = false;
            $_SESSION['is_MMD'] = false;
            $_SESSION['from_date'] = null;
            $_SESSION['to_date'] = null;
            $_SESSION['keyword'] = "";
        }else{
            //bool値変換(vueとの兼ね合い)
            $_SESSION['is_all'] = $this->ConvertFlgToBool($_SESSION['is_all']);
            $_SESSION['is_original'] = $this->ConvertFlgToBool($_SESSION['is_original']);
            $_SESSION['is_clip'] = $this->ConvertFlgToBool($_SESSION['is_clip']);
            $_SESSION['is_vocal'] = $this->ConvertFlgToBool($_SESSION['is_vocal']);
            $_SESSION['is_hand'] = $this->ConvertFlgToBool($_SESSION['is_hand']);
            $_SESSION['is_MMD'] = $this->ConvertFlgToBool($_SESSION['is_MMD']);
        }

        $keywordSelect = "( `title` LIKE '%".$_SESSION['keyword']."%' OR `description` LIKE '%".$_SESSION['keyword']."%' )";

        //todo ここでエラーになったらそのライバーは未実装だよ！エラーが必要になる
        $originalChannelId = $em->getRepository(Liver::class)->findOneBy(['twitter_id' => 'kei_nagao2434'])->getChannelId();

        $categorySelect = "";
        if(!$_SESSION['is_all']){
            if($_SESSION['is_original']){
                $categorySelect = "`channel_id` = '".$originalChannelId."'";
            }

            if($_SESSION['is_clip']){
                $categorySelect .= $categorySelect == "" ? "" : " OR";
                $categorySelect .= " JSON_CONTAINS(`category`, ".Consts::$categoryList['切り抜き'].", '$.category_list')";
            }

            if($_SESSION['is_MMD']){
                $categorySelect .= $categorySelect == "" ? "" : " OR";
                $categorySelect .= " JSON_CONTAINS(`category`, ".Consts::$categoryList['MMD'].", '$.category_list')";
            }

            if($_SESSION['is_vocal']){
                $categorySelect .= $categorySelect == "" ? "" : " OR";
                $categorySelect .= " JSON_CONTAINS(`category`, ".Consts::$categoryList['人力'].", '$.category_list')";
            }

            if($_SESSION['is_hand']){
                $categorySelect .= $categorySelect == "" ? "" : " OR";
                $categorySelect .= " JSON_CONTAINS(`category`, ".Consts::$categoryList['手描き'].", '$.category_list')";
            }

            //何かしら条件があるならくくる
            if($categorySelect){
                $categorySelect = " AND ( ".$categorySelect." )";
            }
        }

        $fromDateSelect = "";
        if($_SESSION['from_date']){
            $fromDateSelect = " AND `published_at` >= '".$_SESSION['from_date']."'";
        }

        $toDateSelect = "";
        if($_SESSION['to_date']){
            $toDateSelect = " AND `published_at` <= '".$_SESSION['to_date']."'";
        }


        $sql = <<<___SQL
        SELECT * 
        FROM video 
        WHERE $keywordSelect
        $categorySelect
        $fromDateSelect
        $toDateSelect
        AND JSON_CONTAINS(`member`, 1, '$.member_list')
        ORDER BY `published_at` DESC
        ___SQL;
        //動くSQL
        //SELECT * FROM `video` WHERE JSON_CONTAINS(`member`, 1, '$.member_list');

        $params = [
            // 'liver_id' => 1, 
            // 'category_id' => 3, 
        ];
        
        $videoList = $em->getConnection()->prepare($sql)->execute($params)->fetchAll();

        //チャンネルデータを取得
        $channnelIdList = [];
        foreach($videoList as $v){
            $channnelIdList[$v['channel_id']] = $v['channel_id'];
        }
        
        $channnelList = $em->getRepository(Channel::class)->getListByIds($channnelIdList);
        return new Response($twig->render('list.html.twig', [
            'channel_list' => $channnelList,
            'list' => $videoList,
            'is_all' => $this->ConvertFlgToString($_SESSION['is_all']),
            'is_original' => $this->ConvertFlgToString($_SESSION['is_original']),
            'is_clip' => $this->ConvertFlgToString($_SESSION['is_clip']),
            'is_vocal' => $this->ConvertFlgToString($_SESSION['is_vocal']),
            'is_hand' => $this->ConvertFlgToString($_SESSION['is_hand']),
            'is_MMD' => $this->ConvertFlgToString($_SESSION['is_MMD']),
            'to_date' => $_SESSION['to_date'],
            'from_date' => $_SESSION['from_date'],
            'keyword' => $_SESSION['keyword'],
        ]));
    }


    #[Route('/save_data', name:'getData')]
    function saveDataAction(Environment $twig, ManagerRegistry $doctrine): Response
    {
        $youtube = $this->getYoutube();

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
                'q' => '長尾景',
                'maxResults' => 50,
                'order' => 'date',
                'publishedBefore' => '2020-11-15T00%3A00%3A00Z',
            ]);

            $list = [];
            $count = 0;
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
                    $newVideo = Consts::setCategoryByConsts($newVideo);

                    //参加ライバーの設定
                    $newVideo = Consts::setMemberByConsts($liverList, $newVideo);

                    //video保存
                    $em->persist($newVideo);
                    $em->flush($newVideo);

                    $count++;
                    
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
        }

        throw new \Exception($count."個の動画が追加されました！");
        // return new Response($twig->render('base.html.twig', [
        return new Response($twig->render('list.html.twig', [
            // 'conferences' => $conferenceRepository->findAll(),
            'controllerName' => 'loginController',
            'list' => [],
        ]));
    }

    #[Route('/post', name:'post')]
    function searchAction(Request $request, Environment $twig, ManagerRegistry $doctrine)
    {
        $session = $request->getSession();

        session_start();

        $_SESSION['is_all'] = $this->ConvertFlgToBool($request->get('is_all'));
        $_SESSION['is_original'] = $this->ConvertFlgToBool($request->get('is_original'));
        $_SESSION['is_clip'] = $this->ConvertFlgToBool($request->get('is_clip'));
        $_SESSION['is_vocal'] = $this->ConvertFlgToBool($request->get('is_vocal'));
        $_SESSION['is_hand'] = $this->ConvertFlgToBool($request->get('is_hand'));
        $_SESSION['is_MMD'] = $this->ConvertFlgToBool($request->get('is_MMD'));
        $_SESSION['to_date'] = $request->get('to_date');
        $_SESSION['from_date'] = $request->get('from_date');
        $_SESSION['keyword'] = $request->get('keyword');

        // $session->set("is_all", $this->ConvertFlgToBool($request->get('is_all')));
        // $session->set("is_original", $this->ConvertFlgToBool($request->get('is_original')));
        // $session->set("is_clip", $this->ConvertFlgToBool($request->get('is_clip')));
        // $session->set("is_hand", $this->ConvertFlgToBool($request->get('is_hand')));
        // $session->set("is_MMD", $this->ConvertFlgToBool($request->get('is_MMD')));
        // $session->set("to_date", $request->get('to_date'));
        // $session->set("from_date", $request->get('from_date'));
        // $session->set("keyword", $request->get('keyword'));
        return $this->redirectToRoute('index');
    }

    function ConvertFlgToBool($flg){
        return $flg == "true" ? true : false;
    }

    function ConvertFlgToString($flg){
        return $flg == true ? "true" : "false";
    }
}
