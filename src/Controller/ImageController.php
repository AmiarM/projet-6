<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
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
    public function index(TrickRepository $trickRepository, Request $request, PaginatorInterface $paginator, ImageRepository $imageRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException('utilisateur innéxistant');
        }
        $tricks = $trickRepository->findBy([
            'user' => $user
        ]);
        foreach ($tricks as $trick) {
            $images = $imageRepository->findBy([
                'trick' => $trick
            ]);
        }
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
            $trick = $form->get('trick')->getData();
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
            $image->setName('/uploads/' . $fichier);
            $image->setTrick($trick);
            $imageRepository->add($image, true);
            $this->addFlash('success', 'Image ajouté avec succès');
            return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('image/new.html.twig', [
            'image' => $image,
            'form' => $form,
            'action' => 'Ajouter une Image'
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
            $image->setName('/uploads/' . $fichier);
            $trick = $form->get('trick')->getData();
            $image->setTrick($trick);
            $imageRepository->add($image, true);
            $this->addFlash('success', 'Image Modifiée avec succès');
            return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('image/edit.html.twig', [
            'image' => $image,
            'form' => $form,
            'action' => 'Editer une  Image'
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_image_delete", methods={"DELETE","POST"})
     */
    public function delete(Request $request, Image $image, ImageRepository $imageRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            $imageRepository->remove($image, true);
            $this->addFlash('success', 'Image supprimée avec succès');
        }

        return $this->redirectToRoute('app_image_index', [], Response::HTTP_SEE_OTHER);
    }
}
