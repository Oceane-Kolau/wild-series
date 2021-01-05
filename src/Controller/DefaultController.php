<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProgramRepository;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(ProgramRepository $programRepository): Response
    {
        return $this->render('index.html.twig', [
            'programs' => $programRepository->findAll(), 
        ]);
    }

    /**
     * @Route("/my-profile", name="app_profile", methods={"GET"})
     */
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('myProfile.html.twig', [
            'user' => $user
        ]);
    }
}
