<?php

namespace App\Controller;

use App\Entity\Card;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as REST;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CardController extends AbstractFOSRestController
{
    private $cardRepository;
    private $em;

    /**
     * CardController constructor.
     * @param $cardRepository
     * @param $em
     */
    public function __construct(CardRepository $cardRepository, EntityManagerInterface $em)
    {
        $this->cardRepository = $cardRepository;
        $this->em = $em;
    }

    /**
     * @Rest\View(serializerGroups={"card"})
     * @Rest\Get("/api/cards/{name}")
     * @param card $card
     * @return \FOS\RestBundle\View\View
     */
    public function getApiCard(card $card, AuthorizationCheckerInterface $authorizationChecker)
    {
        if (false === $authorizationChecker->isGranted('ROLE_ADMIN')) {
            if ($card->getUser() !== $this->getUser()){
                throw new NotFoundHttpException('That\'s not one of your cards. Please get out of here!');
            }
        }
        return $this->view($card);
    }

    /**
     * @Rest\View(serializerGroups={"card"})
     * @Rest\Get("/api/cards")
     */
    public function getApiCards(AuthorizationCheckerInterface $authorizationChecker){

        if (false === $authorizationChecker->isGranted('ROLE_ADMIN')) {
            $cards = $this->cardRepository->findBy(['user'=>$this->getUser()]);
            return $this->view($cards);
        }
        $cards = $this->cardRepository->findAll();

        return $this->view($cards);
    }

    /**
     * @Rest\View(serializerGroups={"card"})
     * @Rest\Post("/api/cards")
     * @ParamConverter("card", converter="fos_rest.request_body")
     * @param card $card
     * @return \FOS\RestBundle\View\View
     */
    public function postApiCard(card $card){
        $this->em->persist($card);
        $this->em->flush();
        return $this->view($card);
    }

    /**
     * @Rest\View(serializerGroups={"card"})
     * @Rest\Patch("/api/cards/{id}")
     * @param card $card
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function patchApiCard(card $card,Request $request, AuthorizationCheckerInterface $authorizationChecker){
        if (false === $authorizationChecker->isGranted('ROLE_ADMIN')) {
            if ($card->getUser() !== $this->getUser()){
                throw new NotFoundHttpException('That\'s not one of your cards. You\' not allowed to change it!');
            }
        }
        if ($request->get('name') !== null) {
            $card->setName($request->get('name'));
        }
        if ($request->get('creditCardType') !== null) {
            $card->setCreditCardType($request->get('creditCardType'));
        }
        if ($request->get('creditCardNumber') !== null) {
            $card->setCreditCardNumber($request->get('creditCardNumber'));
        }
        if ($request->get('currencyCode') !== null) {
            $card->setCurrencyCode($request->get('currencyCode'));
        }
        if ($request->get('value') !== null) {
            $card->setValue($request->get('value'));
        }
        $this->em->persist($card);
        $this->em->flush();
        return $this->view($card);
    }

    /**
     * @Rest\View(serializerGroups={"card"})
     * @Rest\Delete("/api/cards/{id}")
     * @param card $card
     */
    public function deleteApiCard(Card $card, AuthorizationCheckerInterface $authorizationChecker){
        if (false === $authorizationChecker->isGranted('ROLE_ADMIN')) {
            if ($card->getUser() !== $this->getUser()){
                throw new NotFoundHttpException('That\'s not your card. You\' not allowed to delete it!');
            }
        }
        $this->em->remove($card);
        $this->em->flush();
    }

}
