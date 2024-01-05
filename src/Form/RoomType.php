<?php

namespace App\Form;

use App\Entity\Building;
use App\Entity\Group;
use App\Entity\Room;
use App\Entity\User;
use App\Repository\BuildingRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function __construct(private readonly BuildingRepository $buildingRepository,)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('building', EntityType::class, [
                'class' => Building::class,
                #'choices' => $this->buildingRepository->findAll()
            ])
            ->add('room_manager', EntityType::class, [
                'class' => User::class,
                #'choices' => $options['user']
            ])
            ->add('belongs_to', EntityType::class, [
                'class' => Group::class,
                'required' => false,
            ])
            ->add('occupants', EntityType::class, [
                'class' => User::class,
                'multiple' => true,
                'required' => false,
                'by_reference' => false,
            ])
            ->add('is_private', CheckboxType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Provést']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => Room::class,
            ]);
    }
}