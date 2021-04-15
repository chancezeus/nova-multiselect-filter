<?php declare(strict_types=1);


namespace OptimistDigtal\NovaMultiselectFilter;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Nova\Filters\Filter;

abstract class MultiselectFilter extends Filter
{
    public $component = 'nova-multiselect-filter';

    /** @var bool */
    private bool $async = false;

    /** @var string|null */
    private ?string $key = null;

    public function __construct(string $name = null, string $key = null)
    {
        $this->name = $name;
        $this->key = $key ?? ($name ? Str::slug($name) : null);
    }

    /**
     * Apply the filter to the given query.
     *
     * @param Request $request
     * @param Builder $query
     * @param $value
     * @return Builder
     */
    public function apply(Request $request, $query, $value)
    {
        return $query;
    }

    /**
     * @param bool $async
     * @return $this
     */
    public function async(bool $async = true): self
    {
        $this->async = $async;

        return $this->withMeta(['async' => $async]);
    }

    /**
     * Get the filter's options.
     *
     * @param Request $request
     * @param array $filterValues
     * @return array
     */
    public function options(Request $request, array $filterValues = []): array
    {
        return [];
    }

    /**
     * Sets the placeholder value displayed on the field.
     *
     * @param string|null $placeholder
     * @return $this
     */
    public function placeholder(?string $placeholder): self
    {
        return $this->withMeta(['placeholder' => $placeholder]);
    }

    /**
     * Sets the max number of options the user can select.
     *
     * @param int|null $max
     * @return $this
     */
    public function max(?int $max): self
    {
        return $this->withMeta(['max' => $max]);
    }

    /**
     * Enables the field to be used as a single select.
     *
     * This forces the value saved to be a single value and not an array.
     *
     * @param bool $singleSelect
     * @return $this
     **/
    public function singleSelect(bool $singleSelect = true): self
    {
        return $this->withMeta(['singleSelect' => $singleSelect]);
    }

    /**
     * Sets the maximum number of options displayed at once.
     *
     * @param int|null $optionsLimit
     * @return $this
     */
    public function optionsLimit(?int $optionsLimit): self
    {
        return $this->withMeta(['optionsLimit' => $optionsLimit]);
    }

    /**
     * Enables vue-multiselect's group-select feature which allows the
     * user to select the whole group at once.
     *
     * @param bool $groupSelect
     * @return $this
     */
    public function groupSelect(bool $groupSelect = true): self
    {
        return $this->withMeta(['groupSelect' => $groupSelect]);
    }

    /**
     * Formats the options available for select.
     *
     * @param \Illuminate\Http\Request $request
     * @param array $filterValues
     * @return array
     */
    public function getFormattedOptions(Request $request, array $filterValues = []): array
    {
        $options = collect($this->options($request, $filterValues) ?? []);

        $isOptionGroup = $options->contains(fn($label) => is_array($label));

        if ($isOptionGroup) {
            return $options
                ->map(function ($value, $key) {
                    return collect($value + ['value' => $key]);
                })
                ->groupBy('group')
                ->map(function ($value, $key) {
                    return ['label' => $key, 'values' => $value->map->only(['label', 'value'])->toArray()];
                })
                ->values()
                ->toArray();
        }

        return $options->map(function ($label, $value) {
            return ['label' => $label, 'value' => $value];
        })->values()->all();
    }

    /**
     * Prepare the filter for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return array_merge([
            'class' => $this->key(),
            'name' => $this->name(),
            'component' => $this->component(),
            'options' => $this->async ? [] : $this->getFormattedOptions(app(Request::class)),
            'currentValue' => $this->default() ?? '',
        ], $this->meta());
    }

    /**
     * @return string
     */
    public function key(): string
    {
        return $this->key ?? parent::key();
    }

}
