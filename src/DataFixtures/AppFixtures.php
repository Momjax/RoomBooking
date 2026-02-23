<?php

namespace App\DataFixtures;

use App\Entity\Classe;
use App\Entity\Equipement;
use App\Entity\Reservation;
use App\Entity\Room;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // ============================================================
        // 1. CLASSES (promotions)
        // ============================================================
        $classeBTS1 = new Classe();
        $classeBTS1->setClasseName('BTS SIO 1 - SLAM');
        $manager->persist($classeBTS1);

        $classeBTS2 = new Classe();
        $classeBTS2->setClasseName('BTS SIO 2 - SLAM');
        $manager->persist($classeBTS2);

        $classeBTS2SISR = new Classe();
        $classeBTS2SISR->setClasseName('BTS SIO 2 - SISR');
        $manager->persist($classeBTS2SISR);

        // ============================================================
        // 2. UTILISATEURS
        // ============================================================

        // --- ADMIN ---
        $admin = new User();
        $admin->setEmail('admin@mediaschool.me');
        $admin->setFirstname('Marie');
        $admin->setLastname('Dupont');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // --- COORDINATEURS (Professeurs) ---
        $prof1 = new User();
        $prof1->setEmail('n.choquet@mediaschool.me');
        $prof1->setFirstname('Nicolas');
        $prof1->setLastname('Choquet');
        $prof1->setRoles(['ROLE_PROFESSEUR']);
        $prof1->setPassword($this->passwordHasher->hashPassword($prof1, 'prof123'));
        $prof1->addClassesGeree($classeBTS2);
        $manager->persist($prof1);

        $prof2 = new User();
        $prof2->setEmail('j.martin@mediaschool.me');
        $prof2->setFirstname('Julie');
        $prof2->setLastname('Martin');
        $prof2->setRoles(['ROLE_PROFESSEUR']);
        $prof2->setPassword($this->passwordHasher->hashPassword($prof2, 'prof123'));
        $prof2->addClassesGeree($classeBTS1);
        $prof2->addClassesGeree($classeBTS2SISR);
        $manager->persist($prof2);

        // --- ÉTUDIANTS ---
        $etudiant1 = new User();
        $etudiant1->setEmail('lucas.bernard@mediaschool.me');
        $etudiant1->setFirstname('Lucas');
        $etudiant1->setLastname('Bernard');
        $etudiant1->setRoles(['ROLE_USER']);
        $etudiant1->setPassword($this->passwordHasher->hashPassword($etudiant1, 'etudiant123'));
        $etudiant1->setClasse($classeBTS2);
        $manager->persist($etudiant1);

        $etudiant2 = new User();
        $etudiant2->setEmail('emma.petit@mediaschool.me');
        $etudiant2->setFirstname('Emma');
        $etudiant2->setLastname('Petit');
        $etudiant2->setRoles(['ROLE_USER']);
        $etudiant2->setPassword($this->passwordHasher->hashPassword($etudiant2, 'etudiant123'));
        $etudiant2->setClasse($classeBTS2);
        $manager->persist($etudiant2);

        $etudiant3 = new User();
        $etudiant3->setEmail('hugo.roche@mediaschool.me');
        $etudiant3->setFirstname('Hugo');
        $etudiant3->setLastname('Roche');
        $etudiant3->setRoles(['ROLE_USER']);
        $etudiant3->setPassword($this->passwordHasher->hashPassword($etudiant3, 'etudiant123'));
        $etudiant3->setClasse($classeBTS1);
        $manager->persist($etudiant3);

        $etudiant4 = new User();
        $etudiant4->setEmail('lea.moreau@mediaschool.me');
        $etudiant4->setFirstname('Léa');
        $etudiant4->setLastname('Moreau');
        $etudiant4->setRoles(['ROLE_USER']);
        $etudiant4->setPassword($this->passwordHasher->hashPassword($etudiant4, 'etudiant123'));
        $etudiant4->setClasse($classeBTS2SISR);
        $manager->persist($etudiant4);

        // ============================================================
        // 3. SALLES + ÉQUIPEMENTS
        // ============================================================
        $salle1 = new Room();
        $salle1->setNomSalle('Salle Ada Lovelace');
        $salle1->setCapacite(30);
        $salle1->setDescription('Salle de cours principale, idéale pour les cours magistraux.');
        $manager->persist($salle1);

        $this->addEquipements($manager, $salle1, ['Vidéoprojecteur', 'Tableau blanc', 'Wi-Fi']);

        $salle2 = new Room();
        $salle2->setNomSalle('TP Informatique - Linus');
        $salle2->setCapacite(20);
        $salle2->setDescription('Salle de TP équipée de 20 postes de travail.');
        $manager->persist($salle2);

        $this->addEquipements($manager, $salle2, ['20 PC fixes', 'Vidéoprojecteur', 'Tableau interactif', 'Wi-Fi']);

        $salle3 = new Room();
        $salle3->setNomSalle('Box Projet A');
        $salle3->setCapacite(6);
        $salle3->setDescription('Petit espace collaboratif pour travaux de groupe.');
        $manager->persist($salle3);

        $this->addEquipements($manager, $salle3, ['Écran TV', 'Tableau blanc', 'Wi-Fi']);

        $salle4 = new Room();
        $salle4->setNomSalle('Box Projet B');
        $salle4->setCapacite(6);
        $salle4->setDescription('Petit espace collaboratif pour travaux de groupe.');
        $manager->persist($salle4);

        $this->addEquipements($manager, $salle4, ['Écran TV', 'Wi-Fi']);

        $salle5 = new Room();
        $salle5->setNomSalle('Salle de réunion - Turing');
        $salle5->setCapacite(12);
        $salle5->setDescription('Salle de réunion pour les équipes pédagogiques.');
        $manager->persist($salle5);

        $this->addEquipements($manager, $salle5, ['Vidéoprojecteur', 'Webcam HD', 'Système visioconférence', 'Wi-Fi']);

        // ============================================================
        // 4. RÉSERVATIONS
        // ============================================================

        // Prof réserve pour un cours demain 9h-12h
        $resa1 = new Reservation();
        $resa1->setUtilisateur($prof1);
        $resa1->setRoom($salle1);
        $resa1->setReservationStart(new \DateTime('2026-02-24 09:00'));
        $resa1->setReservationEnd(new \DateTime('2026-02-24 12:00'));
        $manager->persist($resa1);

        // Prof réserve TP mardi 14h-17h
        $resa2 = new Reservation();
        $resa2->setUtilisateur($prof1);
        $resa2->setRoom($salle2);
        $resa2->setReservationStart(new \DateTime('2026-02-25 14:00'));
        $resa2->setReservationEnd(new \DateTime('2026-02-25 17:00'));
        $manager->persist($resa2);

        // Étudiant réserve box projet mercredi 10h-12h
        $resa3 = new Reservation();
        $resa3->setUtilisateur($etudiant1);
        $resa3->setRoom($salle3);
        $resa3->setReservationStart(new \DateTime('2026-02-26 10:00'));
        $resa3->setReservationEnd(new \DateTime('2026-02-26 12:00'));
        $manager->persist($resa3);

        // Étudiant réserve box projet jeudi 14h-16h
        $resa4 = new Reservation();
        $resa4->setUtilisateur($etudiant2);
        $resa4->setRoom($salle4);
        $resa4->setReservationStart(new \DateTime('2026-02-27 14:00'));
        $resa4->setReservationEnd(new \DateTime('2026-02-27 16:00'));
        $manager->persist($resa4);

        // Admin réserve salle réunion vendredi 11h-12h
        $resa5 = new Reservation();
        $resa5->setUtilisateur($admin);
        $resa5->setRoom($salle5);
        $resa5->setReservationStart(new \DateTime('2026-02-28 11:00'));
        $resa5->setReservationEnd(new \DateTime('2026-02-28 12:00'));
        $manager->persist($resa5);

        $manager->flush();
    }

    private function addEquipements(ObjectManager $manager, Room $room, array $noms): void
    {
        foreach ($noms as $nom) {
            $equip = new Equipement();
            $equip->setNomEquipement($nom);
            $equip->setRoom($room);
            $manager->persist($equip);
        }
    }
}
