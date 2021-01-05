<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProgramRepository;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Program;
use App\Form\CommentType;
use App\Form\ProgramType;
use App\Service\Slugify;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
* @Route("/programs", name="program_")
*/
class ProgramController extends AbstractController
{
    /**
     * @Route("", name="index"), methods={"GET"})
     */
    public function index(ProgramRepository $programRepository): Response
    {
        return $this->render('program/index.html.twig', [
            'programs' => $programRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", methods={"GET", "POST"}, name="new")
     */
    public function new(Request $request, Slugify $slugify, MailerInterface $mailer) : Response
    {
        $program= new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $entityManager->persist($program);
            $entityManager->flush();
            $this->addFlash('success', 'La série a bien été ajoutée');

            $email = (new Email())
                    ->from($this->getParameter('mailer_from'))
                    ->to($this->getParameter('mailer_to'))
                    ->subject('Une nouvelle série vient d\'être publiée !')
                    ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));
        
            $mailer->send($email);
            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/new.html.twig', ["form" => $form->createView()]);
    }

    /**
    * @Route("/{slug}", methods={"GET"}, name="show")
    * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
    */
    public function show(Program $program): Response
    {

        if (!$program) {
        throw $this->createNotFoundException(
            'No program with id : '.$program.' found in program\'s table.'
        );
        }
        
        $seasons = $program->getSeasons();
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'seasons' => $seasons
        ]);
    }

    /**
     * @Route("/{programSlug}/seasons/{season_id}", methods={"GET"}, name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     */
    public function showSeason(Program $program, Season $season): Response
    {
    
        $episodes = $season->getEpisodes();

        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes,
        ]);
    }

    /**
     * @Route("/{programSlug}/seasons/{season_id}/episodes/{episodeSlug}", methods={"GET", "POST"}, name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"programSlug": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"season_id": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episodeSlug": "slug"}})
     */
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request): Response
    {
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setAuthor($user);
        $comment->setEpisode($episode);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }
        $comments = $this->getDoctrine()
        ->getRepository(Comment::class)
        ->findBy(
            ['episode' => $episode], 
            ['id' => 'DESC'],
            3
        );

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'comments' => $comments,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{slug}/edit", name="edit", methods={"GET","POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"slug": "slug"}})
     */
    public function edit(Request $request, Program $program): Response
    {
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }

}
