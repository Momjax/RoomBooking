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
    #[Route('/calendar', name: 'app_reservation_calendar')]
    public function calendar(): Response
    {
        // Simple calendar view - current month
        return $this->render('reservation/calendar.html.twig', [
            'now' => new \DateTime(),
        ]);
    }

    #[Route('/choose-room', name: 'app_reservation_choose_room')]
    public function chooseRoom(Request $request, RoomRepository $roomRepository): Response
    {
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        return $this->render('reservation/choose_room.html.twig', [
            'rooms' => $roomRepository->findAll(),
            'selectedDate' => $date,
        ]);
    }

    #[Route('/planning/{id}', name: 'app_reservation_planning')]
    public function roomPlanning(Room $room, Request $request, ReservationRepository $resRepo): Response
    {
        $dateStr = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        $date = new \DateTime($dateStr);

        // Get reservations for this room and day
        $startOfDay = clone $date;
        $startOfDay->setTime(0, 0, 0);
        $endOfDay = clone $date;
        $endOfDay->setTime(23, 59, 59);

        $reservations = $resRepo->createQueryBuilder('r')
            ->where('r.room = :room')
            ->andWhere('r.reservationStart >= :start')
            ->andWhere('r.reservationStart <= :end')
            ->setParameter('room', $room)
            ->setParameter('start', $startOfDay)
            ->setParameter('end', $endOfDay)
            ->getQuery()->getResult();

        return $this->render('reservation/planning.html.twig', [
            'room' => $room,
            'date' => $date,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/new/{id}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Room $room, Request $request, ReservationRepository $resRepo, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $start = new \DateTime($request->request->get('start_date') . ' ' . $request->request->get('start_time'));
            $end = new \DateTime($request->request->get('end_date') . ' ' . $request->request->get('end_time'));

            if ($end <= $start) {
                $this->addFlash('error', 'L\'heure de fin doit être après l\'heure de début.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $room->getId()]);
            }

            if ($start < (new \DateTime())->modify('-5 minutes')) {
                $this->addFlash('error', 'Vous ne pouvez pas réserver dans le passé.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $room->getId()]);
            }

            if (!$resRepo->isRoomAvailable($room->getId(), $start, $end)) {
                $this->addFlash('error', 'Désolé, cette salle est déjà occupée sur ce créneau.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $room->getId()]);
            }

            $reservation = new Reservation();
            $reservation->setRoom($room);
            $reservation->setUtilisateur($this->getUser());
            $reservation->setReservationStart($start);
            $reservation->setReservationEnd($end);
            $reservation->setStatus('VALIDE');

            $em->persist($reservation);
            $em->flush();

            $this->addFlash('success', 'Réservation confirmée pour la salle ' . $room->getNomSalle() . ' !');
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('reservation/new.html.twig', [
            'room' => $room,
            'prefilledDate' => $request->query->get('date'),
            'prefilledHour' => $request->query->get('hour'),
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

        $reservation->setStatus('ANNULE');
        $em->flush();

        $this->addFlash('success', 'Réservation annulée.');
        return $this->redirectToRoute('app_my_reservations');
    }
}
