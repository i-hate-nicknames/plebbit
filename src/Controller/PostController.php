<?php

namespace App\Controller;

use App\Entity\Post;
use App\Forms\PostType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/posts", name="posts")
     */
    public function posts(Request $request)
    {
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Post::class);
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll()
        ]);
    }

    /**
     * @Route("/post/{id}", name="post")
     * @param Post $post
     * @return Response
     */
    public function post(Post $post)
    {
        return $this->render('post/post.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/post/{id}/edit", name="editPost")
     * @param int $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editPost(int $id, Request $request)
    {
        $manager = $this->getDoctrine()->getManager()->getRepository(Post::class);
        $post = $manager->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('post',['id' => $post->getId()]);
        }
        return $this->render('post/edit_post.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/{id}/delete", name="deletePost")
     */
    public function deletePost(int $id)
    {

    }
}
