<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        // $product = new Product();
        // $manager->persist($product);

        for ($i = 0; $i < 100; $i++) {
            $post = new Post();
            $post->setTitle($faker->sentence);
            $post->setContent($faker->paragraph(10));
            $post->setCreatedAt(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $faker->dateTimeThisYear->format('Y-m-d H:i:s')));

            $manager->persist($post);
        }

        $manager->flush();
    }
}
