<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin", name="admin")
     * 
     */
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository): Response
    {

        $products = $productRepository->findBy([], ['id' => 'DESC']);
        $categories = $categoryRepository->findBy([], ['id' => 'DESC']);

        return $this->render('admin/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }
}
