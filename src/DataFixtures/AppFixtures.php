<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;

use App\Entity\Product;
use Liior\Faker\Prices;
use App\Entity\Category;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
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

        $users = [];
        $products = [];

        /**
         * Create admin user then some random users
         */
        $admin = new User;
        $admin  ->setEmail("admin@local.com")
                ->setFullName("Admin")
                ->setPassword($this->encoder->encodePassword($admin, "password"))
                ->setRoles(['ROLE_ADMIN']);

        array_push($users, $admin);
        $manager->persist($admin);

        for ($u = 0; $u < 5; $u++) {
            $user = new User ;
            $user   ->setEmail("user$u@local.com")
                    ->setFullName($faker->name)
                    ->setPassword($this->encoder->encodePassword($user, "password"));

            array_push($users, $user);
            $manager->persist($user);
        }

        /**
         * Create 3 random categories for database.
         */
        for ($c = 0; $c < 3; $c++) {

            $category = new Category();
            $category_name = $faker->word();

            $category
                ->setName($category_name);
                // ->setSlug(strtolower($this->slugger->slug($category_name)));  // set in Doctrine Listener

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
                    // ->setSlug(strtolower($this->slugger->slug($product_name))) // set in Doctrine Listener
                    ->setCategory($category)
                    ->setShortDescription($faker->paragraph())
                    ->setMainPicture($faker->imageUrl(200, 200, true));

                array_push($products, $product);
                $manager->persist($product);
            }
        }

        /**
         * Create purchases fixtures
         */
        for ($p = 0; $p < mt_rand(20, 40); $p++) {

            $purchase = new Purchase;
            $purchaseTotalPrice = 0;

            /** @var User */
            $user = $faker->randomElement($users);

            $purchase   ->setFullName($faker->name())
                        ->setAddress($faker->streetAddress())
                        ->setPostalCode($faker->postcode())
                        ->setCity($faker->city())
                        ->setUser($user)
                        ->setPurchasedAt($faker->dateTimeBetween('-6 months'))
            ;

            $selectedProducts = $faker->randomElements($products, mt_rand(3, 5));

            foreach ($selectedProducts as $product) {

                $purchaseItem = new PurchaseItem;

                $purchaseItem   ->setProduct($product)
                                ->setQuantity(mt_rand(1,3))
                                ->setProductName($product->getName())
                                ->setProductPrice($product->getPrice())
                                ->setTotal($purchaseItem->getProductPrice() * $purchaseItem->getQuantity())
                                ->setPurchase($purchase)
                ;

                $purchaseTotalPrice += $purchaseItem->getTotal();
                $manager->persist($purchaseItem);
            }

            $purchase->setTotal($purchaseTotalPrice);

            if ($faker->boolean(90)) $purchase->setStatus(Purchase::STATUS_PAID);
            $manager->persist($purchase);
        }

        $manager->flush();
    }
}
