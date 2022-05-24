<?php

namespace App\Controller;

use App\Service\Mailer;
use App\Form\ResetPassType;
use App\Repository\UserRepository;
use PHPMailer\PHPMailer\PHPMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="app_user_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_user_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/forgotten-password", name="app_user_forgotten_password")
     */
    public function forgottenPass(EntityManagerInterface $em, Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGeneratorInterface, Mailer $mailer): Response
    {
        //on créer le formulaire
        $form = $this->createForm(ResetPassType::class);
        //on fait le traitement
        $form->handleRequest($request);
        //si le formulaire est  valide
        if ($form->isSubmitted()) {
            //onrecupère les données
            $donnees = $form->getData();

            $email = $donnees->getEmail();
            //on cherche si l'utilisateur a cet email
            $user = $userRepository->findOneBy(['email' => $email]);
            //si l'utilisateur n'existe pas
            if (!$user) {
                //onenvoie un message
                $this->addFlash('danger', 'user not found');
                return $this->redirectToRoute('user_login');
            }
            //on genere un token
            $token = $tokenGeneratorInterface->generateToken();
            try {
                $user->setResetToken($token);
                //$user->setActivated(1);
                $em->persist($user);
                $em->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', 'a mistake has occurred' . $e->getMessage());
                return $this->redirectToRoute("user_login");
            }
            //on gènere l'url de réinitialisation du mdp
            $url = $this->generateUrl("app_reset_password", ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
            $mailer->sendMail(
                $user->getEmail(),
                "Reset Password",
                "
                 Bonjour,<br><br>Une demande de réinitialisation de mot de passe a été effectuée pour le site snowtricks.fr. Veuillez cliquer sur le lien suivant : " . $url,
                "changer votre mot de passe",
                "/login"
            );
            // On crée le message flash de confirmation
            $this->addFlash('success', 'E-mail de réinitialisation du mot de passe envoyé !');

            // On redirige vers la page de login
            return $this->redirectToRoute('app_user_login');
        }
        return $this->render('security/forgotten_password.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/reset-pass/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordHasherInterface $passwordEncoder, UserRepository $userRepository, EntityManagerInterface $em)
    {
        // On cherche un utilisateur avec le token donné
        $user = $userRepository->findOneBy(['reset_token' => $token]);
        // Si l'utilisateur n'existe pas
        if ($user === null) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('app_user_login');
        }

        // Si le formulaire est envoyé en méthode post
        if ($request->isMethod('POST')) {
            // On supprime le token
            $user->setResetToken(null);

            // On chiffre le mot de passe
            $user->setPassword($passwordEncoder->hashPassword($user, $request->request->get('password')));

            // On stocke
            $em->persist($user);
            $em->flush();

            // On crée le message flash
            $this->addFlash('message', 'Mot de passe mis à jour');

            // On redirige vers la page de connexion
            return $this->redirectToRoute('app_user_login');
        } else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }
    }
}
