<?php

namespace App\Form;

use App\Entity\Post;
use App\Languages;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('content', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'my_toolbar_1',
                    'required' => 'true',
                    'extraPlugins' => 'codesnippet',
                ],
                'plugins' => [
                    'codesnippet' => [
                        'path' => '/bundles/fosckeditor/plugins/codesnippet/',
                        'filename' => 'plugin.js',
                    ],
                ],
            ])
            ->add('language', ChoiceType::class, [
                'choices' => [
                    'Java' => Languages::JAVA,
                    'PHP' => Languages::PHP,
                    'Python' => Languages::PYTHON,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
