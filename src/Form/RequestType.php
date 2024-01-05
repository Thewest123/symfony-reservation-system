<?php

namespace App\Form;

use App\Entity\Request;
use App\Entity\Room;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $request = $options['request'];
        //$occupants = $options['request']->getRequestedRoom()?->getOccupants() ?? [];

        $builder
            ->add(
                'date',
                DateTimeType::class, [
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'required' => true,
                ]
            )
            ->add(
                'endDate',
                DateTimeType::class, [
                    'widget' => 'single_text',
                    'input' => 'datetime',
                    'required' => true,
                ]
            )
            ->add('requestedRoom', EntityType::class, [
                'class' => Room::class,
                'choices' => $options['allowed_rooms']
            ]);
            

        if ($options['can_approve'])
            $builder->add('approved', CheckboxType::class,
                [
                    'label' => 'Schváleno',
                    'required' => false,
                ])
                ->add('attendees', EntityType::class, [
                    'class' => User::class,
                    'multiple' => true,
                    'expanded' => false,
                    'attr' => [
                        'size' => 10,
                    ],
                ]);

        $builder->add('submit', SubmitType::class, ['label' => 'Provést']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['request', 'allowed_rooms', 'can_approve'])
            ->setAllowedTypes('request', Request::class);
    }
}