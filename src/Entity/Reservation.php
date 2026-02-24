<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $reservationStart = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $reservationEnd = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Room $room = null;

    #[ORM\Column(length: 20)]
    private ?string $status = 'VALIDE';

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = 'VALIDE';
    }

    /**
     * Calcule le statut réel en fonction du temps
     */
    public function getComputedStatus(): string
    {
        if ($this->status === 'ANNULE') {
            return 'ANNULÉ';
        }

        $now = new \DateTime();
        if ($now < $this->reservationStart) {
            return 'VALIDÉ';
        }
        if ($now > $this->reservationEnd) {
            return 'TERMINÉ';
        }
        return 'EN COURS';
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReservationStart(): ?\DateTimeInterface
    {
        return $this->reservationStart;
    }

    public function setReservationStart(\DateTimeInterface $reservationStart): static
    {
        $this->reservationStart = $reservationStart;
        return $this;
    }

    public function getReservationEnd(): ?\DateTimeInterface
    {
        return $this->reservationEnd;
    }

    public function setReservationEnd(\DateTimeInterface $reservationEnd): static
    {
        $this->reservationEnd = $reservationEnd;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUtilisateur(): ?User
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?User $utilisateur): static
    {
        $this->utilisateur = $utilisateur;
        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;
        return $this;
    }
}
