<?php

namespace App\Controller;

use App\Entity\Card;
use App\Entity\User;
use App\Repository\CardRepository;
use App\Repository\SubscriptionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as REST;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class UserController extends AbstractFOSRestController
{
    private $userRepository;
    private $em;

    /**
     * UserController constructor.
     * @param $userRepository
     * @param $em
     */
    public function __construct(UserRepository $userRepository, EntityManagerInterface $em)
    {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    /**
     * @Rest\View(serializerGroups={"anonymous"})
     * @Rest\Get("/api/anonymous/users/{email}")
     * @param User $user
     * @return \FOS\RestBundle\View\View
     */
    public function getApiUser(User $user)
    {
        return $this->view($user);
    }

    /**
     * @Rest\View(serializerGroups={"anonymous"})
     * @Rest\Get("/api/anonymous/users")
     */
    public function getApiUsers(){
        $users = $this->userRepository->findAll();

        return $this->view($users);
    }
    /**
     * @Rest\View(serializerGroups={"admin"})
     * @Rest\Get("/api/admin/users/{email}")
     * @param User $user
     * @return \FOS\RestBundle\View\View
     */
    public function getApiUserAdmin(User $user)
    {
        return $this->view($user);
    }

    /**
     * @Rest\View(serializerGroups={"admin"})
     * @Rest\Get("/api/admin/users")
     */
    public function getApiUsersAdmin(){
        $users = $this->userRepository->findAll();

        return $this->view($users);
    }

    /**
     * @Rest\Get("api/user")
     * @Rest\View(serializerGroups={"user"})
     */
    public function show()
    {
        $user = $this->getUser();
        return $this->view($user);
    }
    /**
     * @Rest\Patch("api/user")
     * @Rest\View(serializerGroups={"user"})
     */
    public function edit(Request $request, CardRepository $cardRepository, SubscriptionRepository $subscriptionRepository)
    {
        $user = $this->userRepository->find($this->getUser());
        if ($request->get('firstname') !== null) {
            $user->setFirstname($request->get('firstname'));
        }
        if ($request->get('lastname') !== null) {
            $user->setLastname($request->get('lastname'));
        }
        if ($request->get('address') !== null) {
            $user->setAddress($request->get('address'));
        }
        if ($request->get('country') !== null) {
            $user->setCountry($request->get('country'));
        }
        if ($request->get('subscription') !== null) {
            $user->setSubscription($subscriptionRepository->findOneBy(['name'=>$request->get('subscription')['name']]));
        }
        if ($request->get('cards') !== null) {
            $mycards = $cardRepository->findBy(['user'=>$user]);
            foreach ($mycards as $card){
                $this->em->remove($card);
            }
            foreach ($request->get('cards') as $cardInfo ){
                $card = new Card();
                if ($cardInfo['name'] !== null) {
                    $card->setName($cardInfo['name']);
                }
                if ($cardInfo['creditCardType'] !== null) {
                    $card->setCreditCardType($cardInfo['creditCardType']);
                }
                if ($cardInfo['creditCardNumber'] !== null) {
                    $card->setCreditCardNumber($cardInfo['creditCardNumber']);
                }
                if ($cardInfo['currencyCode'] !== null) {
                    $card->setCurrencyCode($cardInfo['currencyCode']);
                }
                if ($cardInfo['value'] !== null) {
                    $card->setValue($cardInfo['value']);
                }
                $card->setUser($this->getUser());
                $this->em->persist($card);
                $this->em->flush();
            }
        }
        $this->em->flush();
        return $this->view($user);
    }

    /**
     * @Rest\View(serializerGroups={"anonymous"})
     * @Rest\Post("/api/anonymous/users")
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @param User $user
     * @return \FOS\RestBundle\View\View
     */
    public function postApiUser(Request $request, User $user, CardRepository $cardRepository, SubscriptionRepository $subscriptionRepository, ConstraintViolationListInterface $validationErrors){
        $errors = array();
        if ($validationErrors ->count() > 0) {
            /** @var ConstraintViolation $constraintViolation */
            foreach ($validationErrors as $constraintViolation ){
                $message = $constraintViolation ->getMessage ();
                $propertyPath = $constraintViolation ->getPropertyPath ();
                $errors[] = ['message' => $message , 'propertyPath' => $propertyPath ];
            }
        }

        if(empty($request->get('subscription'))) {
            $errors[] = ['message' => 'You should have a subscription to register successfully', 'propertyPath' => 'subscription'];
        } else {
            $subscription = $subscriptionRepository->findOneBy(['name'=>$request->get('subscription')['name']]);
            if ( $subscription == null){
                throw new NotFoundHttpException('there is no such subscription');
            }
            $user->setSubscription($subscription);
        }
//        if ($request->get('cards') !== null) {
//            foreach ($request->get('cards') as $cardInfo ){
//                $card = new Card();
//                if ($cardInfo['name'] !== null) {
//                    $card->setName($cardInfo['name']);
//                }
//                if ($cardInfo['creditCardType'] !== null) {
//                    $card->setCreditCardType($cardInfo['creditCardType']);
//                }
//                if ($cardInfo['creditCardNumber'] !== null) {
//                    $card->setCreditCardNumber($cardInfo['creditCardNumber']);
//                }
//                if ($cardInfo['currencyCode'] !== null) {
//                    $card->setCurrencyCode($cardInfo['currencyCode']);
//                }
//                if ($cardInfo['value'] !== null) {
//                    $card->setValue($cardInfo['value']);
//                }
//                $user->addCard($card);
//                $this->em->persist($card);
//                $this->em->flush();
//            }
//        }
        if (!empty($errors)) {
            throw new BadRequestHttpException(\json_encode( $errors));
        }

        $this->em->persist($user);
        $this->em->flush();
        return $this->view($user);
    }

    /**
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Patch("/api/admin/users")
     * @param Request $request
     * @return \FOS\RestBundle\View\View
     */
    public function patchApiUser(Request $request, SubscriptionRepository $subscriptionRepository){
        $user = $this->userRepository->find($this->getUser());
        if ($request->get('firstname') !== null) {
            $user->setFirstname($request->get('firstname'));
        }
        if ($request->get('lastname') !== null) {
            $user->setLastname($request->get('lastname'));
        }
        if ($request->get('email') !== null) {
            $user->setEmail($request->get('email'));
        }
        if ($request->get('apiKey') !== null) {
            $user->setApiKey($request->get('apiKey'));
        }
        if ($request->get('address') !== null) {
            $user->setAddress($request->get('address'));
        }
        if ($request->get('country') !== null) {
            $user->setCountry($request->get('country'));
        }
        if ($request->get('subscription') !== null) {
            $user->setSubscription($subscriptionRepository->findOneBy(['name'=>$request->get('subscription')['name']]));
        }
        $this->em->persist($user);
        $this->em->flush();
        return $this->view($user);
    }

    /**
     * @Rest\Delete("/api/admin/users/{id}")
     * @param User $user
     */
    public function deleteApiUser(User $user){
        foreach ($user->getCards() as $card){
            $this->em->remove($card);
        }
        $this->em->remove($user);
        $this->em->flush();
    }
}
