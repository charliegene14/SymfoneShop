<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\ProductRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy([], ['id' => 'DESC'], 3);

        $noCategory = new Category;
        $noCategory
            ->setName("Aucune catégorie")
            ->setSlug("no-category");

        foreach ($products as $product) {
            if (!$product->getCategory()) {
                $product->setCategory($noCategory);
            }
        }

        return $this->render('home.html.twig', [
            'products' => $products
        ]);
    }
}
