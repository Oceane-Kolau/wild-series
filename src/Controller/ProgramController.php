<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Repository\ProgramRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/programs", name="program_")
*/
class ProgramController extends AbstractController
{
    /**
     * @Route("/", name="index"), methods={"GET"})
     */
    public function index(ProgramRepository $programRepository): Response
    {
        return $this->render('program/index.html.twig', [
            'programs' => $programRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{id}", methods={"GET"}, requirements={"id"="\d+"}, name="show")
     * 
     */
    public function show(int $id, ProgramRepository $programRepository): Response
    {
        
        $program = $programRepository->find($id);

        if (!$program) {
        throw $this->createNotFoundException(
            'No program with id : '.$id.' found in program\'s table.'
        );
        }
        return $this->render('program/show.html.twig', [
            'program' => $program,
        ]);
    }
}
