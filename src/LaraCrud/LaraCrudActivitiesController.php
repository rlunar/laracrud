<?php

namespace LaraCrud;

use Auth;
use App\User;
use App\Http\Controllers\Controller;

class LaraCrudActivitiesController extends Controller
{
    /**
     * Show User Activity
     * 
     * @return view
     */
    public function show()
    {
        $user     = Auth::user();
        $activity = $user->activity;

        if (isset($this->views)) {
            return view($this->views . '.activity', compact('activity'));
        } else {
            return view('lara_crud::activity.show', compact('activity'));
        }
    }
}
