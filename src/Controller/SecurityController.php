<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/check-drivers', name: 'app_check_drivers')]
    public function checkDrivers(): Response
    {
        return new Response('Drivers: ' . implode(', ', \PDO::getAvailableDrivers()));
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function profile(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        \App\Repository\ReservationRepository $resRepo
    ): Response {
        $user = $this->getUser();
        if ($request->isMethod('POST')) {
            $newPass = $request->request->get('password');
            if ($newPass) {
                /** @var User $user */
                $user->setPassword($passwordHasher->hashPassword($user, $newPass));
                $em->flush();
                $this->addFlash('success', 'Votre mot de passe a été mis à jour.');
            }
        }
        return $this->render('security/profile.html.twig', [
            'reservations' => $resRepo->findBy(['utilisateur' => $user], ['reservationStart' => 'DESC'])
        ]);
    }
}
