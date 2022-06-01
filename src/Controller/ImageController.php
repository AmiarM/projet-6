<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Form\Image1Type;
use App\Form\IsFirstType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="app_image_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator, ImageRepository $imageRepository): Response
    {
        $images = $imageRepository->findAll();
        $paginations = $paginator->paginate(
            $images, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('image/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/new", name="app_image_new", methods={"GET", "POST"})
     */
    public function new(Request $request, ImageRepository $imageRepository): Response
    {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //on récupére les images transmises
            $imageFile = $form->get('name')->getData();
            //on genere le nouveau nom du fichier
            $fichier = md5(uniqid()) . "." . $imageFile->guessExtension();

            //on copie le fichier dans le dossier upload
            $imageFile->move(
                $this->getParameter('images_directory'),
                $fichier
            );
            //on stock l'image dans la base de données 
            $image->setName($fichier);
            $imageRepository->add($image, true);

            return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('image/new.html.twig', [
            'image' => $image,
            'form' => $form,
            'action' => 'Add Image'
        ]);
    }

    /**
     * @Route("/{id}", name="app_image_show", methods={"GET"})
     */
    public function show(Image $image): Response
    {
        return $this->render('image/show.html.twig', [
            'image' => $image,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_image_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //on récupére les images transmises
            $imageFile = $form->get('name')->getData();
            //on genere le nouveau nom du fichier
            $fichier = md5(uniqid()) . "." . $imageFile->guessExtension();

            //on copie le fichier dans le dossier upload
            $imageFile->move(
                $this->getParameter('images_directory'),
                $fichier
            );
            //on stock l'image dans la base de données 
            $image->setName($fichier);
            $imageRepository->add($image, true);

            return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('image/edit.html.twig', [
            'image' => $image,
            'form' => $form,
            'action' => 'Edit Image'
        ]);
    }

    /**
     * @Route("/{id}", name="app_image_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            $imageRepository->remove($image, true);
        }

        return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
    }
}
