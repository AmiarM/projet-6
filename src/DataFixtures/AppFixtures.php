<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Entity\Categorie;
use Faker\Provider\Youtube;
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
        $faker->addProvider(new Youtube($faker));
        $admin = new User();
        $admin->setFirstname("admin")
            ->setLastname("admin")
            ->setPassword($this->hasher->hashPassword($admin, "password"))
            ->setEmail('admin@admin.com')
            ->setAvatar($faker->imageUrl());
        $manager->persist($admin);
        for ($u = 0; $u < 5; $u++) {
            $user = new User();
            $user->setFirstname($faker->firstName())
                ->setLastname($faker->lastName())
                ->setPassword($this->hasher->hashPassword($user, "password"))
                ->setEmail($faker->email())
                ->setActivated(1)
                ->setAvatar($faker->imageUrl());

            $manager->persist($user);
        }
        for ($c = 0; $c < 5; $c++) {
            $categorie = new Categorie();
            $categorie->setName($faker->word(2));
            $manager->persist($categorie);
            for ($co = 0; $co < 5; $co++) {
                for ($t = 0; $t < 5; $t++) {
                    $trick = new Trick($faker->sentence(1));

                    $trick->setName($faker->word(1))
                        ->setDescription($faker->paragraph())
                        ->setCategorie($categorie)
                        ->setUser($user);
                    $manager->persist($trick);
                    $image = new Image();
                    for ($i = 0; $i < 5; $i++) {
                        $image->setName($faker->imageUrl(400, 400, true))
                            ->setTrick($trick);
                        $manager->persist($image);
                    }
                    $video1 = new Video();
                    $video1
                        ->setUrl("https://www.youtube.com/embed/SQyTWk7OxSI")
                        ->setTrick($trick);

                    $video2 = new Video();
                    $video2
                        ->setUrl("https://www.youtube.com/embed/G9qlTInKbNE")
                        ->setTrick($trick);
                    $video3 = new Video();
                    $video3->setUrl("https://www.youtube.com/embed/SQyTWk7OxSI")
                        ->setTrick($trick);


                    $manager->persist($video1);
                    $manager->persist($video2);
                    $manager->persist($video3);

                    for ($co = 0; $co < 5; $co++) {
                        $comment = new Comment();
                        $comment->setContent($faker->sentence(1))
                            ->setTrick($trick)
                            ->setUser($user);
                        $manager->persist($comment);
                    }
                }
            }
        }
        $manager->flush();
    }
}
