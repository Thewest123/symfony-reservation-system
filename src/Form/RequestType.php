<?php
namespace App\Form;

use App\Entity\User;
use App\Entity\Request;
use App\Entity\Room;
use App\Form\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $occupants = $options['request']->getRequestedRoom()->getOccupants();

        $builder
            ->add(
                'date',
                DateTimeType::class, [
                    'widget' => 'single_text',
                    'input' => 'datetime_immutable',
                    'required' => true,
                ]
            )
            ->add('room', EntityType::class, [
                'class' => Room::class,
                'choices' => $options['request']->getAuthor()->getRooms()
            ])
            ->add('attendees', EntityType::class, [
                'class' => User::class,
                'choices' => $occupants,
                'multiple' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['request'])
            ->setAllowedTypes('request', Request::class);

        /*$resolver
            ->setRequired(['user'])
            ->setAllowedTypes('user', User::class);*/
    }
}