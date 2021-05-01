<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegisterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('name', TextType::class, array(
                    'label' => 'Nombre',
                    'attr' => array(
                        'class' => 'mb-3 form-name form-control'
                    )
                ))
                ->add('surname', TextType::class, array(
                    'label' => 'Apellido',
                    'attr' => array(
                        'class' => 'mb-3 form-surname form-control'
                    )
                ))
                ->add('nick', TextType::class, array(
                    'label' => 'Nick',
                    'attr' => array(
                        'class' => 'mb-3 form-nick form-control nick-input'
                    )
                ))
                ->add('email', EmailType::class, array(
                    'label' => 'Email',
                    'attr' => array(
                        'class' => 'mb-3 form-email form-control'
                    )
                ))
                ->add('password', RepeatedType::class, [
                    'type' => PasswordType::class,
                    'invalid_message' => 'Las contraseñas no coinciden!',
                    'options' => ['attr' => ['class' => 'mb-3 form-password form-control']],
                    'required' => true,
                    'first_options'  => ['label' => 'Contraseña'],
                    'second_options' => ['label' => 'Confirma la contraseña'],
                ])
                ->add('Registrarse', SubmitType::class, array(
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
