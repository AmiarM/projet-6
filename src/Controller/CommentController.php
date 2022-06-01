<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Form\CommentType;
use App\Repository\CommentRepository;
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
     * @Route("/", name="app_comment_index", methods={"GET"})
     */
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'comments' => $commentRepository->findAll(),
        ]);
    }

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
            $this->addFlash('success', 'comment successfully added!');
            return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form,
            'action' => 'New Comment'
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

    // /**
    //  * @Route("/{id}/edit", name="app_comment_edit", methods={"GET", "POST"})
    //  */
    // public function edit(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    // {
    //     $form = $this->createForm(CommentType::class, $comment);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $commentRepository->add($comment);
    //         return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    //     }

    //     return $this->renderForm('comment/edit.html.twig', [
    //         'comment' => $comment,
    //         'form' => $form,
    //         'action' => 'Edit Categorie'
    //     ]);
    // }

    /**
     * @Route("/{id}/delete", name="app_comment_delete",methods={"POST"})
     */
    public function delete(Request $request, Comment $comment, CommentRepository $commentRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $comment->getId(), $request->request->get('_token'))) {
            $commentRepository->remove($comment);
            $this->addFlash('error', 'comment  deleted successfully');
        }

        return $this->redirectToRoute('app_comment_index', [], Response::HTTP_SEE_OTHER);
    }
    /**
     * @Route("/{id}/comment/activated", name="app_comment_activated")
     */
    public function published(CommentRepository $commentRepository, $id)
    {
        $comment = $commentRepository->find($id);
        if (!$comment) {
            throw new NotFoundHttpException('comment not found');
        }
        if ($comment->getActivated() == 1) {
            $this->addFlash('error', 'comment already activated');
        } else {
            $comment->setActivated(1);
            $this->addFlash('success', 'comment activated successfully');
        }
        $commentRepository->add($comment);
        return $this->redirectToRoute('app_comment_index');
        return $this->render('trick/comments.html.twig', [
            'comment' => $comment
        ]);
    }

    /**
     * @Route("/personal/lists", name="app_comment_lists", methods={"GET"})
     */
    public function myComments(): Response
    {
        return $this->render('comment/mycomments.html.twig');
    }
}
