<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Form\RegisterType;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Component\Mime\Email;
use PHPMailer\PHPMailer\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



class RegisterController extends AbstractController
{

    /**
     * @Route("/register", name="user_register")
     */
    public function index(MailerInterface $mailer, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $password = $user->getPassword();
            $user->setToken(md5(random_bytes(10)));
            $user->setPassword($userPasswordHasher->hashPassword($user, $password));
            //on récupére les images transmises
            $image = $form->get('avatar')->getData();
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
            $user->setAvatar($fichier);
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', 'User Created!');
            //dd($user->getEmail());
            //envoi de mail
            $email = (new Email())
                ->from('mohamed.amiar@gmail.com')
                ->to($user->getEmail())
                ->subject('Valider votre Inscription')
                ->text('pour valider votre compte veuillez utiliser l\'url suivante:')
                ->html('https://127.0.0.1:8000/register/confirm/' . $user->getId() . "/" . $user->getToken());
            $mailer->send($email);
            $this->addFlash('message', 'Message envoyé avec succès');
            return $this->redirectToRoute('user_login');
        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/register/confirm/{id}/{token}", name="user_register_confirm")
     */
    public function confirm(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException('User Innéxistant');
        }
        $user->setActivated(1);
        $this->entityManager->flush();
        return $this->redirectToRoute('user_login');
    }
}
