<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', EmailType::class, [
                'label' => 'entity.sender',
                'attr' => [
                    'class' => 'form-control col-6',
                    'title' => 'entity.sender',
                    'disabled' => true,
                ]
            ])
            ->add('to', EmailType::class, [
                'label' => 'entity.recipient',
                'attr' => [
                    'class' => 'form-control col-6',
                    'title' => 'entity.recipient',
                    'required'   => true,
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'entity.object',
                'attr' => [
                    'class' => 'form-control col-6',
                    'title' => 'entity.object',
                    'disabled' => true,
                ]
            ])

            ->add('description', TextareaType::class, [
                'label' => 'entity.message',
                'attr' => [
                    'class' => 'form-control col-6',
                    'title' => 'entity.message',
                    'disabled' => true,
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'button.send',
                'attr' => [
                    'class' => 'btn btn-primary col-2 mt-3',
                    'title' => 'button.send',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
