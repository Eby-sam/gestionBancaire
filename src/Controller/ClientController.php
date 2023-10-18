<?php

namespace App\Controller;

use App\Entity\Client;
use App\Entity\Compte;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ClientController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('/client', name: 'clientsList')]
    public function index(): Response
    {
        $clients = $this->entityManager->getRepository(Client::class)->findAll();

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/api/clientList', name: 'clientsList', methods: ['GET'])]
    public function listClients(ClientRepository $clientRepository, SerializerInterface $serializer): JsonResponse
    {

        $clientList = $clientRepository->findAll();
        $json = $serializer->serialize($clientList,'json',['groups'=>'client']);
        return new JsonResponse($json,Response::HTTP_OK,[],true);
    }

    #[Route('/delete/{id}', name: 'client_delete', methods: ['DELETE'])]
    public function delete(Client $client, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($client);
        $entityManager->flush();

        $this->addFlash('success', 'Le client a été supprimé avec succès.');

        return $this->redirectToRoute('app_client');
    }

    #[Route('/compte/{id}', name: 'client_details' , methods: ['GET'])]
    public function details(Client $client): Response
    {
        return $this->render('compte/index.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/api/add', methods: ['POST'])]
    public function addClient(Request $request): JsonResponse
    {
        $data = $request->getContent();

        $client = $this->serializer->deserialize($data, Client::class, 'json');

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $this->json(['message' => 'Client ajouté']);
    }


    #[Route('/edite/{id}', methods: ['PUT'])]
    public function editClient($id, Request $request): JsonResponse
    {
        $data = $request->getContent();

        $client = $this->serializer->deserialize($data, Client::class, 'json');

        $existingClient = $this->entityManager->getRepository(Client::class)->find($id);

        if (!$existingClient) {
            return $this->json(['message' => 'Client non trouvé'], 404);
        }

        $existingClient->setNom($client->getNom());
        $existingClient->setPrenom($client->getPrenom());
        $existingClient->setAdresse($client->getAdresse());
        $existingClient->setNumTel($client->getNumTel());
        $existingClient->setMail($client->getMail());

        $this->entityManager->flush();

        return $this->json(['message' => 'Client modifié avec succès']);
    }

    #[Route('/retrait/{id}', methods: ['POST'])]
    public function retraitClient($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $client = $this->entityManager->getRepository(Client::class)->find($id);
        if (!$client) {
            return $this->json(['message' => 'Client non trouvé'], 404);
        }

        $montant = $data['montant'];
        if (!is_numeric($montant) || $montant <= 0) {
            return $this->json(['message' => 'Montant de retrait invalide'], 400);
        }

        $this->entityManager->flush();
        return $this->json(['message' => 'Retrait effectué avec succès']);
    }

    #[Route('/depot/{id}', methods: ['POST'])]
    public function depotClient($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $client = $this->entityManager->getRepository(Client::class)->find($id);
        if (!$client) {
            return new JsonResponse(['message' => 'Client non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        if (!array_key_exists('montant', $data)) {
            return new JsonResponse(['message' => 'Le montant na pas été fourni'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $montant = $data['montant'];
        if (!is_numeric($montant) || $montant <= 0) {
            return new JsonResponse(['message' => 'Montant de dépôt invalide'], JsonResponse::HTTP_BAD_REQUEST);
        }


        
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Dépôt effectué avec succès'], JsonResponse::HTTP_OK);
    }

}
