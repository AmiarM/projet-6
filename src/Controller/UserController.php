<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


/**
 * @Route("/user")
 */
class UserController extends AbstractController
{

    private $manager;
    private $userRepository;

    public function __construct(EntityManagerInterface $manager, UserRepository $userRepository)
    {
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/admin/users", name="app_users")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $users = $this->userRepository->findAll();
        $paginations = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('user/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    // 

    /**
     * @Route("/admin/user/edit/{id}", name="app_user_edit")
     */
    public function edit(Request $request, $id, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("le user d'id $id n'existe pas");
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $user->setToken(md5(uniqid(10)));
            $this->manager->persist($user);
            $this->manager->flush();
            $this->addFlash('success', 'User edited successfully');
            return $this->redirectToRoute('app_users');
        }
        return $this->render('user/add.html.twig', [
            'user' => $user,
            'action' => 'Edit User',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="app_user_delete")
     */
    public function delete($id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("le user d'id $id n'existe pas");
        }
        $this->manager->remove($user);
        $this->manager->flush();
        $this->addFlash('success', 'User deleted successfully');
        return $this->redirectToRoute("app_users");
    }

    /**
     * @Route("/admin/user/show/{id}", name="app_user_show")
     */
    public function show($id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("User innexistant");
        }
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }


    /**
     * @Route("/admin/user/activate/{id}", name="app_user_activate")
     */
    public function activateUser(User $user): Response
    {
        if (!$user) {
            throw new NotFoundHttpException("User innexistant");
        }
        if ($user->getActivated() == 1) {
            $this->addFlash('error', 'User already Activated!');
        } else {
            $user->setActivated(1);
            $this->addFlash('success', 'user successfully activated!');
        }
        $this->manager->flush();
        return $this->redirectToRoute('app_users');
        return $this->render('user/show.html.twig', [
            'user' => $user
        ]);
    }
}
