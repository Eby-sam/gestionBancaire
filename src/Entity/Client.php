<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['compte','client'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['compte','client'])]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['compte','client'])]
    private ?string $adresse = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['compte','client'])]
    private ?string $numTel = null;

    #[ORM\Column(length: 255)]
    #[Groups(['compte','client'])]
    private ?string $mail = null;

    #[ORM\OneToMany(mappedBy: 'titulaire', targetEntity: Compte::class)]
    private Collection $clientCompte;

    public function __construct()
    {
        $this->clientCompte = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getNumTel(): ?string
    {
        return $this->numTel;
    }

    public function setNumTel(?string $numTel): static
    {
        $this->numTel = $numTel;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }


    /**
     * @return Collection<int, Compte>
     */
    public function getClientCompte(): Collection
    {
        return $this->clientCompte;
    }

    public function addClientCompte(Compte $clientCompte): static
    {
        if (!$this->clientCompte->contains($clientCompte)) {
            $this->clientCompte->add($clientCompte);
            $clientCompte->setTitulaire($this);
        }

        return $this;
    }

    public function removeClientCompte(Compte $clientCompte): static
    {
        if ($this->clientCompte->removeElement($clientCompte)) {
            if ($clientCompte->getTitulaire() === $this) {
                $clientCompte->setTitulaire(null);
            }
        }

        return $this;
    }
}
