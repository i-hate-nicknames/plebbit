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
        // todo: delete this. Posts should be accessed through districts or
        // from homepage which combines posts from all subscriptions
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Post::class);
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll()
        ]);
    }

    // todo: add submit post method

    /**
     * @Route("/post/{id}", name="post", methods={"GET", "POST"})
     * @return Response
     */
    public function post(Request $request, Post $post)
    {
        if ($request->getMethod() === Request::METHOD_POST) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        }
        $repository = $this->getDoctrine()->getRepository(Comment::class);
        $commentTree = $repository->fetchTree($post->getId());
        $post->setComments($commentTree);
        /** @var User $user */
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setAuthor($user);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('post', ['id' => $post->getId()]);
        }
        return $this->render('post/post.html.twig', [
            'post' => $post,
            'comment_form' => $form->createView()
        ]);
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
        $redirect = $this->handlePostForm($request, $post, $form);
        if (null !== $redirect) {
            return $redirect;
        }
        return $this->render('post/edit.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/posts/submit", name="submitPost")
     * @param Request $request
     * @return RedirectResponse|Response|null
     */
    public function submitPost(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var User $user */
        $user = $this->getUser();
        $post = new Post();
        $post->setAuthor($user);
        $districtRepository = $this->getDoctrine()->getRepository(District::class);
        // todo: use current district when this method is moved to district controller
        $district = $districtRepository->findOneBy(['name' => 'general']);
        $post->setDistrict($district);
        $form = $this->createForm(PostType::class, $post);
        $redirect = $this->handlePostForm($request, $post, $form);
        if (null !== $redirect) {
            return $redirect;
        }
        return $this->render('post/edit.html.twig', [
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
        $this->denyAccessUnlessGranted('delete', $post, 'You are not allowed to delete this post!');
        $manager->remove($post);
        $manager->flush();
        return $this->redirectToRoute('posts');
    }

    private function handlePostForm(Request $request, Post $post, FormInterface $form): ?RedirectResponse
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('post',['id' => $post->getId()]);
        }
        return null;
    }
}
