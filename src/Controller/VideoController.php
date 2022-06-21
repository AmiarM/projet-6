<?php

namespace App\Controller;

use App\Entity\Video;
use App\Form\VideoType;
use App\Form\Video1Type;
use App\Repository\VideoRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/video")
 */
class VideoController extends AbstractController
{
    /**
     * @Route("/", name="app_video_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator, VideoRepository $videoRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException('user innéxistant');
        }
        $tricks = $user->getTricks();
        foreach ($tricks as $trick) {
            $videos = $videoRepository->findBy([
                'trick' => $trick
            ]);
            $paginations = $paginator->paginate(
                $videos, /* query NOT result */
                $request->query->getInt('page', 1), /* page number */
                10 /* limit per page */
            );
        }
        return $this->render('video/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    /**
     * @Route("/new", name="app_video_new", methods={"GET", "POST"})
     */
    public function new(Request $request, VideoRepository $videoRepository): Response
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->get('trick')->getData();
            $video->setTrick($trick);
            $videoRepository->add($video, true);
            $this->addFlash('success', 'video ajouté avec succès');
            return $this->redirectToRoute('app_video_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('video/new.html.twig', [
            'video' => $video,
            'form' => $form,
            'action' => 'Ajouter une video'
        ]);
    }

    /**
     * @Route("/{id}", name="app_video_show", methods={"GET"})
     */
    public function show(Video $video): Response
    {
        return $this->render('video/show.html.twig', [
            'video' => $video,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_video_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Video $video, VideoRepository $videoRepository): Response
    {
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $trick = $form->get('trick')->getData();
            $video->setTrick($trick);
            $videoRepository->add($video, true);
            $this->addFlash('success', 'Video modifié avec succès');
            return $this->redirectToRoute('app_video_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('video/edit.html.twig', [
            'video' => $video,
            'form' => $form,
            'action' => 'Editer une Video'
        ]);
    }

    /**
     * @Route("/{id}", name="app_video_delete", methods={"POST"})
     */
    public function delete(Request $request, Video $video, VideoRepository $videoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {
            $videoRepository->remove($video, true);
            $this->addFlash('success', 'video supprimé avec succès');
        }

        return $this->redirectToRoute('app_video_index', [], Response::HTTP_SEE_OTHER);
    }
}
