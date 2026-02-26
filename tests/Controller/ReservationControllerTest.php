<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ReservationControllerTest extends WebTestCase
{
    public function testReservationProcess(): void
    {
        $client = static::createClient();

        // 1. Authentifier un utilisateur
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('lucas.bernard@mediaschool.me');

        if (!$testUser) {
            $this->markTestSkipped('Veuillez charger les fixtures avant de lancer les tests.');
        }

        $client->loginUser($testUser);

        // 2. Visualiser les réservations (Le dashboard affiche nos réservations)
        $client->request('GET', '/dashboard');
        $this->assertResponseIsSuccessful();

        // 3. Accéder au choix de la salle pour réserver
        $client->request('GET', '/reservation/choose-room');
        $this->assertResponseIsSuccessful();

        // 4. On récupère une salle existante
        $roomRepository = static::getContainer()->get(RoomRepository::class);
        $room = $roomRepository->findOneBy(['nomSalle' => 'Salle Ada Lovelace']);

        if (!$room) {
            $this->markTestSkipped('Salle introuvable, fixtures non chargées.');
        }

        // 5. Test d'accès au planning de la salle
        $client->request('GET', '/reservation/planning/' . $room->getId());
        $this->assertResponseIsSuccessful();
    }
}
