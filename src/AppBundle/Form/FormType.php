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
            'textarea'          => Types\TextareaType::class,
            'number'            => Types\NumberType::class,
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

            if (!empty($json['maxlength']) || !empty($json['minlength'])) {
                $max = (!empty($json['maxlength']) ? $json['maxlength'] : null);
                $min = (!empty($json['minlength']) ? $json['minlength'] : null);

                $options['constraints'][] = new Assert\Length(['max' => $max, 'min' => $min]);
            }
            if (!empty($json['max']) || !empty($json['min'])) {
                $max = (!empty($json['max']) ? $json['max'] : null);
                $min = (!empty($json['min']) ? $json['min'] : null);

                $options['constraints'][] = new Assert\Range(['max' => $max, 'min' => $min]);
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
            if (!empty($json['value'])) {
                $options['data'] = isset($buildOptions['data']) ? $buildOptions['data'][$json['name']] : $json['value'];
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
//            'empty_data' => ['number-1524133264120' => 10],
            'formEntity' => new Form(),
        ));
    }
}
