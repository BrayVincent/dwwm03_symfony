<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // Création d'un nouvel objet Faker
        $faker = Factory::create('fr_FR');

        // Création de nos 5 catégories
        for ($c = 0; $c <= 5; $c++) {

            // Création d'un nouvel objet Tag
            $tag = new Tag;

            // On ajoute un nom à notre catégorie
            $tag->setName($faker->colorName());

            // On fait persister les données
            $manager->persist($tag);
        }

        // On push les catégories en BDD
        $manager->flush();

        // Récupérer les catégories créées
        $allTags = $manager->getRepository(Tag::class)->findAll();

        // Création entre 15 et 30 tâches aléatoirement
        for ($t = 0; $t < mt_rand(15, 30); $t++) {

            // Création d'un nouvel objet Task
            $task = new Task;

            // On nourrit l'objet Task
            $task->setName($faker->sentence(6))
                ->setDescription($faker->paragraph(3))
                ->setCreatedAt(new \DateTime()) // Attention les dates sont créées en fonction du paramétrage de votre serveur
                ->setDueAt($faker->dateTimeBetween('now', '6 months'))
                ->setTag($faker->randomElement($allTags));

            // On fait persister les données
            $manager->persist($task);
        }

        // On push le tout en BDD
        $manager->flush();
    }
}
