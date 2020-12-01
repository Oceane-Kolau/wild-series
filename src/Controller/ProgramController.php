<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\ProgramRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;


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
    */
    public function show($id, ProgramRepository $programRepository): Response
    {
        $program = $programRepository->find($id);

        if (!$program) {
        throw $this->createNotFoundException(
            'No program with id : '.$id.' found in program\'s table.'
        );
        }
        
        $seasons = $program->getSeasons();
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    /**
     * @Route("/{programId}/seasons/{seasonId}", requirements={"seasonId"="\d+"}, methods={"GET"}, name="season_show")
     */
    public function showSeason(int $programId, int $seasonId): Response
    {
        $program = $this->getDoctrine()
        ->getRepository(Program::class)
        ->findOneBy(['id' => $programId]);

        $season = $this->getDoctrine()
        ->getRepository(Season::class)
        ->findOneBy(['id' => $seasonId]);


        $episodes = $season->getEpisodes();

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes,
        ]);
    }

}
