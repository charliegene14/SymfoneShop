<?php

namespace App\Controller\Purchase;

use App\Cart\CartService;
use Stripe\Webhook;
use App\Entity\Purchase;
use Psr\Log\LoggerInterface;
use App\Stripe\StripeService;
use App\Repository\PurchaseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Exception\SignatureVerificationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Stripe\PaymentIntent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PurchasePaymentController extends AbstractController {

    /**
     * @Route("/purchase/pay/{id}", name="purchase_payment_form", requirements={"id": "\d+"})
     * @IsGranted("ROLE_USER")
     */
    public function showForm(int $id, PurchaseRepository $purchaseRepository, StripeService $stripeService): Response {

        $purchase = $purchaseRepository->find($id);

        if (!$purchase
            || ($purchase && $purchase->getUser() !== $this->getUser())
            || ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)
        ) {

            $this->addFlash("warning", "La commande n'existe pas !");
            return $this->redirectToRoute("purchases_list");
        }

        $intent = $stripeService->getIntent($purchase);

        return $this->render('purchase/payment.html.twig', [
            'id' => $id,
            'clientSecret' => $intent->client_secret,
            'publicKey' => $stripeService->getPublicKey(),
        ]);
    }

   /**
    * @Route("/purchase/check", name="purchase_check", methods={"POST"})
    * 
    */
    public function check(StripeService $stripeService): Response {

        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = $stripeService->getSecretEndpoint();

        $payload = @file_get_contents('php://input');
        //$sig_header = $request->server->get('HTTP_STRIPE_SIGNATURE');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {

            $event = Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );

        } catch(\UnexpectedValueException $e) {

            // Invalid payload
            http_response_code(400);
            exit();

        } catch(SignatureVerificationException $e) {

            // Invalid signature
            http_response_code(400);
            exit();
            
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;

                $purchaseId = intval($paymentIntent->metadata->id);
                $stripeService->eventSucceeded($purchaseId);
                break;

            default:
                echo 'Received unknown event type ' . $event->type;
                
        }

        http_response_code(200);

        return new JsonResponse(['status' => 'success']);
    }
}