<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    protected $categoryRepository;
    protected $productRepository;
    protected $urlGenerator;

    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/{id}-{slug}", name="product_category")
     */
    public function category($id, $slug): Response
    {

        $category = $this->categoryRepository->findOneBy([
            'id' => $id,
            'slug' => $slug,
        ]);

        if ($id == "00" && $slug == "no-category") {
            $category = new Category;
            $category
                ->setName("Aucune catégorie")
                ->setSlug("no-category");

            $products = $this->productRepository->findBy([
                'category' => NULL,
            ]);

            foreach ($products as $product) {
                $category->addProduct($product);
            }
        }

        if (!$category) {
            throw new NotFoundHttpException("La catégorie demandée n'éxiste pas.");
        }

        return $this->render('product/category.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/{category_id}-{category_slug}/{product_id}-{product_slug}", name="product_show")
     */
    public function show($category_id, $category_slug, $product_id, $product_slug): Response
    {
        $category = $this->categoryRepository->findOneBy([
            'id' => $category_id,
            'slug' => $category_slug
        ]);

        $product = $this->productRepository->findOneBy([
            'id' => $product_id,
            'slug' => $product_slug,
            'category' => $category,
        ]);

        if (!$product || !$category && $category_id != "00" && $category_slug != "no-category") {
            throw new NotFoundHttpException("Le produit demandée n'éxiste pas.");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
