<?php

namespace App\Controller;

use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryController extends AbstractController
{
    protected $categoryRepository;
    protected $em;

    public function __construct(CategoryRepository $categoryRepository, EntityManagerInterface $em)
    {
        $this->categoryRepository = $categoryRepository;
        $this->em = $em;
    }

    /**
     * @Route("/admin/category/create", name="category_create")
     */
    public function create(Request $request, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted())
        {
            $category = $form->getData();
            $category->setSlug(strtolower($slugger->slug($category->getName())));

            $this->em->persist($category);
            $request->request->set('isNew', 1);
            $this->em->flush();
        }

        return $this->render('category/create.html.twig', [
            'formView' => $form->createView(),
            'isNew' => $request->request->get('isNew', 0),
        ]);
    }

    /**
     * @Route("admin/category/{id}/edit", name="category_edit")
     */
    public function edit($id, Request $request, sluggerInterface $slugger): Response
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            throw new NotFoundHttpException("La catégorie demandée n'éxiste pas.");
        }

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $category->setSlug(strtolower($slugger->slug($category->getName())));
            $request->request->set('isModified', 1);
            $this->em->flush();
        }

        dump($request);

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            "formView" => $form->createView(),
            "isModified" => $request->request->get('isModified', 0),
        ]);
    }
}
