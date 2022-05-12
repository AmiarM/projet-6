<?php

namespace App\Controller\Admin;

use App\Entity\Trick;
use App\Repository\CategorieRepository;
use App\Repository\CommentRepository;
use App\Repository\TrickRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{

    private $categorieRepository;
    private $userRepository;
    private $trickRepository;
    private $commentRepository;

    public function __construct(
        CategorieRepository $categorieRepository,
        UserRepository $userRepository,
        TrickRepository $trickRepository,
        CommentRepository $commentRepository
    ) {
        $this->categorieRepository = $categorieRepository;
        $this->userRepository = $userRepository;
        $this->trickRepository = $trickRepository;
        $this->commentRepository = $commentRepository;
    }

    /**
     * @Route("/admin", name="app_admin")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        $categoriesCount = $this->categorieRepository->countAllCategories();
        $usersCount = $this->userRepository->countAllUsers();
        $tricksCount = $this->trickRepository->countAllTricks();
        $commentsCount = $this->commentRepository->countAllComments();
        return $this->render('admin/index.html.twig', [
            'categoriesCount' => $categoriesCount,
            'usersCount' => $usersCount,
            'user' => $user,
            'tricksCount' => $tricksCount,
            'commentsCount' => $commentsCount
        ]);
    }
}
