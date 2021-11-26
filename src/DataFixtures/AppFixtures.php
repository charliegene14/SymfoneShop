<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Category;

use Faker\Factory;
use Bezhanov\Faker\Provider\Commerce;
use Bluemmb\Faker\PicsumPhotosProvider;
use Liior\Faker\Prices;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{

    protected $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Prices($faker));
        $faker->addProvider(new Commerce($faker));
        $faker->addProvider(new PicsumPhotosProvider($faker));

        /**
         * Create 3 random categories for database.
         */
        for ($c = 0; $c < 3; $c++) {

            $category = new Category();
            $category_name = $faker->word();

            $category
                ->setName($category_name)
                ->setSlug(strtolower($this->slugger->slug($category_name)));

            $manager->persist($category);

            /**
             * Create 15 to 20 product for each category in database.
             */
            for ($p = 0; $p < mt_rand(15, 20); $p++) {
                $product = new Product();
                $product_name = $faker->productName();

                $product
                    ->setName($product_name)
                    ->setPrice($faker->price(9900, 499900))
                    ->setSlug(strtolower($this->slugger->slug($product_name)))
                    ->setCategory($category)
                    ->setShortDescription($faker->paragraph())
                    ->setMainPicture($faker->imageUrl(200, 200, true));

                $manager->persist($product);
            }
        }

        $manager->flush();
    }
}
