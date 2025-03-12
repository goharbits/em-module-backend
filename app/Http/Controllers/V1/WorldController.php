<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;

class WorldController extends Controller
{
    public function get_countries()
    {
        try {
            DB::beginTransaction();

            $countries = Country::has('cities')->get(['id', 'name']);

            DB::commit();
            return sendSuccess('Countries fetched successfully', $countries);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }

    public function get_cities(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'country_id' => 'required|exists:countries,id',
        ]);

        if ($validator->fails()) {
            return sendError('Validation Errors', $validator->errors(), 422);
        }

        try {
            DB::beginTransaction();

            $cities = City::where('country_id', $request->country_id)->get(['id', 'name']);

            DB::commit();
            return sendSuccess('Cities fetched successfully', $cities);
        } catch (\Exception $e) {
            DB::rollBack();
            return sendError($e->getMessage(), null);
        }
    }
}
