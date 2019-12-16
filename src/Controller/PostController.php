<?php

namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="post")
     */
    public function index()
    {
        $doctrine = $this->getDoctrine();
        $repo = $doctrine->getRepository(Post::class);
        return $this->render('post/index.html.twig', [
            'posts' => $repo->findAll(),
        ]);
    }
}
