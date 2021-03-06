<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Trick;
use App\Form\VideoType;
use App\Entity\Categorie;
use App\Form\VideoNewType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TrickType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'Nom du Trick',
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Description du Trick',
                    'class' => 'form-control mb-2'
                ]
            ])
            ->add('categorie', EntityType::class, [
                'label' => 'Choisir une catégorie:',
                'required' => true,
                'class' => Categorie::class,
                'choice_label' => 'name',
                'multiple' => false,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-control mb-2'
                ]
            ])
            //on ajoute le champ image dans le formulaire
            //il n'est pas lié à la base de données
            ->add('images', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => false,
                'multiple' => true
            ])
            ->add('videos', CollectionType::class, [
                'entry_type' => VideoType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'mapped' => false,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'attr' => ['class' => 'form-control'],
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider',
                'attr' => [
                    'class' => 'btn btn-primary btn-block'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Trick::class,
        ]);
    }
}
