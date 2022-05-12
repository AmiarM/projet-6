<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Form\CommentType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\EnvNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    private $entityManagerInterface;
    private $trickRepository;

    public function __construct(EntityManagerInterface $entityManagerInterface, TrickRepository $trickRepository)
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->trickRepository = $trickRepository;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(): Response
    {
        $tricks = $this->trickRepository->findAll();
        return $this->render('home/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/trick/{id}-{slug}", name="trick_show")
     */
    public function show($id, Request $request): Response
    {
        $trick = $this->trickRepository->find($id);
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException("user not found");
        }
        //on créer le commentaire vièrge
        $comment = new Comment();
        //on génére le formulaire
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setTrick($trick);
            $comment->setUser($user);
            $this->entityManagerInterface->persist($comment);
            $this->entityManagerInterface->flush();

            $this->addFlash('message', 'Votre commentaire a bien été envoyé');
            return $this->redirectToRoute(
                'trick_show',
                [
                    'id' => $trick->getId(),
                    'slug' => $trick->getSlug()
                ]
            );
        }
        return $this->render('home/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView()
        ]);
    }
}
