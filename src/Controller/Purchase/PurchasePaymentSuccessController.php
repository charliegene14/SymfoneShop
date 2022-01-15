<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Event\PurchaseSuccessEvent;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PurchasePaymentSuccessController extends AbstractController {

    /**
     * @Route("/purchase/success/{id}", name="purchase_payment_success", requirements={"id": "\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function success(int $id, PurchaseRepository $purchaseRepository, EntityManagerInterface $em, CartService $cartService, EventDispatcherInterface $dispatcher) {

        /** @var Purchase */
        $purchase = $purchaseRepository->find($id);

        if (!$purchase
            || ($purchase && $purchase->getUser() !== $this->getUser())
            || ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)
        ) {

            $this->addFlash("warning", "La commande n'existe pas !");
            return $this->redirectToRoute("purchases_list");
        }


        /**
         * For enabling these following functions,
         * please go through stripe commands.
         * Else just unset // commentaries.
         * 
         * In local:
         * stripe login
         * stripe listen --forward-to https://localhost:8000/purchase/check
         * 
         * Online (HIGHLY RECOMMENDED)
         * add stripe webhook on /purchase/check.
         * 
         * See at App\Stripe\StripeService->eventSucceeded().
         */

        // $purchase->setStatus(Purchase::STATUS_PAID);
        // $em->flush();
        $cartService->empty();
        $this->addFlash("success", "La commande a été payée payée et confirmée !");

        $purchaseEvent = new PurchaseSuccessEvent($purchase);
        $dispatcher->dispatch($purchaseEvent, 'purchase.success');

        return $this->redirectToRoute("purchases_list");
    }
}