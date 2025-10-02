<?php

namespace LemonLabs\SelectableKeyValue;

use Illuminate\Support\Arr;
use Laravel\Nova\Contracts\FilterableField;
use Laravel\Nova\Exceptions\NovaException;
use Laravel\Nova\Fields\FieldFilterable;
use Laravel\Nova\Fields\Filters\SelectFilter;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Searchable;
use Laravel\Nova\Fields\SupportsDependentFields;
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
     * Indicates if the field is used to manipulate JSON.
     *
     * @var bool
     */
    public $json = false;

    /**
     * The JSON encoding options.
     *
     * @var int|null
     */
    public $jsonOptions = null;

    /**
     * The field's keys callback.
     *
     * @var iterable<string|int, array<string, mixed>|string>|callable|class-string<\BackedEnum>|null
     *
     * @phpstan-var TKeyOption|(callable(): (TKeyOption))|null
     */
    public $keyOptionsCallback;

    /**
     * Create a new field.
     *
     * @param  string  $name
     * @param  string|\Closure|callable|object|null  $attribute
     * @param  callable|null  $resolveCallback
     * @return void
     */
    public function __construct($name, $attribute = null, ?callable $resolveCallback = null)
    {
        parent::__construct($name, $attribute, $resolveCallback);

        $this->withMeta([
            'options' => []
        ]);
    }

    /**
     * Set the key options for the select menu.
     *
     * @param  iterable<string|int, array<string, mixed>|string>|callable|class-string<\BackedEnum>  $keyOptions
     * @return $this
     *
     * @phpstan-param TKeyOption|(callable(): (TKeyOption)) $keyOptions
     */
    public function keyOptions(iterable|callable|string $keyOptions)
    {
        $this->keyOptionsCallback = $keyOptions;

        return $this;
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
     * Serialize key options for the field.
     *
     * @return array<int, array<string, mixed>>
     *
     * @phpstan-return array<int, array{group?: string, label: string, value: TKeyOptionValue}>
     */
    protected function serializeKeyOptions(bool $searchable): array
    {
        /** @var TKeyOption $keyOptions */
        $keyOptions = value($this->keyOptionsCallback);

        if (\is_string($keyOptions) && enum_exists($keyOptions)) {
            /** @var class-string<\BackedEnum> $keyOptions */
            return collect($keyOptions::cases())
                ->map(static fn ($keyOption) => [
                    'label' => Nova::humanize($keyOption),
                    'value' => $keyOption->value,
                ])->all();
        }

        if (\is_callable($keyOptions)) {
            $keyOptions = $keyOptions();
        }

        return collect($keyOptions ?? [])->map(static function ($label, $value) use ($searchable) {
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
            'keyOptions' => $this->serializeKeyOptions($searchable = $this->isSearchable(app(NovaRequest::class))),
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

            $keyOptions = collect($this->serializeKeyOptions(false))->pluck('label', 'value')->all();

            if (!is_array($value)) {
                return $keyOptions[$value] ?? $value;
            }

            return collect($value)->map(function ($fieldValue, $key) use ($keyOptions) {
                return [
                    $keyOptions[$key] ?? $key => $fieldValue,
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
     * @return $this
     */
    public function placeholder(string|\Stringable|null $text)
    {
        return $this->withMeta(['placeholder' => $text]);
    }

    /**
     * Resolve the given attribute from the given resource.
     *
     * @param  \Laravel\Nova\Resource|\Illuminate\Database\Eloquent\Model|object  $resource
     */
    #[\Override]
    protected function resolveAttribute($resource, string $attribute): mixed
    {
        $value = parent::resolveAttribute($resource, $attribute);

        if ($this->json) {
            return json_decode($value, $this->jsonOptions ?? JSON_PRETTY_PRINT);
        }

        return $value;
    }

    protected function parseJsonValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $lower = strtolower($value);

            if ($lower === 'true') {
                return true;
            } elseif ($lower === 'false') {
                return false;
            } elseif (is_numeric($value)) {
                return strpos($value, '.') !== false ? (float) $value : (int) $value;
            }
        }

        return $value;
    }

    protected function fillAttribute(NovaRequest $request, string $requestAttribute, object $model, string $attribute): void
    {
        if (!$request->exists($requestAttribute)) {
            return;
        }

        $value = $request->input($requestAttribute);

        if ($this->json) {
            if (empty($value)) {
                $model->{$attribute} = null;

                return;
            }

            $data = is_string($value) ? json_decode($value, true) : $value;
            $parsed = [];

            foreach ((array) $data as $key => $val) {
                $parsed[$key] = $this->parseJsonValue($val);
            }

            $value = $parsed;
        }

        $model->{$attribute} = $value;
    }

    /**
     * Indicate that the code field is used to manipulate JSON.
     *
     * @return $this
     */
    public function json(?int $options = null)
    {
        $this->json = true;

        $this->jsonOptions = $options ?? JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE;

        return $this->options(['mode' => 'application/json']);
    }
}
