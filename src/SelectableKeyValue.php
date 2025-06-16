<?php

namespace NowakAdmin\SelectableKeyValue;

use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\FieldFilterable;
use Laravel\Nova\Fields\SupportsDependentFields;
use Laravel\Nova\Fields\Searchable;
use Illuminate\Support\Arr;
use Laravel\Nova\Contracts\FilterableField;
use Laravel\Nova\Exceptions\NovaException;
use Laravel\Nova\Fields\Filters\SelectFilter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Stringable;

use function Orchestra\Sidekick\Http\safe_int;

class SelectableKeyValue extends KeyValue implements FilterableField
{
    use FieldFilterable;
    use Searchable;
    use SupportsDependentFields;

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'selectable-key-value';

    /**
     * The field's options callback.
     *
     * @var iterable<string|int, array<string, mixed>|string>|callable|class-string<\BackedEnum>|null
     *
     * @phpstan-var TOption|(callable(): (TOption))|null
     */
    public $optionsCallback;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|\Closure|callable|object|null  $attribute
     * @param  callable|null  $resolveCallback
     * @return void
     */

     /**
     * Set the options for the select menu.
     *
     * @param  iterable<string|int, array<string, mixed>|string>|callable|class-string<\BackedEnum>  $options
     * @return $this
     *
     * @phpstan-param TOption|(callable(): (TOption)) $options
     */
    public function options(iterable|callable|string $options)
    {
        $this->optionsCallback = $options;

        return $this;
    }
    /*public function __construct($name, $attribute = null, callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta([
            'options' => [],
            'keyLabel' => 'Key',
            'valueLabel' => 'Value',
        ]);
    }*/

    /**
     * Set the options for the select field.
     *
     * @param  array  $options
     * @return $this
     */
   /* public function options($options)
    {
        // Convert sequential array to associative if needed
        if (is_array($options) && array_keys($options) === range(0, count($options) - 1)) {
            $options = array_combine($options, $options);
        }

        return $this->withMeta(['options' => $options]);
    }*/

    /**
     * Enable subtitles within the related search results.
     *
     * @return $this
     *
     * @throws \Laravel\Nova\Exceptions\HelperNotSupported
     */
    public function withSubtitles()
    {
        throw NovaException::helperNotSupported(__METHOD__, __CLASS__);
    }

    /**
     * Make the field filter.
     *
     * @return \Laravel\Nova\Fields\Filters\Filter
     */
    protected function makeFilter(NovaRequest $request)
    {
        return new SelectFilter($this);
    }

    /**
     * Prepare the field for JSON serialization.
     */
    public function serializeForFilter(): array
    {
        return transform($this->jsonSerialize(), static fn ($field) => Arr::only($field, [
            'uniqueKey',
            'name',
            'attribute',
            'options',
            'searchable',
        ]));
    }

    /**
     * Serialize options for the field.
     *
     * @return array<int, array<string, mixed>>
     *
     * @phpstan-return array<int, array{group?: string, label: string, value: TOptionValue}>
     */
    protected function serializeOptions(bool $searchable): array
    {
        /** @var TOption $options */
        $options = value($this->optionsCallback);

        if (\is_string($options) && enum_exists($options)) {
            /** @var class-string<\BackedEnum> $options */
            return collect($options::cases())
                ->map(static fn ($option) => [
                    'label' => Nova::humanize($option),
                    'value' => $option->value,
                ])->all();
        }

        if (\is_callable($options)) {
            $options = $options();
        }

        return collect($options ?? [])->map(static function ($label, $value) use ($searchable) {
            $label = $label instanceof Stringable ? (string) $label : $label;
            $value = safe_int($value);

            if ($searchable && isset($label['group'])) {
                return [
                    'label' => $label['group'].' - '.$label['label'],
                    'value' => $value,
                ];
            }

            return \is_array($label) ? $label + ['value' => $value] : ['label' => $label, 'value' => $value];
        })->values()->all();
    }

    /**
     * Prepare the field for JSON serialization.
     *
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $this->withMeta([
            'options' => $this->serializeOptions($searchable = $this->isSearchable(app(NovaRequest::class))),
            'keyLabel' => $this->keyLabel ?? Nova::__('Key'),
            'valueLabel' => $this->valueLabel ?? Nova::__('Value'),
            'actionText' => $this->actionText ?? Nova::__('Add row'),
            'placeholder' => $this->placeholder ?? Nova::__('Select a key'),
            'readonlyKeys' => $this->readonlyKeys(app(NovaRequest::class)),
            'canAddRow' => $this->canAddRow,
            'canDeleteRow' => $this->canDeleteRow,
        ]);

        return array_merge(parent::jsonSerialize(), [
            'searchable' => $searchable,
        ]);
    }

    /**
     * Display values using their corresponding specified labels.
     *
     * @return $this
     */
    /**
     * Display values using their corresponding specified labels.
     *
     * @return $this
     */
    public function displayUsingLabels()
    {
        return $this->resolveUsing(function ($value) {
            if (is_null($value) || $this->isValidNullValue($value)) {
                return $value;
            }

            $options = collect($this->serializeOptions(false))->pluck('label', 'value')->all();
            
            if (!is_array($value)) {
                return $options[$value] ?? $value;
            }

            return collect($value)->map(function ($fieldValue, $key) use ($options) {
                return [
                    $options[$key] ?? $key => $fieldValue
                ];
            })->collapse()->all();
        })->displayUsing(function ($value) {
            if (is_null($value) || $this->isValidNullValue($value)) {
                return $value;
            }

            return collect($value)->map(function ($v, $k) {
                return "$k: $v";
            })->join(', ');
        });
    }

    /**
     * Set the placeholder text for the field.
     *
     * @param  string|\Stringable|null  $text
     * @return $this
     */
    public function placeholder(string|\Stringable|null $text)
    {
        return $this->withMeta(['placeholder' => $text]);
    }
}
