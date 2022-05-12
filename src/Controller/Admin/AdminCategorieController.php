<?php

namespace App\Controller\Admin;

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

class AdminCategorieController extends AbstractController
{

    private $entityManagerInterface;
    private $categorieRepository;

    public function __construct(EntityManagerInterface $entityManagerInterface, CategorieRepository $categorieRepository)
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->categorieRepository = $categorieRepository;
    }

    /**
     * @Route("admin/categories", name="admin_categories")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $categories = $this->categorieRepository->findAll();
        $paginations = $paginator->paginate(
            $categories, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('admin/categorie/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/admin/categorie/add", name="admin_categorie_add")
     */
    public function add(Request $request): Response
    {
        $category = new Categorie();
        $form = $this->createForm(CategorieType::class, $category);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            $this->entityManagerInterface->persist($category);
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/categorie/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/categorie/edit/{id}", name="admin_categorie_edit")
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
            $this->entityManagerInterface->persist($categorie);
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('admin_categories');
        }
        return $this->render('admin/categorie/edit.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie
        ]);
    }

    /**
     * @Route("/admin/categorie/delete/{id}", name="admin_categorie_delete")
     */
    public function delete($id): Response
    {
        $category = $this->categorieRepository->find($id);
        if (!$category) {
            throw new NotFoundHttpException("la catégorie d'id $id n'existe pas");
        }
        $this->entityManagerInterface->remove($category);
        $this->entityManagerInterface->flush();
        return $this->redirectToRoute("admin_categories");
    }
}
