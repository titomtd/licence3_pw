<?php

namespace App\Form;

use App\Entity\User;
use App\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('username')
            ->add('job', ChoiceType::class, [
                'choices' => [
                    'app.ui.searcher' => Job::SEARCHER,
                    'app.ui.student' => Job::STUDENT,
                    'app.ui.teacher' => Job::TEACHER,
                ],
            ])
            ->add('birthday', DateType::class, [
                'widget' => 'single_text'
            ])
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
