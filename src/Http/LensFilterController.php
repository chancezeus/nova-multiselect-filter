<?php

namespace OptimistDigtal\NovaMultiselectFilter\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\LensRequest;

class LensFilterController extends Controller
{
    /**
     * @param \Laravel\Nova\Http\Requests\LensRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(LensRequest $request): JsonResponse
    {
        $filter = $request->lens()
            ->availableFilters($request)
            ->first(fn(Filter $filter) => $filter->key() === $request->input('filter'));

        abort_if(!$filter, 404);

        return response()->json(
            $filter->getOptions(
                $request,
                json_decode(base64_decode($request->input('filters')), true)
            )
        );
    }
}
