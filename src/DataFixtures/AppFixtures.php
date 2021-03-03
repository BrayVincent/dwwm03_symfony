<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Tag;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{

    /**
     * Encodeur de mot de passe
     * 
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {

        // Création d'un nouvel objet Faker
        $faker = Factory::create('fr_FR');

        // Création de nos 5 catégories
        for ($c = 0; $c < 5; $c++) {

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

        // Création de 5 utilisateurs
        for ($u = 0; $u < 5; $u++) {

            // Création d'un nouvel objet User
            $user = new User;

            // Hashage de notre mot de passe avec les paramètres de sécurité de notre $user
            // dans /config/packages/security.yaml
            $hash = $this->encoder->encodePassword($user, "password");

            // Si premier utilisateur crée on lui donne le rôle d'admin
            // et on lui force son adresse mail
            if ($u === 0) {
                $user->setRoles(["ROLE_ADMIN"])
                    ->setEmail("admin@admin.local");
            } else {
                $user->setEmail($faker->safeEmail());
            }

            // Pour tout le monde
            $user->setPassword($hash);

            // On fait persister les données
            $manager->persist($user);
        }

        // On push le tout en BDD
        $manager->flush();
    }
}
