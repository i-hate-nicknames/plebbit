<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Post;
use App\Entity\User;
use App\Forms\CommentType;
use App\Forms\CreateDistrictType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DistrictController extends AbstractController
{
    /**
     * @Route("/districts", name="districtList")
     */
    public function index()
    {
        $repository = $this->getDoctrine()->getRepository(District::class);
        return $this->render('district/list.html.twig', [
            'districts' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/d/{name}", name="district")
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function posts(string $name)
    {
        $districtRepository = $this->getDoctrine()->getRepository(District::class);
        $district = $districtRepository->getByName($name);
        if (null === $district) {
            return $this->createNotFoundException('District not found my friend');
        }
        $doctrine = $this->getDoctrine();
        $postRepository = $doctrine->getRepository(Post::class);
        $postsWithStats = $postRepository->getPostsListing($this->getUser(), [$district->getId()]);
        return $this->render('district/posts.html.twig', [
            'district' => $district,
            'data' => $postsWithStats
        ]);
    }

    /**
     * @Route("/d/{name}/details", name="districtDetails")
     * @param string $name
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function info(string $name)
    {
        $repository = $this->getDoctrine()->getRepository(District::class);
        $district = $repository->getByName($name);
        if (null === $district) {
            return $this->createNotFoundException('District not found my friend');
        }
        return $this->render('district/details.html.twig', [
            'district' => $district,
        ]);
    }

    /**
     * @Route("/createDistrict", name="createDistrict")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createDistrict(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $district = new District();
        $district->setOwner($user);
        $form = $this->createForm(CreateDistrictType::class, $district);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $district = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($district);
            $entityManager->flush();
            return $this->redirectToRoute('district', ['name' => $district->getName()]);
        }
        return $this->render('district/create.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
