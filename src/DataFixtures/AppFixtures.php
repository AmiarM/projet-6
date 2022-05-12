<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Categorie;
use App\Entity\Image;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $slugger;
    private $hasher;

    public function __construct(SluggerInterface $slugger, UserPasswordHasherInterface $hasher)
    {
        $this->slugger = $slugger;
        $this->hasher = $hasher;
    }
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');
        $admin = new User();
        $admin->setFirstname("admin")
            ->setLastname("admin")
            ->setPassword($this->hasher->hashPassword($admin, "password"))
            ->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@admin.com')
            ->setAvatar('avatar');
        $manager->persist($admin);
        for ($c = 0; $c < 10; $c++) {
            $categorie = new Categorie();
            $categorie->setName($faker->sentence(1));
            $manager->persist($categorie);
            for ($u = 0; $u < 5; $u++) {
                $user = new User();


                $user->setFirstname($faker->firstName())
                    ->setLastname($faker->lastName())
                    ->setPassword($this->hasher->hashPassword($user, "password"))
                    ->setEmail($faker->email())
                    ->setAvatar('avatar');

                $manager->persist($user);
                for ($t = 0; $t < 10; $t++) {
                    $trick = new Trick($faker->sentence(1));

                    $trick->setName($faker->sentence(1))
                        ->setDescription($faker->paragraph(1))
                        ->setSlug(strtolower($this->slugger->slug($trick->getName())))
                        ->setCategorie($categorie)
                        ->setUser($user);
                    $manager->persist($trick);
                    $image = new Image();
                    for ($i = 0; $i < 10; $i++) {
                        $image->setName($faker->imageUrl(400, 400, true))
                            ->setTrick($trick);
                        $manager->persist($image);
                    }
                }
            }
        }
        $manager->flush();
    }
}
