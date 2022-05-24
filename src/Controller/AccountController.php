<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class AccountController extends AbstractController
{

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManagerInterface)
    {
        $this->entityManager = $entityManagerInterface;
    }
    /**
     * @Route("/account", name="app_user_account")
     */
    public function index(): Response
    {
        $user = $this->getUser();
        return $this->render('account/index.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * @Route("/account/password/change", name="app_user_change_password")
     */
    public function changePassword(Request $request, UserPasswordHasherInterface $encoder)
    {
        //changement du mot de passe
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {

            $old_pwd = $form->get('old_password')->getData();
            if ($encoder->isPasswordValid($user, $old_pwd)) {

                $new_pwd = $form->get('new_password')->getData();
                $user->setPassword($encoder->hashPassword($user, $new_pwd));

                $this->entityManager->persist($user);
                $this->entityManager->flush();
                $this->addFlash('success', 'password changed seccessfully!!');
            }
        }
        return $this->render('account/password.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
