<?php

namespace App\Controller;

use App\Form\UserType;
use App\Entity\User;
use App\Service\GlobalClock;
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author   Gaëtan Rolé-Dubruille <gaetan.role@gmail.com>
 */
final class RegistrationController extends AbstractController
{
    /**
     * @todo This code is generated by the maker-bundle, do not hesitate to factorize it and split the business logic.
     * @Route("/register", name="app_user_registration")
     * @throws Exception From NowInDateTime
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        GlobalClock $clock,
        Security $security,
        TranslatorInterface $translator,
        EntityManagerInterface $em
    ) {
        if ($security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $this->addFlash('danger', $translator->trans('is_authenticated_fully.flash.redirection', [], 'flashes'));
            return $this->redirectToRoute('app_index');
        }

        // Build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // Handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Encode the password (you could also do this via Doctrine listener)
            $password
                = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            // Using TimeContinuum to have power on time unit
            $user->setCreationDate($clock->getNowInDateTime());

            // Save the User object
            $em->persist($user);
            $em->flush();

            // Add a notification on security/login.html.twig
            $this->addFlash(
                'registration-success',
                $translator->trans('account_registered.flash.redirection', [], 'flashes')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', ['form' => $form->createView()]);
    }
}