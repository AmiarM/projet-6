<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Form\TrickType;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/trick")
 */
class TrickController extends AbstractController
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }
    /**
     * @Route("/personal/tricks",name="app_tricks_lists")
     *
     * @return void
     */
    public function tricks(TrickRepository $trickRepository, PaginatorInterface $paginator, Request $request)
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException('utilisateur innéxistant');
        }
        $tricks = $trickRepository->findBy([
            'user' => $user
        ]);
        $paginations = $paginator->paginate(
            $tricks, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('trick/mytricks.html.twig', [
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/new", name="app_trick_new",methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $trick = new Trick();
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
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
                $image->setName('/uploads/' . $fichier);
                $trick->addImage($image);
            }
            //on récupére les images transmises
            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }
            $trick->setUser($user);
            $this->manager->persist($trick);
            $this->manager->flush();
            $this->addFlash('success', 'Trick ajouté avec succès!');
            return $this->redirectToRoute('app_tricks_lists');
        }

        return $this->renderForm('trick/new.html.twig', [
            'trick' => $trick,
            'form' => $form,
            'action' => 'Ajouter un Trick'
        ]);
    }

    /**
     * @Route("/{id}", name="app_trick_show", methods={"GET"})
     */
    public function show(Trick $trick, PaginatorInterface $paginator, TrickRepository $trickRepository, Request $request): Response
    {
        if (!$trick) {
            throw new NotFoundHttpException('Trick  n\'existe pas');
        }
        $comments = $trick->getComments();
        $paginations = $paginator->paginate(
            $comments, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
        return $this->render('trick/show.html.twig', [
            'trick' => $trick,
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_trick_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_user_login');
        }
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
            $videos = $form->get('videos')->getData();
            foreach ($videos as $video) {
                $trick->addVideo($video);
            }
            $trick->setUser($user);
            $trickRepository->add($trick);
            $this->addFlash('success', 'Trick edité avec succès');
            return $this->redirectToRoute('app_tricks_lists');
        }

        return $this->render('trick/edit.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'action' => 'Editer un Trick'
        ]);
    }


    /**
     * @Route("/image/{id}", name="app_trick_image_delete", methods={"DELETE"})
     */
    public function deleteImage(Image $image, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {
            // On récupère le nom de l'image
            $nom = $image->getName();
            // On supprime le fichier
            unlink($this->getParameter('images_directory') . '/' . $nom);

            // On supprime l'entrée de la base
            $this->manager->remove($image);
            $this->manager->flush();
            $this->addFlash('success', 'trick supprimé avec succès');
            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
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
            4 /* limit per page */
        );
        return $this->render('trick/comments.html.twig', [
            'trick' => $trick,
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/video/delete/{id}", name="app_trick_video_delete", methods={"POST"})
     */
    public function deleteVideo(Request $request, Video $video): Response
    {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $data['_token'])) {
            $this->manager->remove($video);
            $this->manager->flush();

            // On répond en json
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }



    /**
     * @Route("/delete/{id}", name="app_trick_delete", methods={"DELETE","POST"})
     */
    public function delete(Request $request, Trick $trick, TrickRepository $trickRepository): Response
    {
        if (!$trick) {
            throw new NotFoundHttpException('Trick Innexistant');
        }
        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {
            $trickRepository->remove($trick);
            $this->addFlash('success', 'Trick supprimé avec succés');
        }
        return $this->redirectToRoute('app_tricks_lists', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/image/promote/{id}", name="app_trick_image_promote", methods={"GET"})
     */
    public function promote(Image $image, EntityManagerInterface $em)
    {
        if (!$image) {
            throw new NotFoundHttpException('Image n\'existe pas');
        }
        if ($image->getIsFirst() == 1) {
            $image->setIsFirst(0);
        } else {
            $image->setIsFirst(1);
        }

        $em->flush();
        $this->addFlash('success', 'status changé avec succès');
        return $this->redirectToRoute('app_image_show', [
            'id' => $image->getId()
        ]);
    }

    /**
     *
     * @Route("/images/{id}",name="app_trick_images")
     */
    public function getImages(Request $request, PaginatorInterface $paginator, Trick $trick, ImageRepository $imageRepository)
    {
        if (!$trick) {
            throw new NotFoundHttpException("trick innéxistant");
        }
        $images = $imageRepository->findBy([
            'trick' => $trick
        ]);
        $paginations = $paginator->paginate(
            $images, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
        return $this->render('image/index.html.twig', [
            'paginations' => $paginations
        ]);
    }
    /**
     *
     * @Route("/videos/{id}",name="app_trick_videos")
     */
    public function getVideos(Request $request, PaginatorInterface $paginator, Trick $trick, VideoRepository $videoRepository)
    {
        if (!$trick) {
            throw new NotFoundHttpException("trick innéxistant");
        }
        $videos = $videoRepository->findBy([
            'trick' => $trick
        ]);
        $paginations = $paginator->paginate(
            $videos, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
        return $this->render('video/index.html.twig', [
            'paginations' => $paginations
        ]);
    }
}
