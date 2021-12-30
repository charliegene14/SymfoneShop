<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;

use App\Entity\Product;
use Liior\Faker\Prices;
use App\Entity\Category;
use Bezhanov\Faker\Provider\Commerce;

use Bluemmb\Faker\PicsumPhotosProvider;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{

    protected $slugger;
    protected $encoder;

    public function __construct(SluggerInterface $slugger, UserPasswordEncoderInterface $encoder)
    {
        $this->slugger = $slugger;
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager): void
    {

        $faker = Factory::create('fr_FR');
        $faker->addProvider(new Prices($faker));
        $faker->addProvider(new Commerce($faker));
        $faker->addProvider(new PicsumPhotosProvider($faker));

        /**
         * Create admin user and some random users
         */
        $admin = new User;
        $admin  ->setEmail("admin@local.com")
                ->setFullName("Admin")
                ->setPassword($this->encoder->encodePassword($admin, "password"))
                ->setRoles(['ROLE_ADMIN']);

        $manager->persist($admin);

        for ($u = 0; $u < 5; $u++) {
            $user = new User ;
            $user   ->setEmail("user$u@local.com")
                    ->setFullName($faker->name)
                    ->setPassword($this->encoder->encodePassword($user, "password"));

            $manager->persist($user);
        }

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
