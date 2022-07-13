<?php

namespace App\Form;

use App\Entity\Artist;
use App\Entity\Genre;
use App\Entity\Song;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SongType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('date')
            ->add('ImgUrl', FileType::class, [
                'label' => 'Avatar
                 (PDF file)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('MusicUrl', FileType::class, [
                'label' => 'Avatar
                 (Mp3 file)',
                'mapped' => false,
                'required' => false,
            ])
            ->add('artist',EntityType::class,[
                'class'=> Artist::class,
                'choice_label' => 'Name'
            ])
            ->add('Aname')
            ->add('category')
            ->add('Genre',EntityType::class,[
                'class'=> Genre::class,
                'choice_label' => 'Name'
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Song::class,
        ]);
    }
}
