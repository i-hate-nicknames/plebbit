<?php

namespace App\Controller;

use App\Entity\District;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $repository = $this->getDoctrine()->getRepository(District::class);
        $district = $repository->getByName($name);
        if (null === $district) {
            return $this->createNotFoundException('District not found my friend');
        }
        return $this->render('district/posts.html.twig', [
            'district' => $district,
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
}
