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
use Symfony\Component\Serializer\SerializerInterface;

class CompteController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
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
                'client' => $compte->getTitulaire()->getId(),
                'autorisationDecouvert' => $compte->isAutorisationDecouvert(),
                'tauxInteret' => $compte->getTauxInteret(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/compte/add', methods: ['POST'])]
    public function addCompte(Request $request): JsonResponse
    {
        $data = $request->getContent();

        $compte = $this->serializer->deserialize($data, Compte::class, 'json');

        $this->entityManager->persist($compte);
        $this->entityManager->flush();

        return $this->json(['message' => 'Compte ajouté']);
    }

    #[Route('/compte/edite/{id}', methods: ['PUT'])]
    public function editCompte($id, Request $request): JsonResponse
    {
        $data = $request->getContent();

        $compte = $this->serializer->deserialize($data, Compte::class, 'json');

        $existingCompte = $this->entityManager->getRepository(Compte::class)->find($id);

        if (!$existingCompte) {
            return $this->json(['message' => 'Compte non trouvé'], 404);
        }

        $existingCompte->setNumeroCompte($compte->getNumeroCompte());
        $existingCompte->setTypeCompte($compte->getTypeCompte());
        $existingCompte->setSolde($compte->getSolde());
        $existingCompte->setTitulaire($compte->getTitulaire());
        $existingCompte->setAutorisationDecouvert($compte->isAutorisationDecouvert());
        $existingCompte->setTauxInteret($compte->getTauxInteret());

        $this->entityManager->flush();

        return $this->json(['message' => 'Compte modifié avec succès']);
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

    #[Route('/searchClient', methods: ['POST'])]
    public function searchClient(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $nomClient = $data['nom'];

        if (empty($nomClient)) {
            return new JsonResponse(['message' => 'Le nom du client na pas été fourni'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $comptes = $this->entityManager->getRepository(Compte::class)->findByClientName($nomClient);

        $compteData = [];
        foreach ($comptes as $compte) {
            $compteData[] = [
                'id' => $compte->getId(),
                'numeroCompte' => $compte->getNumeroCompte(),
                'typeCompte' => $compte->getTypeCompte(),
                'solde' => $compte->getSolde(),
                'client' => $compte->getTitulaire()->getId(),
                'autorisationDecouvert' => $compte->isAutorisationDecouvert(),
                'tauxInteret' => $compte->getTauxInteret(),
            ];
        }

        return new JsonResponse($compteData, JsonResponse::HTTP_OK);
    }

    #[Route('/calculateInterest/{id}', methods: ['GET'])]
    public function calculateInterest($id): JsonResponse
    {
        $compte = $this->entityManager->getRepository(Compte::class)->find($id);
        if (!$compte) {
            return new JsonResponse(['message' => 'Compte non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }
        if ($compte->getTypeCompte() !== 'epargne') {
            return new JsonResponse(['message' => 'Ce compte nest pas un compte dépargne'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $solde = $compte->getSolde();
        $tauxInteret = $compte->getTauxInteret();
        $interets = ($solde * $tauxInteret) / 100;
        return new JsonResponse(['interets' => $interets], JsonResponse::HTTP_OK);
    }


    #[Route('/addInterest/{id}', methods: ['GET'])]
    public function addInterest($id): JsonResponse
    {
        $compte = $this->entityManager->getRepository(Compte::class)->find($id);

        if (!$compte) {
            return new JsonResponse(['message' => 'Compte non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($compte->getTypeCompte() !== 'epargne') {
            return new JsonResponse(['message' => 'Ce compte nest pas un compte dépargne'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $solde = $compte->getSolde();
        $tauxInteret = $compte->getTauxInteret();

        $interets = ($solde * $tauxInteret) / 100;

        $nouveauSolde = $solde + $interets;
        $compte->setSolde($nouveauSolde);

        $this->entityManager->flush();

        return new JsonResponse(['message' => 'Intérêts ajoutés au solde avec succès', 'nouveauSolde' => $nouveauSolde], JsonResponse::HTTP_OK);
    }

}
