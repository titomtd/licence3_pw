<?php

namespace App\Form;

use App\Entity\PostComment;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostCommentFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', CKEditorType::class, [
                'config' => [
                    'toolbar' => 'my_toolbar_2',
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
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PostComment::class,
        ]);
    }
}
