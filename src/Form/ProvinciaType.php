<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\{TextType, SubmitType};

class ProvinciaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("nombre", TextType::class, array("label" => "Nombre: "))
            ->add("save", SubmitType::class, array("label" => "Enviar"));
    }
}
?>