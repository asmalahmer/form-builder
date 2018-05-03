<?php

namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_SimpleFunction;


class BuilderExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $availableBuilderFields;

    /**
     * @var array
     */
    private $allowedTypes;

    /**
     * @var array
     */
    private $disabledBuilderFields;

    /**
     * BuilderService constructor.
     *
     * @param array $availableBuilderFields
     * @param array $allowedTypes
     * @param array $disabledBuilderFields
     */
    public function __construct($availableBuilderFields, $allowedTypes, $disabledBuilderFields)
    {
        $this->availableBuilderFields = $availableBuilderFields;
        $this->allowedTypes = $allowedTypes;
        $this->disabledBuilderFields = $disabledBuilderFields;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('filterJson', array($this, 'filterJson')),
        );
    }
    public function getFunctions()
    {
        return array(
            new Twig_SimpleFunction('getDisabledFields', array($this, 'getDisabledFields')),
        );
    }

    /**
     * Filters the not allowed fields out
     *
     * @param   array $json
     * @return  array
     */
    public function filterJson($json)
    {
        $filteredJson = [];

        foreach ($json as $field) {
            if (isset($field['subtype']) && in_array($field['subtype'], $this->allowedTypes)) {
                $filteredJson[] = $field;
            } elseif (isset($field['type']) && in_array($field['type'], $this->allowedTypes)) {
                $filteredJson[] = $field;
            }
        }

        return $filteredJson;
    }

    /**
     * Returns the disabled fields for the form builder
     *
     * @return string
     */
    public function getDisabledFields()
    {
        $disabledFields = array_diff($this->availableBuilderFields, $this->allowedTypes);
        $disabledFields = array_merge($this->disabledBuilderFields, $disabledFields);

        return json_encode($disabledFields);
    }

}
