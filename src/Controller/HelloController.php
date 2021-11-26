<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello/{name?World}", name="hello")
     */
    public function hello($name)
    {
        return $this->render("hello.html.twig", [
            "name" => $name
        ]);
    }
}
