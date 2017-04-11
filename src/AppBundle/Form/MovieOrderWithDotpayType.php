<?php

namespace AppBundle\Form;

use AppBundle\Entity\MovieAccess;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\CallbackTransformer;
use Doctrine\ORM\EntityManager;

class MovieOrderWithDotpayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', HiddenType::class, ['data' => $options['amount']])
            ->add('id', HiddenType::class, ['data' => $options['id']])
            ->add('description', HiddenType::class, ['data' => $options['description']])
            ->add('control', HiddenType::class, ['data' => $options['control']])
            ->add('api_version', HiddenType::class, ['data' => $options['api_version']])
            ->add('currency', HiddenType::class, ['data' => $options['currency']])
            ->add('lang', HiddenType::class, ['data' => $options['lang']])
            ->add('type', HiddenType::class, ['data' => $options['type']])
            ->add('URL', HiddenType::class, ['data' => $options['URL']])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'amount' => null,
            'id' => null,
            'description' => null,
            'control' => null,
            'api_version' => null,
            'currency' => null,
            'lang' => null,
            'type' => null,
            'URL' => null
        ]);
    }

    //We don't want any prefixes in inputs names
    public function getBlockPrefix()
    {
        return null;
    }
}
