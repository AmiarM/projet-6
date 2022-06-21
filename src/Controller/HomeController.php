<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class HomeController extends AbstractController
{

    private $manager;
    private $trickRepository;

    public function __construct(EntityManagerInterface $manager, TrickRepository $trickRepository)
    {
        $this->manager = $manager;
        $this->trickRepository = $trickRepository;
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function index(): Response
    {
        $tricks = $this->trickRepository->findBy([], ['createdAt' => 'desc']);
        return $this->render('home/index.html.twig', [
            'tricks' => $tricks
        ]);
    }

    /**
     * @Route("/trick/show/{id}/{slug}", name="app_home_show")
     */
    public function show($id, Request $request, PaginatorInterface $paginator): Response
    {
        $trick = $this->trickRepository->find($id);
        if (!$trick) {
            throw new NotFoundHttpException('Trick innéxistant');
        }
        $comments = $trick->getComments();
        $paginations = $paginator->paginate(
            $comments, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            3 /* limit per page */
        );
        $user = $this->getUser();
        //on créer le commentaire vièrge
        $comment = new Comment();
        //on génére le formulaire
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setTrick($trick);
            $comment->setUser($user);
            $this->manager->persist($comment);
            $this->manager->flush();

            $this->addFlash('message', 'your comment is posted!');
            return $this->redirectToRoute(
                'app_home_show',
                [
                    'id' => $trick->getId(),
                    'slug' => $trick->getSlug()
                ]
            );
        }
        return $this->render('home/show.html.twig', [
            'trick' => $trick,
            'form' => $form->createView(),
            'paginations' => $paginations
        ]);
    }
}
