<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        
        ->add('name', TextType::class, [
            'label' => 'Nom du produit',
        ])

        ->add('price', MoneyType::class, [
            'label' => 'Prix du produit',
        ])

        ->add('category', EntityType::class, [
            'label' => 'Catégorie du produit',
            'placeholder' => '-- Choisir une catégorie --',
            'class' => Category::class,
            'choice_label' => 'name',
            'required' => false,

        ])

        ->add('mainPicture', UrlType::class, [
            'label' => 'Image du produit',
        ])

        ->add('shortDescription', TextareaType::class, [
            'label' => 'Courte description du produit',
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
