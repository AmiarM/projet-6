<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Image;
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

class AdminUserController extends AbstractController
{

    private $entityManagerInterface;
    private $userRepository;

    public function __construct(EntityManagerInterface $entityManagerInterface, UserRepository $userRepository)
    {
        $this->entityManagerInterface = $entityManagerInterface;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/admin/users", name="admin_users")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $users = $this->userRepository->findAll();
        $paginations = $paginator->paginate(
            $users, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('admin/user/index.html.twig', [
            'paginations' => $paginations
        ]);
    }

    // 

    /**
     * @Route("/admin/user/edit/{id}", name="admin_user_edit")
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
            // $password = $user->getPassword();
            //$user->setPassword($userPasswordHasher->hashPassword($user, $password));
            //on récupére les images transmises
            //$image = $form->get('avatar')->getData();
            // //on genere le nouveau nom du fichier
            // $fichier = md5(uniqid()) . "." . $image->guessExtension();
            // //on copie le fichier dans le dossier upload
            // $image->move(
            //     $this->getParameter('images_directory'),
            //     $fichier
            // );
            // //on stock l'image dans la base de données 
            // $image = new Image();
            // $image->setName($fichier);
            // $user->setAvatar($fichier);
            $this->entityManagerInterface->persist($user);
            $this->entityManagerInterface->flush();
            return $this->redirectToRoute('admin_users');
        }
        return $this->render('admin/user/add.html.twig', [
            'user' => $user,
            'action' => 'Edit User',
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="admin_user_delete")
     */
    public function delete($id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("le user d'id $id n'existe pas");
        }
        $this->entityManagerInterface->remove($user);
        $this->entityManagerInterface->flush();
        return $this->redirectToRoute("admin_users");
    }

    /**
     * @Route("/admin/user/show/{id}", name="admin_user_show")
     */
    public function show($id): Response
    {
        $user = $this->userRepository->find($id);
        if (!$user) {
            throw new NotFoundHttpException("User innexistant");
        }
        return $this->render('admin/user/show.html.twig', [
            'user' => $user
        ]);
    }


    /**
     * @Route("/admin/user/activate/{id}", name="admin_user_activate")
     */
    public function activateUser(User $user): Response
    {
        if (!$user) {
            throw new NotFoundHttpException("User innexistant");
        }
        $user->setActivated(1);
        $this->entityManagerInterface->flush();
        $this->addFlash('success', 'user successfully activated!');
        return $this->redirectToRoute('admin_users');
        return $this->render('admin/user/show.html.twig', [
            'user' => $user
        ]);
    }
}
