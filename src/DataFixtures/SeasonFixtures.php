<?php

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker;

class SeasonFixtures extends Fixture implements DependentFixtureInterface
{
    
    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('fr_FR');
        
        for ($i = 0; $i < 50; $i++) {
            $season = new Season();
            $season->setNumber($faker->numberBetween(1, 10));
            $season->setDescription($faker->paragraph);
            $season->setYear($faker->year);
            $manager->persist($season);
            $this->setReference('season_'. $i, $season);
            $season->setProgram($this->getReference('program_'.rand(0, 5)));
        }
        $manager->flush();
    }

    public function getDependencies()  
    {
        return [ProgramFixtures::class];  
    }
}
