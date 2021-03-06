<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Image;
use App\Service\Mailer;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;




/**
 * @Route("/register")
 */
class RegisterController extends AbstractController
{

    /**
     * @Route("/", name="app_user_register")
     */
    public function index(Mailer $mailer, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        //authentification
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $email_search = $em->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if (!$email_search) {
                $password = $user->getPassword();
                $user->setToken(md5(uniqid(10)));
                $user->setPassword($userPasswordHasher->hashPassword($user, $password));
                //on récupére les images transmises
                $image = $form->get('avatar')->getData();
                if ($image) {
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
                    $user->setAvatar('/uploads/' . $fichier);
                }
                $em->persist($user);
                $em->flush();
                $this->addFlash('success', 'Utilisateur ajouté avec succès! un email vous est été envoyé pour activer votre compte');
                //dd($user->getEmail());
                //envoi de mail
                $mailer->sendMail(
                    $user->getEmail(),
                    "valider votre inscription",
                    "pour valider votre compte veuillez utiliser l'url suivante:<br>
                    http://localhost:8000/register/confirm/" . $user->getToken(),
                    "confirmer votre inscription",
                    "/login"
                );
                $this->addFlash('success', 'Message envoyé avec succès');
                return $this->redirectToRoute('app_user_login');
            } else {
                $this->addFlash('danger', 'L\'email que vous avez renseigné existe déjà');
                $this->redirectToRoute('app_user_register');
            }
        }
        return $this->render('register/index.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/confirm/{token}", name="user_register_confirm")
     */
    public function confirm($token, Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        //on verifie si un utilisateur à ce token
        $user = $userRepository->findOneBy(['token' => $token]);
        if (!$user) {
            throw new NotFoundHttpException('Utilisateur Innéxistant');
        }
        //on supprime le token 
        $user->setToken('NULL');
        $user->setActivated(1);
        $em->persist($user);
        $em->flush();
        //on envoie un message flash
        $this->addFlash('success', 'utilisateur activé');
        return $this->redirectToRoute('app_user_login');
    }
}
