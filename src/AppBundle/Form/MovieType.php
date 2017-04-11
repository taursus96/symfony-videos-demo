<?php

namespace AppBundle\Form;

use AppBundle\Entity\Movie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\CallbackTransformer;

class MovieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('access', ChoiceType::class, [
                'choices' => [
                    'movie.access.free' => Movie::ACCESS_FREE,
                    'movie.access.paid' => Movie::ACCESS_PAID,
                    'movie.access.private' => Movie::ACCESS_PRIVATE
                ],
                'choice_translation_domain' => 'movie_upload'
            ])
            ->add('price', NumberType::class, ['required' => false])
        ;

        if ($options['includeFile']) {
            $builder->add('file', FileType::class);
        }
        if ($options['includePreview']) {
            $builder->add('preview', FileType::class);
        }

        //We store price in database as integer using the lowest possible unit so we need to transform it to that unit
        $builder->get('price')->addModelTransformer(new CallbackTransformer(
            function (int $priceAsInt = null) {
                return $priceAsInt !== null ? $priceAsInt / 100 : null;
            },
            function (float $priceAsFloat = null) {
                //we need to use round before casting to int because of php's retarded float -> int casting which would return wrong values in certain cases
                return $priceAsFloat !== null ? (int)round($priceAsFloat * 100) : null;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'includeFile' => true,
            'includePreview' => true,
            'data_class' => Movie::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                $groups = ['Default'];

                if ($data->getAccess() === Movie::ACCESS_PAID) {
                    $groups[] = 'price_required';
                }
                if ($form->getConfig()->getOptions()['includeFile']) {
                    $groups[] = 'file_required';
                }
                if ($form->getConfig()->getOptions()['includePreview']) {
                    $groups[] = 'preview_required';
                }
                return $groups;
            },
        ]);
    }
}
