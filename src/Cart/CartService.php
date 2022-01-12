<?php

namespace App\Cart;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService {

    protected $session;
    protected $productRepository;

    public function __construct(SessionInterface $session, ProductRepository $productRepository)
    {
        $this->session = $session;
        $this->productRepository = $productRepository;
    }

    public function empty(): void {
        $this->session->set('cart', []);
    }

    public function add(int $id): void {

        $cart = $this->session->get('cart', []);

        array_key_exists($id, $cart) ? $cart[$id]++ : $cart[$id] = 1;

        $this->session->set('cart', $cart);
    }

    public function getTotal(): float {
        $total = 0;

        foreach ($this->session->get('cart', []) as $id => $qty) {
            
            $product = $this->productRepository->find($id);

            if (!$product) continue;

            $total += $product->getPrice() * $qty;
        }

        return $total;
    }

    /**
     * @return CartItem[]
     */
    public function getCartItems(): array {

        $items = [];

        foreach ($this->session->get('cart', []) as $id => $qty) {
            $product = $this->productRepository->find($id);
            if (!$product) continue;

            $cartItem = new CartItem($product, $qty);

            array_push($items, $cartItem);
        }

        return $items;
    }

    public function getTotalQuantity(): int {

        $total = 0;

        foreach ($this->session->get('cart', []) as $id => $qty) {
            
            $product = $this->productRepository->find($id);
            if (!$product) continue;

            $total += $qty;
        }

        return $total;
    }

    public function delete(int $id): void {
        $cart = $this->session->get('cart', []);
 
        if ( isset($cart[$id]) ) unset($cart[$id]);

        $this->session->set('cart', $cart);
    }

    public function decrement(int $id): void {

        $cart = $this->session->get('cart', []);
    
        if (!isset($cart[$id])) return;

        if ($cart[$id] == 1) {
            $this->delete($id);
            return;
        }
        
        $cart[$id]--;
        $this->session->set('cart', $cart);
    }
}