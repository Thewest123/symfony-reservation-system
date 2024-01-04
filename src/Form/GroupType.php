<?php
namespace App\Form;

use App\Entity\User;
use App\Entity\Group;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('group_manager', EntityType::class, [
                'class' => User::class
            ])
            ->add('parent', EntityType::class, [
                'class' => Group::class,
                'choices' => $options['groups']
            ])
            ->add('submit', SubmitType::class, ['label' => 'Provést']);
    }
    
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['groups']);
    }
}