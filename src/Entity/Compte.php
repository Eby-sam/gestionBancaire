<?php

namespace App\Entity;

use App\Repository\CompteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompteRepository::class)]
class Compte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numeroCompte = null;

    #[ORM\Column(length: 255)]
    private ?string $typeCompte = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $solde = null;

    #[ORM\ManyToOne(inversedBy: 'clientCompte')]
    private ?Client $titulaire = null;

    #[ORM\Column]
    private ?bool $autorisationDecouvert = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $tauxInteret = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroCompte(): ?string
    {
        return $this->numeroCompte;
    }

    public function setNumeroCompte(?string $numeroCompte): static
    {
        $this->numeroCompte = $numeroCompte;

        return $this;
    }

    public function getTypeCompte(): ?string
    {
        return $this->typeCompte;
    }

    public function setTypeCompte(string $typeCompte): static
    {
        $this->typeCompte = $typeCompte;

        return $this;
    }

    public function getSolde(): ?string
    {
        return $this->solde;
    }

    public function setSolde(string $solde): static
    {
        $this->solde = $solde;

        return $this;
    }

    public function getTitulaire(): ?Client
    {
        return $this->titulaire;
    }

    public function setTitulaire(?Client $titulaire): static
    {
        $this->titulaire = $titulaire;

        return $this;
    }

    public function isAutorisationDecouvert(): ?bool
    {
        return $this->autorisationDecouvert;
    }

    public function setAutorisationDecouvert(bool $autorisationDecouvert): static
    {
        $this->autorisationDecouvert = $autorisationDecouvert;

        return $this;
    }

    public function getTauxInteret(): ?string
    {
        return $this->tauxInteret;
    }

    public function setTauxInteret(?string $tauxInteret): static
    {
        $this->tauxInteret = $tauxInteret;

        return $this;
    }
}
