<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class TestController extends AbstractController
{
    #[Route('/test', name:'test')]
function index(Environment $twig): Response
    {
    // dump(['test' => 999]);
    return new Response($twig->render('base.html.twig', [
        // 'conferences' => $conferenceRepository->findAll(),
        'controllerName' => 'testController',
    ]));
}
}
