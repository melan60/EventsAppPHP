<?php

// src/Form/RegistrationFormType.php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez votre nom',
                    ]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez votre prénom',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Entrez un email',
                    ]),
                    new Email(['message' => '"{{ value }}" est invalide.']),
                    new Regex([
                        'pattern' => "/^[\w.-]+@[a-zA-Z\d.-]+\.[a-zA-Z]{2,6}$/",
                        'message' => 'Ce champ doit être au format xxx@yyy.zz',
                    ])
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'required' => true,
                'first_options'  => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Entrez votre mot de passe',
                        ]),
                        new Length([
                            'min' => 8,
                            'minMessage' => 'Votre mot de passe doit au moins faire {{ limit }} caractères',
                            // max length allowed by Symfony for security reasons
                            'max' => 4096,
                        ]),
                        new Regex([
                            'pattern' => "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/",
                            'message' => 'Votre mot de passe doit contenir au moins une lettre et un chiffre',
                        ]),
                    ],
                    'label' => 'Mot de passe'
                ],
                'second_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'Confirmez votre mot de passe',
                        ]),
                    ],
                    'label' => 'Confirmez votre mot de passe',
                ],
                'invalid_message' => 'Les champs de mot de passe doivent correspondre.',
            ])
            ->add('register', SubmitType::class, [
                'label' => 'Inscription',
                'attr' => ['class' => 'btn btn-primary btn-block']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}

