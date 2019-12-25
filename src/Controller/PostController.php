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
        $newPost = new Post();
        $form = $this->createForm(PostType::class, $newPost);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('posts');
        }

        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Post::class);
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll(),
            'form' => $form->createView()
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
     */
    public function editPost(int $id)
    {

    }

    /**
     * @Route("/post/{id}/delete", name="deletePost")
     */
    public function deletePost(int $id)
    {

    }
}
