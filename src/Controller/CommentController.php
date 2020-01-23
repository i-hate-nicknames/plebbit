<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Post;
use App\Entity\User;
use App\Forms\CommentType;
use App\Forms\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function var_export;

class CommentController extends AbstractController
{

    //TODO: remove GET deletion, implement DELETE method on frontend
    /**
     * @Route("/comment/{id}", name="deleteComment", methods={"GET"})
     * @param Comment $comment
     * @return RedirectResponse
     */
    public function deleteComment(Comment $comment)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $this->denyAccessUnlessGranted('delete', $comment, 'You are not allowed to delete this comment!');
        $comment->setIsDeleted(true);
        $entityManager->persist($comment);
        $entityManager->flush();
        return $this->redirectToRoute('posts');
    }
}
