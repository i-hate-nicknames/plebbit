<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Post;
use App\Entity\PostVote;
use App\Entity\User;
use App\Forms\CommentType;
use App\Forms\PostType;
use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function count;
use function json_decode;
use function sleep;
use function sprintf;
use function var_export;

class PostController extends AbstractController
{

    /**
     * @Route("/", name="home")
     * @return Response
     */
    public function home()
    {
        return new RedirectResponse($this->generateUrl('posts'));
    }

    // todo: add homepage: top posts from all subs for unauthenticated users
    // top posts from subscribed districts for authenticated

    /**
     * @Route("/post/{id}", name="post", methods={"GET"})
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function post(Request $request, int $id)
    {
        $postRepository = $this->getDoctrine()->getRepository(Post::class);
        $postData = $postRepository->getSinglePost($this->getUser(), $id);
        $post = $postData['post'];
        $commentRepository = $this->getDoctrine()->getRepository(Comment::class);
        $commentTree = $commentRepository->fetchTree($post->getId());
        $post->setComments($commentTree);
        /** @var User $user */
        $user = $this->getUser();
        $comment = new Comment();
        $comment->setAuthor($user);
        $form = $this->createForm(CommentType::class, $comment);
        return $this->render('post/post.html.twig', [
            'postData' => $postData,
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
        $this->denyAccessUnlessGranted('edit', $post);
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

    // todo: implement via API platform
    /**
     * @Route("/post/{id}/votes", name="votePost", methods={"POST"})
     */
    public function vote(Post $post, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $manager = $this->getDoctrine()->getManager();
        $voteRepository = $manager->getRepository(PostVote::class);
        $postRepository = $manager->getRepository(Post::class);
        /** @var User $user */
        $user = $this->getUser();
        $existingVote = $voteRepository->findByUserAndPost($user, $post);
        $content = json_decode($request->getContent(), true);
        $value = $content['value'] ?? 0;
        if ($value != 1 && $value != -1) {
            return new JsonResponse([
                'error' => sprintf('Invalid vote value: %d', $value)
            ], 400);
        }
        if ($existingVote !== null) {
            if ($existingVote->getValue() === -1) {
                $postRepository->decDownvotes($post->getId());
            } else {
                $postRepository->decUpvotes($post->getId());
            }
            if ($value === $existingVote->getValue()) {
                $manager->flush();
                return new JsonResponse([], 204);
            }
        }
        $vote = new PostVote();
        $vote->setPost($post)
            ->setUser($this->getUser())
            ->setValue($value)
            ->setCreatedAt(new DateTime('now', new DateTimeZone('UTC')));

        try {
            if ($vote->getValue() === 1) {
                $postRepository->incUpvotes($post->getId());
            } else {
                $postRepository->incDownvotes($post->getId());
            }
            $manager->persist($vote);
            $manager->flush();
        } catch (UniqueConstraintViolationException $exception) {
            return new JsonResponse([
                'error' => 'You can only vote once'
            ], 400);
        }
        return new JsonResponse([], 204);
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
