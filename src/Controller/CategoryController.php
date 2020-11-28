<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/categories", name="category_")
*/
class CategoryController extends AbstractController
{
    /**
     * @Route("/", name="index"), methods={"GET"})
     */
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * @Route("/{categoryName}", methods={"GET"}, name="show")
     * 
     */
    public function show(string $categoryName): Response
    {
        $category = $this->getDoctrine()
        ->getRepository(Category::class)
        ->findOneBy(['name' => $categoryName]);

        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['category' => $category], 
                ['id' => 'DESC'],
                3
            );

        if (!$category) {
            throw $this->createNotFoundException(
                'Aucune série n\'a trouvée dans la catégorie : '.$categoryName.''
            );
        }

        return $this->render('category/show.html.twig', [
            'programs' => $programs,
            'category' => $category
        ]);
    }
}
