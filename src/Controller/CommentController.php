<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/comment")
 */
class CommentController extends AbstractController
{


    /**
     * @Route("/new", name="app_comment_new", methods={"GET", "POST"})
     */
    public function new(Request $request, CommentRepository $commentRepository): Response
    {
        $comment = new Comment();
        $user = $this->getUser();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $comment->setUser($user);
            $commentRepository->add($comment);
            $this->addFlash('success', 'commentaire ajouté avec  succès!');
            return $this->redirectToRoute('app_comment_lists', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'action' => 'Ajouter'
        ]);
    }

    /**
     * @Route("/{id}/show", name="app_comment_show", methods={"GET"})
     */
    public function show(Comment $comment): Response
    {
        return $this->render('comment/show.html.twig', [
            'comment' => $comment,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_comment_edit", methods={"POST","GET"})
     */
    public function edit(EntityManagerInterface $manager, Comment $comment, Request $request): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $manager->flush();
            $this->addFlash('success', 'commentaire modifié avec succès');
            return $this->redirectToRoute('app_comment_lists');
        }
        return $this->render('comment/edit.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'action' => 'Editer'
        ]);
    }
    /**
     * @Route("/{id}/delete", name="app_comment_delete",methods={"POST"})
     */
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment);
            $this->addFlash('error', 'commentaire  supprimé avec  succès');
        }

        return $this->redirectToRoute('app_comment_lists', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/personal/lists", name="app_comment_lists", methods={"GET"})
     */
    public function myComments(CommentRepository $commentRepository, Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw new NotFoundHttpException("Utilisateur innexistant");
        }
        $comments = $commentRepository->findBy([
            'user' => $user
        ]);
        $paginations = $paginator->paginate(
            $comments, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            10 /* limit per page */
        );
        return $this->render('comment/mycomments.html.twig', [
            'paginations' => $paginations
        ]);
    }
}
