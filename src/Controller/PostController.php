<?php

namespace App\Controller;

use App\Entity\Post;
use App\Forms\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function home()
    {
        return new Response('feels like home :DDDDDDD');
    }
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
     * @Route("/post/{id}", name="post", methods={"GET"})
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
        $repository = $this->getDoctrine()->getManager()->getRepository(Post::class);
        $post = $repository->find($id);
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
     * @Route("/post/{id}", name="deletePost", methods={"DELETE"})
     */
    public function deletePost(int $id)
    {
        $manager = $this->getDoctrine()->getManager();
        $post = $manager->getRepository(Post::class)->find($id);
        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }
        $manager->remove($post);
        $manager->flush();
        return $this->redirectToRoute('posts');
    }
}
