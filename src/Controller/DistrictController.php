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
     * @Route("/district/{id}", name="district")
     * @param District $district
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function posts(District $district)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        return $this->render('district/posts.html.twig', [
            'district' => $district,
        ]);
    }

    /**
     * @Route("/district/{id}/details", name="districtDetails")
     * @param District $district
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function info(District $district)
    {
        return $this->render('district/details.html.twig', [
            'district' => $district,
        ]);
    }
}
