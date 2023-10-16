<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompteController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/compte', methods: ['GET'])]
    public function listComptes(): JsonResponse
    {
        $comptes = $this->entityManager->getRepository(Compte::class)->findAll();

        $data = [];
        foreach ($comptes as $compte) {
            $data[] = [
                'id' => $compte->getId(),
                'numeroCompte' => $compte->getNumeroCompte(),
                'typeCompte' => $compte->getTypeCompte(),
                'solde' => $compte->getSolde(),
                'client' => $compte->getClient()->getId(),
                'autorisationDecouvert' => $compte->getAutorisationDecouvert(),
                'tauxInteret' => $compte->getTauxInteret(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/compte/add', methods: ['POST'])]
    public function addCompte(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $compte = new Compte();
        $compte->setNumeroCompte($data['numeroCompte']);
        $compte->setTypeCompte($data['typeCompte']);
        $compte->setSolde($data['solde']);
        $compte->setClient($this->entityManager->getReference(Client::class, $data['client']));
        $compte->setAutorisationDecouvert($data['autorisationDecouvert']);
        $compte->setTauxInteret($data['tauxInteret']);
        $this->entityManager->persist($compte);
        $this->entityManager->flush();

        return $this->json(['message' => 'Compte ajouté avec succès']);
    }

    #[Route('/compte/edite/{id}', methods: ['PUT'])]
    public function editCompte($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $compte = $this->entityManager->getRepository(Compte::class)->find($id);

        if (!$compte) {
            return $this->json(['message' => 'Compte pastrouvé'], 404);
        }

        $compte->setNumeroCompte($data['numeroCompte']);
        $compte->setTypeCompte($data['typeCompte']);
        $compte->setSolde($data['solde']);
        $compte->setClient($this->entityManager->getReference(Client::class, $data['client']));
        $compte->setAutorisationDecouvert($data['autorisationDecouvert']);
        $compte->setTauxInteret($data['tauxInteret']);

        $this->entityManager->flush();

        return $this->json(['message' => 'Compte modifié']);
    }

    #[Route('/compte/delete/{id}', methods: ['DELETE'])]
    public function deleteCompte($id): JsonResponse
    {
        $compte = $this->entityManager->getRepository(Compte::class)->find($id);

        if (!$compte) {
            return $this->json(['message' => 'Compte pas trouvé'], 404);
        }

        $this->entityManager->remove($compte);
        $this->entityManager->flush();

        return $this->json(['message' => 'Compte supprimé']);
    }

    #[Route('/compte/{id}/versement', methods: ['POST'])]
    public function versementCompte($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $compte = $this->entityManager->getRepository(Compte::class)->find($id);

        if (!$compte) {
            return $this->json(['message' => 'Compte non trouvé'], 404);
        }

        $montant = $data['montant'];
        if (!is_numeric($montant) || $montant <= 0) {
            return $this->json(['message' => 'Montant de versement invalide'], 400);
        }

        $compte->setSolde($compte->getSolde() + $montant);
        $this->entityManager->flush();
        return $this->json(['message' => 'Versement effectué avec succès']);
    }

}
