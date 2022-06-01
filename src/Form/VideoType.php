<?php

namespace App\Form;

use App\Entity\Trick;
use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class VideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('url', UrlType::class, [
                'label' => 'Url',
                'required' => false,
                'attr' => [
                    'placeholder' => 'URL de la vidéo',
                    'class' => 'form-control'
                ],
            ]);
        // ->add('trick', EntityType::class, [
        //     'label' => 'Choose Trick:',
        //     'required' => true,
        //     'class' => Trick::class,
        //     'choice_label' => 'name',
        //     'multiple' => false,
        //     'expanded' => false,
        //     'attr' => [
        //         'class' => 'form-control mb-2'
        //     ]
        // ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
        ]);
    }
}
