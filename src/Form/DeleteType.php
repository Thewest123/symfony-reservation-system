<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeleteType extends AbstractType
{
    /**
     * @param array{'entity': string, 'name': string} $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('button', SubmitType::class, [
                'label' => 'Delete',
                'attr' => [
                    'onClick' => "return confirm(\"Are you sure you want to delete {$options['entity']}'?\")",
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired('entity')
            ->setAllowedTypes('entity', 'string');
    }
}
