<?php

namespace App\Controller;

use App\Entity\Classe;
use App\Entity\Equipement;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use App\Repository\ClasseRepository;
use App\Repository\RoomRepository;
use App\Repository\ReservationRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    // ============================================================
    // DASHBOARD ADMIN
    // ============================================================
    #[Route('', name: 'app_admin')]
    public function index(
        RoomRepository $roomRepository,
        UserRepository $userRepository,
        ClasseRepository $classeRepository
    ): Response {
        return $this->render('admin/index.html.twig', [
            'rooms' => $roomRepository->findAll(),
            'users' => $userRepository->findAll(),
            'classes' => $classeRepository->findAll(),
            'totalRooms' => count($roomRepository->findAll()),
            'totalUsers' => count($userRepository->findAll()),
            'totalClasses' => count($classeRepository->findAll()),
        ]);
    }

    // ============================================================
    // GESTION DES SALLES
    // ============================================================
    #[Route('/rooms', name: 'app_admin_rooms')]
    public function rooms(RoomRepository $roomRepository): Response
    {
        return $this->render('admin/rooms/list.html.twig', [
            'rooms' => $roomRepository->findAll(),
        ]);
    }

    #[Route('/rooms/create', name: 'app_admin_room_create', methods: ['GET', 'POST'])]
    public function createRoom(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $room = new Room();
            $room->setNomSalle($request->request->get('nom_salle'));
            $room->setCapacite((int) $request->request->get('capacite'));
            $room->setDescription($request->request->get('description'));

            // Équipements
            $equipements = $request->request->get('equipements', '');
            if (!empty($equipements)) {
                foreach (explode(',', $equipements) as $nomEquip) {
                    $nomEquip = trim($nomEquip);
                    if (!empty($nomEquip)) {
                        $equip = new Equipement();
                        $equip->setNomEquipement($nomEquip);
                        $equip->setRoom($room);
                        $em->persist($equip);
                    }
                }
            }

            $em->persist($room);
            $em->flush();

            $this->addFlash('success', 'Salle "' . $room->getNomSalle() . '" créée avec succès !');
            return $this->redirectToRoute('app_admin_rooms');
        }

        return $this->render('admin/rooms/create.html.twig');
    }

    #[Route('/rooms/{id}/edit', name: 'app_admin_room_edit', methods: ['GET', 'POST'])]
    public function editRoom(Room $room, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $room->setNomSalle($request->request->get('nom_salle'));
            $room->setCapacite((int) $request->request->get('capacite'));
            $room->setDescription($request->request->get('description'));

            // On vide les anciens équipements pour remettre les nouveaux
            foreach ($room->getEquipements() as $equip) {
                $em->remove($equip);
            }

            $equipements = $request->request->get('equipements', '');
            if (!empty($equipements)) {
                foreach (explode(',', $equipements) as $nomEquip) {
                    $nomEquip = trim($nomEquip);
                    if (!empty($nomEquip)) {
                        $equip = new Equipement();
                        $equip->setNomEquipement($nomEquip);
                        $equip->setRoom($room);
                        $em->persist($equip);
                    }
                }
            }

            $em->flush();
            $this->addFlash('success', 'Salle "' . $room->getNomSalle() . '" modifiée !');
            return $this->redirectToRoute('app_admin_rooms');
        }

        // On prépare la chaîne des équipements pour le champ input
        $equipNames = [];
        foreach ($room->getEquipements() as $e) {
            $equipNames[] = $e->getNomEquipement();
        }

        return $this->render('admin/rooms/edit.html.twig', [
            'room' => $room,
            'equipNames' => implode(', ', $equipNames)
        ]);
    }

    #[Route('/rooms/{id}', name: 'app_admin_room_show')]
    public function showRoom(Room $room): Response
    {
        return $this->render('admin/rooms/show.html.twig', [
            'room' => $room,
        ]);
    }

    #[Route('/rooms/{id}/delete', name: 'app_admin_room_delete', methods: ['POST'])]
    public function deleteRoom(Room $room, EntityManagerInterface $em): Response
    {
        $nom = $room->getNomSalle();
        $em->remove($room);
        $em->flush();

        $this->addFlash('success', 'Salle "' . $nom . '" supprimée.');
        return $this->redirectToRoute('app_admin_rooms');
    }

    // ============================================================
    // GESTION DES CLASSES
    // ============================================================
    #[Route('/classes', name: 'app_admin_classes')]
    public function classes(ClasseRepository $classeRepository): Response
    {
        return $this->render('admin/classes/list.html.twig', [
            'classes' => $classeRepository->findAll(),
        ]);
    }

    #[Route('/classes/create', name: 'app_admin_classe_create', methods: ['GET', 'POST'])]
    public function createClasse(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $classe = new Classe();
            $classe->setClasseName($request->request->get('classe_name'));
            $em->persist($classe);
            $em->flush();

            $this->addFlash('success', 'Classe "' . $classe->getClasseName() . '" créée !');
            return $this->redirectToRoute('app_admin_classes');
        }

        return $this->render('admin/classes/create.html.twig');
    }

    #[Route('/classes/{id}/edit', name: 'app_admin_classe_edit', methods: ['GET', 'POST'])]
    public function editClasse(Classe $classe, Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $classe->setClasseName($request->request->get('classe_name'));
            $em->flush();

            $this->addFlash('success', 'Classe modifiée avec succès.');
            return $this->redirectToRoute('app_admin_classes');
        }

        return $this->render('admin/classes/edit.html.twig', [
            'classe' => $classe,
        ]);
    }

    #[Route('/classes/{id}', name: 'app_admin_classe_show')]
    public function showClasse(Classe $classe): Response
    {
        return $this->render('admin/classes/show.html.twig', [
            'classe' => $classe,
        ]);
    }

    #[Route('/classes/{id}/delete', name: 'app_admin_classe_delete', methods: ['POST'])]
    public function deleteClasse(Classe $classe, EntityManagerInterface $em): Response
    {
        if ($classe->getEtudiants()->count() > 0) {
            $this->addFlash('error', 'Impossible de supprimer une classe qui contient encore des étudiants.');
            return $this->redirectToRoute('app_admin_classe_show', ['id' => $classe->getId()]);
        }

        $nom = $classe->getClasseName();
        $em->remove($classe);
        $em->flush();

        $this->addFlash('success', 'Classe "' . $nom . '" supprimée.');
        return $this->redirectToRoute('app_admin_classes');
    }

    // ============================================================
    // GESTION DES UTILISATEURS
    // ============================================================
    #[Route('/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        return $this->render('admin/users/list.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }


    #[Route('/users/create', name: 'app_admin_user_create', methods: ['GET', 'POST'])]
    public function createUser(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        ClasseRepository $classeRepository,
        UserRepository $userRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            if ($userRepository->findOneBy(['email' => $email])) {
                $this->addFlash('error', "Cet email est déjà utilisé.");
                return $this->redirectToRoute('app_admin_user_create');
            }

            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($request->request->get('firstname'));
            $user->setLastname($request->request->get('lastname'));
            $user->setRoles([$request->request->get('role')]);
            $user->setPassword($passwordHasher->hashPassword($user, $request->request->get('password')));

            $classeId = $request->request->get('classe_id');
            if ($classeId) {
                $classe = $classeRepository->find($classeId);
                if ($classe) {
                    $user->setClasse($classe);
                }
            }

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur "' . $user->getFullName() . '" créé !');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users/create.html.twig', [
            'classes' => $classeRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function editUser(
        User $user,
        Request $request,
        EntityManagerInterface $em,
        ClasseRepository $classeRepository,
        UserRepository $userRepository
    ): Response {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Vérif si le nouvel email n'est pas déjà pris par UN AUTRE
            $existing = $userRepository->findOneBy(['email' => $email]);
            if ($existing && $existing !== $user) {
                $this->addFlash('error', "Cet email est déjà pris.");
                return $this->redirectToRoute('app_admin_user_edit', ['id' => $user->getId()]);
            }

            $user->setEmail($email);
            $user->setFirstname($request->request->get('firstname'));
            $user->setLastname($request->request->get('lastname'));
            $user->setRoles([$request->request->get('role')]);

            // Gestion de la classe unique (Élève)
            $classeId = $request->request->get('classe_id');
            if ($classeId) {
                $user->setClasse($classeRepository->find($classeId));
            } else {
                $user->setClasse(null);
            }

            // Gestion des classes gérées (Professeur) - ManyToMany
            if (in_array('ROLE_PROFESSEUR', $user->getRoles())) {
                // On vide d'abord
                foreach ($user->getClassesGerees() as $oldClasse) {
                    $user->removeClassesGeree($oldClasse);
                }
                // On ajoute les nouvelles
                $managedClassesIds = $request->request->all('classes_gerees') ?? [];
                foreach ($managedClassesIds as $cid) {
                    $c = $classeRepository->find($cid);
                    if ($c)
                        $user->addClassesGeree($c);
                }
            }

            $em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour.');
            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'classes' => $classeRepository->findAll(),
        ]);
    }

    #[Route('/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, EntityManagerInterface $em): Response
    {
        $nom = $user->getFullName();
        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur "' . $nom . '" supprimé.');
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/users/{id}/reset-password', name: 'app_admin_user_reset_password', methods: ['POST'])]
    public function resetPassword(
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $newPassword = 'reset123';
        $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
        $em->flush();

        $this->addFlash('success', 'Mot de passe de "' . $user->getFullName() . '" réinitialisé (nouveau: reset123).');
        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/reservations', name: 'app_admin_reservations')]
    public function manageReservations(
        Request $request,
        ReservationRepository $resRepo,
        RoomRepository $roomRepo,
        UserRepository $userRepo
    ): Response {
        $roomId = $request->query->get('room');
        $userId = $request->query->get('user');
        $dateFilter = $request->query->get('date');
        $order = $request->query->get('order', 'DESC');

        $criteria = [];
        if ($roomId) {
            $criteria['room'] = $roomId;
        }
        if ($userId) {
            $criteria['utilisateur'] = $userId;
        }

        // Pour la date, on filtre sur le jour même
        if ($dateFilter) {
            $startOfDay = new \DateTime($dateFilter . ' 00:00:00');
            $endOfDay = new \DateTime($dateFilter . ' 23:59:59');

            // On va utiliser le QueryBuilder du repository pour un filtre plus complexe (plage de date)
            $qb = $resRepo->createQueryBuilder('r')
                ->where('r.reservationStart >= :start')
                ->andWhere('r.reservationStart <= :end')
                ->setParameter('start', $startOfDay)
                ->setParameter('end', $endOfDay);

            foreach ($criteria as $field => $value) {
                $qb->andWhere("r.$field = :$field")
                    ->setParameter($field, $value);
            }

            $qb->orderBy('r.reservationStart', $order);
            $reservations = $qb->getQuery()->getResult();
        } else {
            $reservations = $resRepo->findBy($criteria, ['reservationStart' => $order]);
        }

        return $this->render('admin/reservations.html.twig', [
            'reservations' => $reservations,
            'rooms' => $roomRepo->findAll(),
            'users' => $userRepo->findAll(),
            'selectedRoom' => $roomId,
            'selectedUser' => $userId,
            'selectedDate' => $dateFilter,
            'selectedOrder' => $order,
        ]);
    }

    #[Route('/reservations/{id}/cancel', name: 'app_admin_reservation_cancel', methods: ['POST'])]
    public function adminCancelReservation(Reservation $reservation, EntityManagerInterface $em): Response
    {
        $reservation->setStatus('ANNULE');
        $em->flush();

        $this->addFlash('success', 'Réservation annulée par l\'administrateur.');
        return $this->redirectToRoute('app_admin_reservations');
    }

    #[Route('/reservations/{id}/delete', name: 'app_admin_reservation_delete', methods: ['POST'])]
    public function adminDeleteReservation(Reservation $reservation, EntityManagerInterface $em): Response
    {
        if ($reservation->getStatus() !== 'ANNULE') {
            $this->addFlash('error', 'Vous devez d\'abord annuler la réservation avant de la supprimer définitivement.');
            return $this->redirectToRoute('app_admin_reservations');
        }

        $em->remove($reservation);
        $em->flush();

        $this->addFlash('success', 'Réservation supprimée définitivement de la base de données.');
        return $this->redirectToRoute('app_admin_reservations');
    }
}
