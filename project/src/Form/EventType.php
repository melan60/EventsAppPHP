<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255]),
                ]
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Callback([$this, 'validateDate']),
                ]
            ])
            ->add('participants_number', NumberType::class, [
                'label' => 'Nombre de participants',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new GreaterThan(0)
                ]
            ])
            ->add('public', CheckboxType::class, [
                'label' => 'Public',
                'required' => false,
            ])
            ->add('price', NumberType::class, [
                'label' => 'Prix',
                'required' => true,
                'scale' => 2,  // Si vous voulez des décimales
                'attr' => [
                    'min' => 1,
                    'step' => 1
                ],
                'empty_data' => 0
            ])
            ->add('add', SubmitType::class, [
                'label' => 'Valider',
                'attr' => ['class' => 'btn btn-primary btn-block']
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }

    public function validateDate($date, ExecutionContextInterface $context) : void {
        if ($date <= new \DateTime()) {
            $context->buildViolation('La date doit être dans le futur.')
                ->addViolation();
        }
    }
}
