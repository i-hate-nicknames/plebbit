<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\District;
use App\Entity\Post;
use App\Entity\Subscription;
use App\Entity\User;
use App\Forms\CommentType;
use App\Forms\CreateDistrictType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        $districtsData = $repository->findAllWithSubscribeStatus($this->getUser());
        return $this->render('district/list.html.twig', [
            'districtsData' => $districtsData
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
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
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

    /**
     * @Route("/subscribe/{id}", name="districtSubscribe")
     * @param District $district
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function subscribe(District $district)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $districtRepository = $entityManager->getRepository(District::class);
        $user = $this->getUser();
        $subscription = new Subscription();
        $subscription->setUser($user)
            ->setDistrict($district);
        $entityManager->beginTransaction();
        try {
            $districtRepository->updateNumSubscribers($district->getId(), 1);
            $entityManager->persist($subscription);
            $entityManager->flush();
            $entityManager->commit();
        } catch(UniqueConstraintViolationException $ex) {
            $entityManager->rollback();
            return new JsonResponse(['message' => 'already subscribed'], 404);
        }
        return new JsonResponse(['message' => 'success']);
    }

    /**
     * @Route("/unsubscribe/{id}", name="districtUnsubscribe")
     * @param District $district
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unsubscribe(District $district)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $districtRepository = $entityManager->getRepository(District::class);
        $subscriptionRepository = $entityManager->getRepository(Subscription::class);
        $user = $this->getUser();
        $subscription = $subscriptionRepository->findByUserAndDistrict($user, $district);
        if (!$subscription) {
            return new JsonResponse(['message' => 'not subscribed'], 404);
        }
        $entityManager->beginTransaction();
        try {
            $districtRepository->updateNumSubscribers($district->getId(), -1);
            $entityManager->remove($subscription);
            $entityManager->flush();
            $entityManager->commit();
        } catch(UniqueConstraintViolationException $ex) {
            $entityManager->rollback();
            return new JsonResponse(['message' => 'not subscribed to begin with'], 404);
        }
        return new JsonResponse(['message' => 'success']);
    }

}
