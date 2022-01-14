<?php

namespace App\EventDispatcher;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Event\PurchaseSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PurchaseSuccessRefreshSubscriber implements EventSubscriberInterface {


    protected $cartService;
    protected $flashBag;
    protected $em;
    
    public function __construct(CartService $cartService, FlashBagInterface $flashBag, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->cartService = $cartService;
        $this->flashBag = $flashBag;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            
        ];
    }

    public function refreshSuccess(PurchaseSuccessEvent $purchaseSuccessEvent) {

        
        $purchase = $purchaseSuccessEvent->getPurchase();
        $purchase->setStatus(Purchase::STATUS_PAID);

        $this->cartService->empty();
        $this->flashBag->add('success', 'Votre commande a bien été passée ! Merci !');
        $this->em->flush();
    }
}