<?php

namespace App\Stripe;

use App\Entity\Purchase;
use App\Cart\CartService;
use App\Event\PurchaseSuccessEvent;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class StripeService {

    protected $secretKey;
    protected $publicKey;
    protected $purchaseRepository;
    protected $em;
    protected $cartService;
    protected $flashBag;
    protected $urlGenerator;
    protected $secretEndpoint;
    protected $dispatcher;

    public function __construct(
        string $secretKey,
        string $publicKey,
        string $secretEndpoint,
        PurchaseRepository $purchaseRepository,
        EntityManagerInterface $em,
        CartService $cartService,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $urlGenerator,
        EventDispatcherInterface $dispatcher
    )
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        $this->secretEndpoint = $secretEndpoint;
        $this->purchaseRepository = $purchaseRepository;
        $this->em = $em;
        $this->cartService = $cartService;
        $this->flashBag = $flashBag;
        $this->urlGenerator = $urlGenerator;
        $this->dispatcher = $dispatcher;
    }

    public function getPublicKey(): string {
        return $this->publicKey;
    }

    public function getSecretEndpoint(): string {
        return $this->secretEndpoint;
    }

    public function getIntent(Purchase $purchase) {
        
        $stripe = new \Stripe\StripeClient($this->secretKey);

        $intent = $stripe->paymentIntents->create(
          [
            'amount' => $purchase->getTotal(),
            'currency' => 'eur',
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'id' => $purchase->getId(),
            ],
          ]
        );

        return $intent;
    }

    public function eventSucceeded(int $id) {

        /** @var Purchase */
        $purchase = $this->purchaseRepository->find($id);

        $purchaseEvent = new PurchaseSuccessEvent($purchase);
        $this->dispatcher->dispatch($purchaseEvent, 'purchase.success');
    }
}