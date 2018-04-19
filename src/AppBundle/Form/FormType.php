<?php

namespace AppBundle\Form;

use AppBundle\Entity\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as Types;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $buildOptions)
    {
        $typeMapping = [
            'text'              => Types\TextType::class,     // subtype
            'email'             => Types\EmailType::class,    // subtype
            'select'            => Types\ChoiceType::class,
            'checkbox-group'    => Types\ChoiceType::class,
            'radio-group'       => Types\ChoiceType::class,
            'button'            => Types\ButtonType::class,   // subtype
            'submit'            => Types\SubmitType::class,   // subtype
            'reset'             => Types\ResetType::class,    // subtype
        ];

        foreach ($buildOptions['formEntity']->getJson() as $json) {
            if (isset($json['subtype']) && isset($typeMapping[$json['subtype']])) {
                $formType = $typeMapping[$json['subtype']];
            } elseif (isset($typeMapping[$json['type']])) {
                $formType = $typeMapping[$json['type']];
            } else {
                continue;
            }

            $options = [];
            $options['label'] = $json['label'];

            switch ($formType) {
                case Types\EmailType::class:
                    $options['constraints'][] = new Assert\Email();
                    break;
                case Types\ChoiceType::class:
                    foreach ($json['values'] as $choice) {
                        $options['choices'][$choice['label']] = $choice['value'];
                    }

                    if ($json['type'] == 'checkbox-group' || $json['type'] == 'radio-group') {
                        $options['expanded'] = true;
                    }

                    $options['multiple'] = false;
                    if (!empty($json['multiple']) || $json['type'] == 'checkbox-group') {
                        $options['multiple'] = true;
                    }

                    $options['constraints'][] = new Assert\Choice([
                        'choices'   => array_values($options['choices']),
                        'multiple'  => $options['multiple'],
                    ]);

                    break;
            }

            $builder->add($json['name'], $formType, $options);

            $field = $builder->get($json['name']);

            if (!empty($json['maxlength'])) {
                $options['constraints'][] = new Assert\Length(['max' => $json['maxlength']]);
            }
            if ($field->hasOption('required')) {
                $options['required'] = false;
                if (!empty($json['required'])) {
                    $options['required'] = true;
                    $options['constraints'][] = new Assert\NotBlank();
                }
            }
            if (!empty($json['className'])) {
                $options['attr'] = [
                    'class' => $json['className']
                ];
            }
            if (!empty($json['placeholder'])) {
                if ($field->hasOption('placeholder')) {
                    $options['placeholder'] = $json['placeholder'];
                } else {
                    $options['attr'] = [
                        'placeholder' => $json['placeholder']
                    ];
                }
            }

            $builder->add($json['name'], $formType, $options);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
//            'empty_data' => ['text-1523427016520' => 'test','checkbox-group-1523426964534'],
            'formEntity' => new Form(),
        ));
    }
}
