<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(RoomRepository $roomRepository, ReservationRepository $reservationRepository): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_admin');
        }
        if ($this->isGranted('ROLE_PROFESSEUR')) {
            return $this->redirectToRoute('app_coordinator');
        }

        $user = $this->getUser();
        $rooms = $roomRepository->findAll();
        $reservations = $reservationRepository->findBy(
            ['utilisateur' => $user],
            ['reservationStart' => 'ASC']
        );

        return $this->render('dashboard/index.html.twig', [
            'rooms' => $rooms,
            'reservations' => $reservations,
        ]);
    }
}
