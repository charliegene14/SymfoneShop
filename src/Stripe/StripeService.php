<?php

namespace App\Stripe;

use App\Cart\CartService;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService {

    protected $secretKey;
    protected $publicKey;
    protected $purchaseRepository;
    protected $em;
    protected $cartService;
    protected $flashBag;
    protected $urlGenerator;
    protected $secretEndpoint;

    public function __construct(
        string $secretKey,
        string $publicKey,
        string $secretEndpoint,
        PurchaseRepository $purchaseRepository,
        EntityManagerInterface $em,
        CartService $cartService,
        FlashBagInterface $flashBag,
        UrlGeneratorInterface $urlGenerator
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

        $purchase->setStatus(Purchase::STATUS_PAID);
        $this->cartService->empty();
        $this->flashBag->add('success', 'Votre commande a bien été passée ! Merci !');
        $this->em->flush();
    }
}