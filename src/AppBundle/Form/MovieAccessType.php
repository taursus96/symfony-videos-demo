<?php

namespace AppBundle\Form;

use AppBundle\Entity\User;
use AppBundle\Entity\MovieAccess;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Doctrine\ORM\EntityManager;

class MovieAccessType extends AbstractType
{
    /** EntityManager $em */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', TextType::class)
        ;

        $builder->get('user')->addModelTransformer(new CallbackTransformer(
            function (User $userAsEntity = null) {
                return $userAsEntity !== null ? $userAsEntity->getUsername() : null;
            },
            function (string $username = null) {
                return $this->em->getRepository('AppBundle:User')->findOneBy(['username' => $username]);
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MovieAccess::class
        ]);
    }
}
