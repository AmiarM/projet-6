<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/categorie")
 */
class CategorieController extends AbstractController
{

    /**
     * $categoryRepository
     *
     * @var CategorieRepository
     */
    private $categorieRepository;
    /**
     * $manager
     *
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(EntityManagerInterface $manager, CategorieRepository $categorieRepository)
    {
        $this->manager = $manager;
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * @Route("/lists", name="app_categories")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $categories = $this->categorieRepository->findAll();
        $paginations = $paginator->paginate(
            $categories, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            9 /* limit per page */
        );
        return $this->render('categorie/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/add", name="app_categorie_add")
     */
    public function add(Request $request): Response
    {
        $category = new Categorie();
        $form = $this->createForm(CategorieType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->manager->persist($category);
            $this->manager->flush();
            $this->addFlash('success', 'Categorie added successfully');
            return $this->redirectToRoute('app_categories');
        }
        return $this->render('categorie/create.html.twig', [
            'form' => $form->createView(),
            'action' => 'Ajouter une Catégorie'
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_categorie_edit")
     */
    public function edit(Request $request, $id): Response
    {
        $categorie = $this->categorieRepository->find($id);
        if (!$categorie) {
            throw new NotFoundHttpException("la catégorie d'id $id n'existe pas");
        }
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $categorie = $form->getData();
            $this->manager->persist($categorie);
            $this->manager->flush();
            $this->addFlash('success', 'Categorie edited successfully');
            return $this->redirectToRoute('app_categories');
        }
        return $this->render('categorie/edit.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
            'action' => 'Editer une Catégorie'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_categorie_delete")
     */
    public function delete($id): Response
    {
        $category = $this->categorieRepository->find($id);
        if (!$category) {
            throw new NotFoundHttpException("la catégorie d'id $id n'existe pas");
        }
        $this->manager->remove($category);
        $this->manager->flush();
        $this->addFlash('success', 'Categorie deleted successfully');
        return $this->redirectToRoute("app_categories");
    }
}
