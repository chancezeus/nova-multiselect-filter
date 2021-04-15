<?php

namespace OptimistDigtal\NovaMultiselectFilter\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;

class FilterController extends Controller
{
    /**
     * @param \Laravel\Nova\Http\Requests\NovaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(NovaRequest $request): JsonResponse
    {
        $resource = $request->newResource();

        $filters = $resource->availableFilters($request);

        if (
            trait_exists(\DigitalCreative\MegaFilter\HasMegaFilterTrait::class) &&
            in_array(\DigitalCreative\MegaFilter\HasMegaFilterTrait::class, class_uses_recursive($resource))
        ) {
            $card = collect($resource->cards($request))
                ->whereInstanceOf(\DigitalCreative\MegaFilter\MegaFilter::class)
                ->first();

            if ($card) {
                $filters = $filters->merge($card->filters());
            }
        }

        $filter = $filters->first(fn(Filter $filter) => $filter->key() === $request->input('filter'));

        abort_if(!$filter, 404);

        return response()->json(
            $filter->getFormattedOptions(
                $request,
                json_decode(base64_decode($request->input('filters')), true)
            )
        );
    }
}
