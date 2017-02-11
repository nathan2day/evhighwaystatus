<?php

namespace App\Http\Controllers;

use App\Handlers\AutoComplete;
use Illuminate\Http\Request;

class AutoCompleteController extends Controller
{
    /**
     * Get auto-complete predictions from the given string.
     *
     * @param AutoComplete $autoComplete
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function query(AutoComplete $autoComplete, Request $request)
    {
        return response()->json($autoComplete->query(
            $request->input('input'),
            $request->input('location'),
            $request->input('radius')
        ));
    }

}
