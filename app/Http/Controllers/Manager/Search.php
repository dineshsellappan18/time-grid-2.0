<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\TG\SearchEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Timegridio\Concierge\Models\Business;

class Search extends Controller
{
    public function postSearch(Business $business, Request $request)
    {
        $this->authorize('manage', $business);

        $criteria = $request->input('criteria');

        Log::info('search.execute', [
            'actor' => auth()->id(),
            'resource' => 'search',
            'operation' => 'query',
            'context' => ['business_id' => $business->id, 'criteria_length' => strlen($criteria)],
        ]);

        $search = new SearchEngine($criteria, [$business->id]);
        $search->run();

        $results = $search->results();

        return view('manager.search.index')->with(compact('results', 'criteria'));
    }
}
