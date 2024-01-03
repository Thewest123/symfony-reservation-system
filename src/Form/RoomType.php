<?php
namespace App\Form;

use App\Entity\User;
use App\Entity\Building;
use App\Repository\BuildingRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomType extends AbstractType
{
    public function __construct(private readonly BuildingRepository $buildingRepository, )
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
            ->add('is_private', CheckboxType::class);
    }

    /*public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['user'])
            ->setAllowedTypes('user', User::class);
    }*/
}