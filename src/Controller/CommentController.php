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

    /**
     * @Route("/post/{id}/{parentId}", name="addComment", methods={"POST"})
     * @return Response
     */
    public function addComment(Request $request, Post $post, int $parentId)
    {
        // todo: consider moving this shit to a common place
        // I think trait?
        /** @var User $user */
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setAuthor($user);
        if ($parentId != 0) {
            $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
            $parent = $commentRepository->find($parentId);
            if (null === $parent) {
                $this->createNotFoundException("Parent comment is not found");
            }
            $parent->addChild($comment);
        }
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $post->addComment($comment);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('post', ['id' => $post->getId()]);
        }
        return $this->render('post/post.html.twig', [
            'post' => $post,
            'comment_form' => $form->createView()
        ]);
    }
}
