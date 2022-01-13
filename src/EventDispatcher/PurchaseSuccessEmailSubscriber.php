<?php

namespace App\EventDispatcher;

use Symfony\Component\Mime\Email;
use App\Event\PurchaseSuccessEvent;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseSuccessEmailSubscriber implements EventSubscriberInterface {

    protected $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            'purchase.success' => 'sendSuccessEmail'
        ];
    }

    public function sendSuccessEmail(PurchaseSuccessEvent $purchaseSuccessEvent) {

        
        $purchase = $purchaseSuccessEvent->getPurchase();
        $user = $purchase->getUser();
        
        $email = new TemplatedEmail();

        $email  ->from(new Address("contact@mail.com", "Infos de la boutique"))
                ->to(new Address($user->getEmail(), $user->getFullName()))
                ->htmlTemplate("email/purchase_success.html.twig")
                ->context([
                   'user' => $user,
                   'purchase' => $purchase,
                ])
                ->subject("Votre commande " . $purchase->getId() . " a bien été prise en compte !")
        ;

        $this->mailer->send($email);
    }
}