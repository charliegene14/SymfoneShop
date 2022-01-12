<?php

namespace App\Controller\Purchase;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchasesListController extends AbstractController
{
    /**
     * @Route("/purchases/list", name="purchases_list")
     * @IsGranted("ROLE_USER")
     */
    public function index(Request $request): Response
    {
        /** @var User */
        $user = $this->getUser();

        $purchases = $user->getPurchases();

        return $this->render('purchase/index.html.twig', [
            'purchases' => $purchases,
        ]);
    }
}
