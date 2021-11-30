<?php

namespace App\Form;

use App\Entity\Experience;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\User;

class ExperienceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
             ->add('note',IntegerType::class,[
                'label'=>'Note',
                'attr'=>[
                    'placeholder'=>"Veuillez notez le site",
                    'class'=>'note'
                ]
            ])
            ->add('avis',TextType::class)
            ->add('clientId',TextType::class,[
                'label'=>'Nom Prénom',
                'attr'=>[
                    'placeholder'=>"Choisir l'utilisateur",
                    'class'=>'clientId'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Experience::class,
        ]);
    }
}
