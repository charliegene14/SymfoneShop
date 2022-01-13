<?php

namespace App\EventDispatcher;

use App\Event\ProductViewEvent;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductViewEmailSubscriber implements EventSubscriberInterface {

    protected $mailer;

    public function __construct(MailerInterface $mailer)
    {
       $this->mailer = $mailer; 
    }

    public static function getSubscribedEvents() {

        return [
            'product.view' => 'sendViewedEmail'
        ];
    }

    public function sendViewedEmail(ProductViewEvent $productViewEvent) {
        
        // $email = new TemplatedEmail();

        // $email  ->from(new Address("contact@mail.com", "Infos de la boutique"))
        //         ->to("admin@mail.com")
        //         ->htmlTemplate("email/product_view.html.twig")
        //         ->context([
        //             'product' =>  $productViewEvent->getProduct()
        //         ])
        //         ->subject("Visite du produit n°" . $productViewEvent->getProduct()->getId())
        // ;

        // $this->mailer->send($email);
    }
}