<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;

class PeopleApiController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');
        
        if (!$query || strlen($query) < 1) {
            return response()->json([]);
        }

        $people = Person::where('name', 'like', '%' . $query . '%')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name']);

        return response()->json($people);
    }
}
