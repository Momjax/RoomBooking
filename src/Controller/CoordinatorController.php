<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClasseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/coordinator')]
#[IsGranted('ROLE_PROFESSEUR')]
class CoordinatorController extends AbstractController
{
    #[Route('', name: 'app_coordinator')]
    public function index(): Response
    {
        $user = $this->getUser();
        $classesGerees = $user->getClassesGerees();

        return $this->render('coordinator/index.html.twig', [
            'classes' => $classesGerees,
        ]);
    }

    #[Route('/classe/{id}', name: 'app_coordinator_classe_show')]
    public function showClasse(
        int $id,
        ClasseRepository $classeRepository
    ): Response {
        $classe = $classeRepository->find($id);
        $user = $this->getUser();

        // Vérifier que le prof gère bien cette classe
        if (!$user->getClassesGerees()->contains($classe)) {
            $this->addFlash('error', 'Vous ne gérez pas cette classe.');
            return $this->redirectToRoute('app_coordinator');
        }

        return $this->render('coordinator/classe_show.html.twig', [
            'classe' => $classe,
        ]);
    }

    #[Route('/classe/{id}/add-student', name: 'app_coordinator_add_student', methods: ['GET', 'POST'])]
    public function addStudent(
        int $id,
        Request $request,
        ClasseRepository $classeRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $classe = $classeRepository->find($id);
        $user = $this->getUser();

        if (!$user->getClassesGerees()->contains($classe)) {
            $this->addFlash('error', 'Vous ne gérez pas cette classe.');
            return $this->redirectToRoute('app_coordinator');
        }

        if ($request->isMethod('POST')) {
            $student = new User();
            $student->setEmail($request->request->get('email'));
            $student->setFirstname($request->request->get('firstname'));
            $student->setLastname($request->request->get('lastname'));
            $student->setRoles(['ROLE_USER']);
            $student->setPassword($passwordHasher->hashPassword($student, 'etudiant123'));
            $student->setClasse($classe);

            $em->persist($student);
            $em->flush();

            $this->addFlash('success', 'Étudiant "' . $student->getFullName() . '" ajouté à la classe !');
            return $this->redirectToRoute('app_coordinator_classe_show', ['id' => $id]);
        }

        return $this->render('coordinator/add_student.html.twig', [
            'classe' => $classe,
        ]);
    }

    #[Route('/classe/{classeId}/remove-student/{studentId}', name: 'app_coordinator_remove_student', methods: ['POST'])]
    public function removeStudent(
        int $classeId,
        int $studentId,
        ClasseRepository $classeRepository,
        UserRepository $userRepository,
        EntityManagerInterface $em
    ): Response {
        $classe = $classeRepository->find($classeId);
        $user = $this->getUser();

        if (!$user->getClassesGerees()->contains($classe)) {
            $this->addFlash('error', 'Vous ne gérez pas cette classe.');
            return $this->redirectToRoute('app_coordinator');
        }

        $student = $userRepository->find($studentId);
        if ($student && $student->getClasse() === $classe) {
            $student->setClasse(null);
            $em->flush();
            $this->addFlash('success', 'Étudiant "' . $student->getFullName() . '" retiré de la classe.');
        }

        return $this->redirectToRoute('app_coordinator_classe_show', ['id' => $classeId]);
    }
}
