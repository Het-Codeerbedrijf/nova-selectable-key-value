<?php

namespace Nowakadmin\SelectableKeyValue;

use Laravel\Nova\Fields\KeyValue;

class SelectableKeyValue extends KeyValue
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'selectable-key-value';

    public function options(array $options)
    {
        return $this->withMeta(['options' => $options]);
    }
}
