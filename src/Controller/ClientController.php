<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ClientController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/client', name: 'clientsList')]
    public function index(): Response
    {
        $clients = $this->entityManager->getRepository(Client::class)->findAll();

        return $this->render('client/index.html.twig', [
            'clients' => $clients,
        ]);
    }

    #[Route('/delete/{id}', name: 'client_delete')]
    public function delete(Client $client, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($client);
        $entityManager->flush();

        $this->addFlash('success', 'Le client a été supprimé avec succès.');

        return $this->redirectToRoute('app_client');
    }

    #[Route('/compte/{id}', name: 'client_details')]
    public function details(Client $client): Response
    {
        return $this->render('compte/index.html.twig', [
            'client' => $client,
        ]);
    }

    #[Route('/add', methods: ['POST'])]
    public function addClient(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setNom($data['nom']);
        $client->setPrenom($data['prenom']);
        $client->setAdresse($data['adresse']);
        $client->setNumTel($data['numeroTelephone']);
        $client->setMail($data['adresseEmail']);

        $this->entityManager->persist($client);
        $this->entityManager->flush();

        return $this->json(['message' => 'Client ajouté']);
    }


    #[Route('/edite/{id}', methods: ['PUT'])]
    public function editClient($id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = $this->entityManager->getRepository(Client::class)->find($id);

        if (!$client) {
            return $this->json(['message' => 'Client non trouvé'], 404);
        }

        $client->setNom($data['nom']);
        $client->setPrenom($data['prenom']);
        $client->setAdresse($data['adresse']);
        $client->setNumTel($data['numeroTelephone']);
        $client->setMail($data['adresseEmail']);

        $this->entityManager->flush();

        return $this->json(['message' => 'Client modifié']);
    }
}
