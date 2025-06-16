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
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|\Closure|callable|object|null  $attribute
     * @param  callable|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta([
            'options' => [],
            'keyLabel' => 'Key',
            'valueLabel' => 'Value',
        ]);
    }

    /**
     * Set the options for the select field.
     *
     * @param  array  $options
     * @return $this
     */
    public function options($options)
    {
        // Convert sequential array to associative if needed
        if (is_array($options) && array_keys($options) === range(0, count($options) - 1)) {
            $options = array_combine($options, $options);
        }

        return $this->withMeta(['options' => $options]);
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_merge(parent::jsonSerialize(), [
            'options' => $this->meta['options'] ?? [],
            'keyLabel' => $this->meta['keyLabel'] ?? 'Key',
            'valueLabel' => $this->meta['valueLabel'] ?? 'Value',
        ]);
    }
}
