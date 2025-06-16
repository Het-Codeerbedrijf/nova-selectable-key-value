<?php

namespace NowakAdmin\SelectableKeyValue;

use Laravel\Nova\Fields\KeyValue;

class SelectableKeyValue extends KeyValue
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'selectable-key-value';

    /**
     * Set the options for the select field.
     *
     * @param array $options
     * @return $this
     */
    public function options(array $options)
    {
        // Convert sequential array to associative if needed
        if (array_keys($options) === range(0, count($options) - 1)) {
            $options = array_combine($options, $options);
        }

        return $this->withMeta(['options' => $options]);
    }
}
