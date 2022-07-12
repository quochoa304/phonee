<?php

namespace App\Form;

use App\Entity\Artist;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArtistType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('about')
            ->add('ImgUrl')
            ->add('Born')
            ->add('country',ChoiceType::class, [
                'choices'  => [
                    'Viet Nam' => 'Viet Nam',
                    'America' => 'America',
                    'Australia' => 'Australia',
                    'Unite Kingdom' => 'Unite Kingdom',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Artist::class,
        ]);
    }
}
