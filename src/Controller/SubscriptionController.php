<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionController extends AbstractFOSRestController
{
    private $subscriptionRepository;
    private $em;

    /**
     * SubscriptionController constructor.
     * @param $subscriptionRepository
     * @param $em
     */
    public function __construct(SubscriptionRepository $subscriptionRepository, EntityManagerInterface $em)
    {
        $this->subscriptionRepository = $subscriptionRepository;
        $this->em = $em;
    }

    /**
     * @Rest\View(serializerGroups={"subscription"})
     * @Rest\Get("/api/anonymous/subscriptions/{name}")
     * @param subscription $subscription
     * @return \FOS\RestBundle\View\View
     */
    public function getApiSubscription(subscription $subscription)
    {
        return $this->view($subscription);
    }

    /**
     * @Rest\View(serializerGroups={"subscription"})
     * @Rest\Get("/api/anonymous/subscriptions")
     */
    public function getApiSubscriptions(){
        $subscriptions = $this->subscriptionRepository->findAll();

        return $this->view($subscriptions);
    }

    /**
     * @Rest\View(serializerGroups={"subscription"})
     * @Rest\Post("/api/admin/subscriptions")
     * @ParamConverter("subscription", converter="fos_rest.request_body")
     * @param subscription $subscription
     * @return \FOS\RestBundle\View\View
     */
    public function postApiSubscription(subscription $subscription){
        $this->em->persist($subscription);
        $this->em->flush();
        return $this->view($subscription);
    }

    /**
     * @Rest\View(serializerGroups={"subscription"})
     * @Rest\Patch("/api/admin/subscriptions/{id}")
     * @param subscription $subscription
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function patchApiSubscription(subscription $subscription,Request $request){
        if ($request->get('name') !== null) {
            $subscription->setName($request->get('name'));
        }
        if ($request->get('slogan') !== null) {
            $subscription->setSlogan($request->get('slogan'));
        }
        if ($request->get('url') !== null) {
            $subscription->setUrl($request->get('url'));
        }

        $this->em->persist($subscription);
        $this->em->flush();
        return $this->view($subscription);
    }

    /**
     * @Rest\View(serializerGroups={"subscription"})
     * @Rest\Delete("/api/admin/subscriptions/{id}")
     * @param Subscription $subscription
     */
    public function deleteApisubscription(Subscription $subscription){
        foreach ($subscription->getUsers() as $user){
            if (!empty($user)){
                throw new NotFoundHttpException('You are not allowed to delete a Subscription if there is at least one User linked.' );
            }
        }
        $this->em->remove($subscription);
        $this->em->flush();
    }
}
