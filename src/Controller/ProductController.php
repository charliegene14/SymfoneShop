<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProductController extends AbstractController
{

    protected $manager;
    protected $categoryRepository;
    protected $productRepository;

    public function __construct(
        CategoryRepository $categoryRepository,
        ProductRepository $productRepository,
        EntityManagerInterface $manager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->productRepository = $productRepository;
        $this->manager = $manager;
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

        if (!$product) {
            throw new NotFoundHttpException("Le produit demandée n'éxiste pas.");
        }

        if (!$category) {
            if ($category_id != "00" || $category_slug != "no-category") {
                throw new NotFoundHttpException("Le produit demandé n'éxiste pas.");
            }
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/admin/product/create", name="product_create")
     */
    public function create(sluggerInterface $slugger, Request $request): Response
    {
        $form = $this->createForm(ProductType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $product = $form->getData();
            $product->setSlug(strtolower($slugger->slug($product->getName())));
            
            $this->manager->persist($product);
            $this->manager->flush();

            return $this->redirectToRoute('product_edit', [
                'id' => $product->getId(),
                'isNew' => 1,
            ]);
        }

        return $this->render("/product/create.html.twig", [
            'formView' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/product/{id}/edit", name="product_edit")
     */
    public function edit($id, Request $request, sluggerInterface $slugger): Response {

        $product = $this->productRepository->find($id);

        if(!$product) {
            throw new NotFoundHttpException("Le produit demandé n'éxiste pas.");
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $product->setSlug(strtolower($slugger->slug($product->getName())));
            $this->manager->flush();
        }

        return $this->render("/product/edit.html.twig", [
            "product" => $product,
            "formView" => $form->createView(),
            "isNew" => $request->query->get('isNew', 0)
        ]);
    }
}
