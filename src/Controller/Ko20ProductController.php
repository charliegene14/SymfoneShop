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

    // CONSTRUCTEUR
    protected $categoryRepository;
    protected $productRepository;
    // protected $urlGenerator;

    // Pour passer les produits et catégories dans les URL
    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
    }


    // FONCTION POUR AFFICHER PAGE PRODUITS SANS CATEGORIE 
    private function no_category($category)
    {
        // Si on écrit "aucune-categorie" dans l'url, créer artificiellement une catégorie correspondante
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
        return $category;
    }

    // PAGE CATEGORIES
    /**
     * @Route("/{category_slug}", name="product_category")
     */
    public function category($category_slug): Response
    {
        // Récupérer la catégorie existante en bdd
        $category = $this->categoryRepository->findOneBy([
            'slug' => $category_slug
        ]);

        // Si la catégorie recherchée n'existe pas en bdd = soit produits sans catégorie -> page aucune catégorie, soit erreur 404
        if (!$category) {
            if ($category_slug === "aucune-categorie") {
                $category = $this->no_category($category);
            } else {
                throw $this->createNotFoundException("La catégorie recherchée n'existe pas");
            }
        }

        return $this->render('product/category.html.twig', [
            'category' => $category
        ]);
    }


    // PAGE PRODUIT
    /**
     * @Route("/{category_slug}/{product_id}-{product_slug}", name="product_show")
     */
    public function show($category_slug, $product_id, $product_slug): Response
    {
        // Récupérer la catégorie existante en bdd
        $category = $this->categoryRepository->findOneBy([
            'slug' => $category_slug
        ]);

        // Récupérer les produits correspondants
        $product = $this->productRepository->findOneBy([
            'id' => $product_id,
            'slug' => $product_slug,
            'category' => $category
        ]);

        // Si la catégorie recherchée n'existe pas en bdd = soit produits sans catégorie -> page aucune catégorie, soit erreur 404
        if (!$category) {
            if ($category_slug === "aucune-categorie") {
                $category = $this->no_category($category);
            } else {
                throw $this->createNotFoundException("La catégorie et/ou le produit recherché-e-s n'existe-nt pas.");
            }
        }

        // Si le produit n'existe pas 
        if (!$product) {
            throw $this->createNotFoundException("Le produit recherché n'existe pas");
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'category' => $category
        ]);
    }
}
