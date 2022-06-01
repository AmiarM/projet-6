<?php

namespace App\Form;

use App\Entity\Image;
use App\Entity\Trick;
use Doctrine\DBAL\Types\BooleanType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', FileType::class, [
                'label' => false,
                'data_class' => null,
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('isFirst', ChoiceType::class, [
                'label' => 'Is First',
                'choices' => [
                    'First' => 1,
                    'Not First' => 0
                ],
                'attr' => [
                    'class' => 'form-control'
                ],  'expanded' => true,
                'multiple' => false

            ])
            ->add('trick', EntityType::class, [
                'label' => 'Choose Trick:',
                'required' => true,
                'class' => Trick::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
