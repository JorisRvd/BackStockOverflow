<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/error', name: 'app_error')]
    public function show(FlattenException $exception): Response
    {
        return $this->render('error/error.html.twig', [
            'error' => $exception,
        ]);
    }
}
