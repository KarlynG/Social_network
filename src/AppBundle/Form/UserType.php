<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
                    'label' => 'Nombre',
                    'required' => true,
                    'attr' => array(
                        'class' => 'mb-3 form-name form-control'
                    ),
                ))
                ->add('surname', TextType::class, array(
                    'label' => 'Apellido',
                    'required' => true,
                    'attr' => array(
                        'class' => 'mb-3 form-surname form-control'
                    ),
                ))
                ->add('nick', TextType::class, array(
                    'label' => 'Nick',
                    'required' => true,
                    'attr' => array(
                        'class' => 'mb-3 form-nick form-control'
                    ),
                ))
                ->add('email', EmailType::class, array(
                    'label' => 'Email',
                    'required' => true,
                    'attr' => array(
                        'class' => 'mb-3 form-email form-control'
                    ),
                ))
                ->add('bio', TextareaType::class, array(
                    'label' => 'Bio',
                    'required' => false,
                    'attr' => array(
                        'class' => 'mb-3 form-bio form-control'
                    ),
                ))
                ->add('image', FileType::class, array(
                    'label' => 'Photo',
                    'required' => false,
                    'data_class' => null,
                    'attr' => array(
                        'class' => 'mb-3 form-image form-control'
                    ),
                ))
                ->add('Guardar cambios', SubmitType::class, array(
                    "attr" => array(
                        "class" => "form-submit btn btn-success"
                    )
                ));
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'MainBundle\Entity\User'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'mainbundle_user';
    }


}
