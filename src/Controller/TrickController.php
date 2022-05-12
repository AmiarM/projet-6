<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Form\TrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/trick")
 */
class TrickController extends AbstractController
{
    private $entityManagerInterface;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManagerInterface = $entityManagerInterface;
    }

    /**
     * @Route("/", name="app_trick_index", methods={"GET"})
     */
    public function index(TrickRepository $trickRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $tricks = $trickRepository->findAll();
        $paginations = $paginator->paginate(
            $tricks, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('trick/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/new", name="app_trick_new")
     */
    public function new(Request $request): Response
    {
        $trick = new Trick();
        $user = $this->getUser();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on récupére les images transmises
            $images = $form->get('images')->getData();
            //on boucle sur les images
            foreach ($images as $image) {
                //on genere le nouveau nom du fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                //on copie le fichier dans le dossier upload
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                //on stock l'image dans la base de données 
                $image = new Image();
                $image->setName($fichier);
                $trick->addImage($image);
            }
            $trick->setUser($user);
            $this->entityManagerInterface->persist($trick);
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('app_trick_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
            'action' => 'New Trick'
        ]);
    }

    /**
     * @Route("/{id}", name="app_trick_show", methods={"GET"})
     */
    public function show(Trick $trick): Response
    {
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_trick_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //on récupére les images transmises
            $images = $form->get('images')->getData();
            //on boucle sur les images
            foreach ($images as $image) {
                //on genere le nouveau nom du fichier
                $fichier = md5(uniqid()) . "." . $image->guessExtension();
                //on copie le fichier dans le dossier upload
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                //on stock l'image dans la base de données 
                $image = new Image();
                $image->setName($fichier);
                $trick->addImage($image);
            }
            $trick->setUser($user);
            $trickRepository->add($trick);
            return $this->redirectToRoute('app_trick_index');
        }

        return $this->render('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'action' => 'Edit Trick'
        ]);
    }

    /**
     * @Route("/{id}", name="app_trick_delete", methods={"POST"})
     */
    public function delete(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $trickRepository->remove($trick);
        }

        return $this->redirectToRoute('app_trick_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/image/{id}", name="app_trick_image_delete", methods={"DELETE"})
     */
    public function deleteImage(Image $image, Request $request, EntityManagerInterface $entityManagerInterface)
    {
        $data = json_decode($request->getContent(), true);
        //on verifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['token'])) {
            //on récupere le nom de l'image
            $name = $image->getName();
            //on supprime l'image
            unlink($this->getParameter('images_directory') . '/' . $name);
            //on supprime l'entrée de la base
            $entityManagerInterface->remove($image);
            $entityManagerInterface->flush();
            //on répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['Error' => 'Token Invalide'], 400);
        }
    }

    /**
     * @Route("/{id}/comments", name="app_trick_comments")
     */
    public function comments(Request $request, TrickRepository $trickRepository, $id, PaginatorInterface $paginator)
    {
        $trick = $trickRepository->find($id);
        $comments = $trick->getComments();
        $paginations = $paginator->paginate(
            $comments, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            1 /* limit per page */
        );
        return $this->render('trick/comments.html.twig', [
            'trick' => $trick,
            'paginations' => $paginations
        ]);
    }
}
