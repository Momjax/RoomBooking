<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Room;
use App\Repository\ReservationRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/new/{id}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Room $room, Request $request, ReservationRepository $resRepo, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $start = new \DateTime($request->request->get('start_date') . ' ' . $request->request->get('start_time'));
            $end = new \DateTime($request->request->get('end_date') . ' ' . $request->request->get('end_time'));

            // Sécurité : fin doit être après début
            if ($end <= $start) {
                $this->addFlash('error', 'L\'heure de fin doit être après l\'heure de début.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $room->getId()]);
            }

            // Sécurité : pas de réservation dans le passé
            if ($start < new \DateTime()) {
                $this->addFlash('error', 'Vous ne pouvez pas réserver dans le passé.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $room->getId()]);
            }

            // Vérification de disponibilité via le Repository
            if (!$resRepo->isRoomAvailable($room->getId(), $start, $end)) {
                $this->addFlash('error', 'Désolé, cette salle est déjà occupée sur ce créneau.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $room->getId()]);
            }

            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUtilisateur($this->getUser());
            $reservation->setReservationStart($start);
            $reservation->setReservationEnd($end);

            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', 'Réservation confirmée pour la salle ' . $room->getNomSalle() . ' !');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('reservation/new.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/my-reservations', name: 'app_my_reservations')]
    public function myReservations(ReservationRepository $resRepo): Response
    {
        $reservations = $resRepo->findBy(
            ['utilisateur' => $this->getUser()],
            ['reservationStart' => 'DESC']
        );

        return $this->render('reservation/my_reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Reservation $reservation, EntityManagerInterface $em): Response
    {
        // Sécurité : On ne peut annuler que ses propres réservations (sauf Admin)
        if ($reservation->getUtilisateur() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas annuler cette réservation.');
        }

        $em->remove($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation annulée avec succès.');
        return $this->redirectToRoute('app_my_reservations');
    }
}
