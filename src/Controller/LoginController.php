<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class LoginController extends AbstractController
{

    // youtubeAPIの認証キー
    public static $API_KEY = 'AIzaSyABl3f_6BFkXaqgv9EfmtRq-7k1AguJLnk';


    #[Route('/login', name:'login')]
    function index(Environment $twig): Response
    {

        $client = new \Google\Client();
        $client->setDeveloperKey(self::$API_KEY);

        // Define an object that will be used to make all API requests.
        $youtube = new \Google\Service\YouTube($client);

        try {
            $searchResponse = $youtube->search->listSearch('id,snippet', [
                'q' => '長尾景 不破湊',
                'maxResults' => 10,
                'order' => 'date',
            ]);
    dump($searchResponse['items']);
            $count = 1;
            $list = [];
            foreach($searchResponse['items'] as $item){
                dump($item);
                $list[$item['id']['videoId']] = $item['snippet']['title'];
                $count++;
            }

        } catch (\Exception$e) {
            dump($e);
        }

        // return new Response($twig->render('base.html.twig', [
        return new Response($twig->render('list.html.twig', [
            // 'conferences' => $conferenceRepository->findAll(),
            'controllerName' => 'loginController',
            'list' => $list,
        ]));
    }
}
