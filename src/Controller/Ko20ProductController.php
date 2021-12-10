<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
// Pour afficher une erreur 404 Not found sans utiliser AbstractController
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{

    protected $categoryRepository;
    protected $productRepository;
    // protected $urlGenerator;

    // Pour passer les produits et caégories dans les URL
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/{slug}", name="product_category")
     */
    public function category($slug): Response
    {

        $category = $this->categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        // Si on écrit "aucune-categorie" dans l'url, créer artificiellement une catégorie correspondante
        if ($slug === "aucune-categorie") {
            $category = new Category;
            $category
                ->setName("Aucune catégorie")
                ->setSlug("aucune-categorie");

            // Pour afficher les produits existants qui n'ont pas de catégorie (NULL en bdd) : on va chercher tous les produits ayant une catégorie NULL en bdd et on les passe dans $category
            $products = $this->productRepository->findBy([
                'category' => NULL,
            ]);

            foreach ($products as $product) {
                $category->addProduct($product);
            }
        }

        // Si on passe une catégorie inexistante dans l'url
        if (!$category) {
            throw $this->createNotFoundException("La catégorie recherchée n'existe pas");
        }

        return $this->render('product/category.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * @Route("/{category_slug}/{product_id}-{product_slug}", name="product_show")
     */
    public function show($category_slug, $product_id, $product_slug): Response
    {
        $category = $this->categoryRepository->findOneBy([
            'slug' => $category_slug
        ]);

        $product = $this->productRepository->findOneBy([
            'id' => $product_id,
            'slug' => $product_slug,
            'category' => $category
        ]);

        // Si la catégorie recherchée n'existe pas en bdd / n'est pas "aucune-categorie"
        if (!$category && $category_slug !== "aucune-categorie") {
            throw $this->createNotFoundException("Le produit recherché n'existe pas");
        }

        // Si le produit n'existe pas 
        if (!$product) {
            throw $this->createNotFoundException("Le produit recherché n'existe pas");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }
}
