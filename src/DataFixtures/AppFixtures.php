<?php

namespace App\DataFixtures;

use App\Entity\Client;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {

        for ($i = 1; $i <= 5; $i++) {
            $client = new Client();
            $client->setNom('Nom du client ' . $i);
            $client->setPrenom('Prénom du client ' . $i);
            $client->setAdresse('Adresse du client ' . $i);
            $client->setNumTel('Numéro de téléphone du client ' . $i);
            $client->setMail('client' . $i . '@example.com');
            $client->setPassword('password'.$i);
            $manager->persist($client);
        }
        $manager->flush();
    }
}
