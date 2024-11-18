<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('civility')
            ->add('maritalStatus')
            ->add('name')
            ->add('firstname')
            ->add('birthday')
            ->add('address')
            ->add('city')
            ->add('email')
            ->add('phone')
            ->add('account')
            ->add('cinFile', FileType::class, ['required' => true, 'mapped' => false, 'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'maxSizeMessage' => 'Veuillez téléverser un fichier inférieur à 2 Mo',
                    'mimeTypes' => ['image/png', 'image/jpeg', 'application/pdf'],
                    'mimeTypesMessage' => 'Veuillez téléverser un png, jpeg, pdf',
                ])
            ]])
            ->add('passportFile', FileType::class, ['required' => true, 'mapped' => false, 'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'maxSizeMessage' => 'Veuillez téléverser un fichier inférieur à 2 Mo',
                    'mimeTypes' => ['image/png', 'image/jpeg', 'application/pdf'],
                    'mimeTypesMessage' => 'Veuillez téléverser un png, jpeg, pdf',
                ])
            ]])
            ->add('ribFile', FileType::class, ['required' => true, 'mapped' => false, 'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'maxSizeMessage' => 'Veuillez téléverser un fichier inférieur à 2 Mo',
                    'mimeTypes' => ['image/png', 'image/jpeg', 'application/pdf'],
                    'mimeTypesMessage' => 'Veuillez téléverser un png, jpeg, pdf',
                ])
            ]])
            ->add('affiliationFile', FileType::class, ['required' => true, 'mapped' => false, 'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'maxSizeMessage' => 'Veuillez téléverser un fichier inférieur à 2 Mo',
                    'mimeTypes' => ['image/png', 'image/jpeg', 'application/pdf'],
                    'mimeTypesMessage' => 'Veuillez téléverser un png, jpeg, pdf',
                ])
            ]])
            ->add('ibanFile', FileType::class, ['required' => true, 'mapped' => false, 'constraints' => [
                new File([
                    'maxSize' => '2048k',
                    'maxSizeMessage' => 'Veuillez téléverser un fichier inférieur à 2 Mo',
                    'mimeTypes' => ['image/png', 'image/jpeg', 'application/pdf'],
                    'mimeTypesMessage' => 'Veuillez téléverser un png, jpeg, pdf',
                ])
            ]])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'allow_extra_fields' => true,
        ]);
    }
}
