<?php

namespace App\Controller\Purchase;

use DateTime;
use App\Entity\User;
use App\Entity\Purchase;
use App\Cart\CartService;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchaseConfirmationController extends AbstractController {

    /**
     * @Route("/purchase/confirm", name="purchase_confirm")
     * @IsGranted("ROLE_USER")
     */
    public function confirm(Request $request, CartService $cartService, EntityManagerInterface $em) {
        
        /** @var User */
        $user = $this->getUser();
        $form = $this->createForm(CartConfirmationType::class);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $this->addFlash('danger', "Vous n'avez pas confirmé le formulaire de livraison !");
            return $this->redirectToRoute("cart_show");
        }
        
        $cartItems = $cartService->getCartItems();

        if (count($cartItems) <= 0) {
            $this->addFlash('warning', "Vous ne pouvez pas confirmer une commande sans produits !");
            return $this->redirectToRoute("cart_show");
        }

        /** @var Purchase */
        $purchase = $form->getData();
        $purchase   ->setUser($user)
                    // ->setPurchasedAt(new DateTime()) // set in prePersist ORM entity 
                    // ->setTotal($cartService->getTotal()) // set in preFlush ORM entity 
        ;

        $em->persist($purchase);

        foreach ($cartItems as $cartItem) {

            $purchaseItem = new PurchaseItem;

            $purchaseItem   ->setPurchase($purchase)
                            ->setProduct($cartItem->product)
                            ->setProductName($cartItem->product->getName())
                            ->setProductPrice($cartItem->product->getPrice())
                            ->setQuantity($cartItem->qty)
                            ->setTotal($cartItem->getTotal())
            ;

            $em->persist($purchaseItem);
        }

        $em->flush();

    
        return $this->redirectToRoute("purchase_payment_form", [
            'id' => $purchase->getId(),
        ]);
    }
}