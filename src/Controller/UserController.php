<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user/{id}", name="user")
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(int $id)
    {
        $repository = $this->getDoctrine()->getRepository(User::class);
        $user = $repository->find($id);
        if (!$user) {
            throw new NotFoundHttpException('User doesn\'t exist!');
        }
        return $this->render('user/index.html.twig', [
            'user' => $user,
        ]);
    }
}
