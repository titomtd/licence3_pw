<?php

namespace App\Form;

use App\Entity\User;
use App\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('email')
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
            ->add('website')
            ->add('github')
            ->add('twitter')
            ->add('instagram')
            ->add('facebook')
            ->add('pictureFileName', FileType::class,  [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/png',
                            'image/jpeg',
                        ],
                        'mimeTypesMessage' => 'app.ui.picture_error',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
