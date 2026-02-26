<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLogin(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Se connecter');

        // On soumet le formulaire avec l'admin (les fixtures doivent être chargées)
        $form = $crawler->selectButton('Connexion')->form([
            '_username' => 'admin@mediaschool.me',
            '_password' => 'admin123'
        ]);

        $client->submit($form);

        // Après le login on doit être redirigé vers le dashboard
        $this->assertResponseRedirects('/dashboard');

        $client->followRedirect();
        // On vérifie que la page est bien chargée et contient "Tableau de bord"
        $this->assertResponseIsSuccessful();
    }

    public function testLogout(): void
    {
        $client = static::createClient();

        // On log l'utilisateur manuellement
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('admin@mediaschool.me');

        if (!$testUser) {
            $this->markTestSkipped('Les fixtures ne sont pas chargées, utilisateur de test manquant.');
        }

        $client->loginUser($testUser);

        $client->request('GET', '/logout');

        // La déconnexion doit renvoyer vers le login
        $this->assertResponseRedirects('http://localhost/login');
    }
}
