<?php

namespace App\Forms;

use App\Entity\Group;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUserToGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('group', EntityType::class, [
                'class' => Group::class,
                'choice_label' => 'name',
                'label' => 'Vyberte skupinu',
                // Dont show groups that user is already part of
                'query_builder' => fn(EntityRepository $er) => $er->createQueryBuilder('g')
                    ->where(':user NOT MEMBER OF g.users')
                    ->setParameter('user', $options['user']),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Přidat',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
        ]);
    }
}
