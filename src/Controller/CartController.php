<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CartController extends AbstractController
{

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CartService
     */
    protected $cartService;

    public function __construct(ProductRepository $productRepository, CartService $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * @Route("/cart/add/{id}", name="cart_add", requirements={"id":"\d+"})
     */
    public function add($id, Request $request)
    {
        
        $product = $this->productRepository->find($id);
        if (!$product) throw new NotFoundHttpException("Le produit demandé n'éxiste pas.");

        $this->cartService->add($product->getId());

        $this->addFlash('success', 'Le produit a bien été ajouté au panier !');
    
        if ($request->query->get('returnToCart')) return $this->redirectToRoute("cart_show");
    
        return $this->redirectToRoute("product_show", [
            'category_id' => $product->getCategory() ? $product->getCategory()->getId() : '00',
            'category_slug' => $product->getCategory()? $product->getCategory()->getSlug() : 'no-category',
            'product_id' => $product->getId(),
            'product_slug' => $product->getSlug(),
        ]);
    }

    /**
     * @Route("/cart", name="cart_show")
     */
    public function show() {

        $items = $this->cartService->getCartItems();
        $total = $this->cartService->getTotal();

        return $this->render("cart/show.html.twig", [
            'items' => $items,
            'total' => $total,
        ]);
    }

    /**
     * @Route("/cart/delete/{id}", name="cart_delete", requirements={"id": "\d+"})
     */
    public function delete(int $id) {
        $product = $this->productRepository->find($id);

        if (!$product) throw $this->createNotFoundException("Le produit demandé n'éxiste pas !");

        $this->cartService->delete($id);

        $this->addFlash('success', 'Le produit a bien été supprimé du panier !');

        return $this->redirectToRoute("cart_show");
    }

    /**
     * @Route("/cart/decrement/{id}", name="cart_decrement", requirements={"id": "\d+"})
     */
    public function decrement(int $id) {

        $product = $this->productRepository->find($id);
        if (!$product) throw new NotFoundHttpException("Le produit demandé n'éxiste pas.");

        $this->cartService->decrement($id);

        $this->addFlash('success', 'Le produit a bien été supprimé du panier !');

        return $this->redirectToRoute("cart_show");
    }
}
