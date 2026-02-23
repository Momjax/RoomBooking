<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClasseRepository::class)]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $classeName = null;

    /**
     * Étudiants appartenant à cette classe
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'classe')]
    private Collection $etudiants;

    /**
     * Professeurs qui gèrent cette classe (ManyToMany inverse)
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'classesGerees')]
    private Collection $professeurs;

    public function __construct()
    {
        $this->etudiants = new ArrayCollection();
        $this->professeurs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClasseName(): ?string
    {
        return $this->classeName;
    }

    public function setClasseName(string $classeName): static
    {
        $this->classeName = $classeName;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getEtudiants(): Collection
    {
        return $this->etudiants;
    }

    public function addEtudiant(User $etudiant): static
    {
        if (!$this->etudiants->contains($etudiant)) {
            $this->etudiants->add($etudiant);
            $etudiant->setClasse($this);
        }
        return $this;
    }

    public function removeEtudiant(User $etudiant): static
    {
        if ($this->etudiants->removeElement($etudiant)) {
            if ($etudiant->getClasse() === $this) {
                $etudiant->setClasse(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getProfesseurs(): Collection
    {
        return $this->professeurs;
    }

    public function addProfesseur(User $professeur): static
    {
        if (!$this->professeurs->contains($professeur)) {
            $this->professeurs->add($professeur);
            $professeur->addClassesGeree($this);
        }
        return $this;
    }

    public function removeProfesseur(User $professeur): static
    {
        if ($this->professeurs->removeElement($professeur)) {
            $professeur->removeClassesGeree($this);
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->classeName ?? '';
    }
}
