<?php

namespace Modules\Cargo\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Cargo\Entities\State;
use Modules\Cargo\Http\Requests\StateRequest;
use Modules\Cargo\Entities\Country;

class StateController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        breadcrumb([
            [
                'name' => __('cargo::view.dashboard'),
                'path' => fr_route('admin.dashboard')
            ],
            [
                'name' => 'Add state',
            ]
        ]);
        $country_id = $id ;
        $adminTheme = env('ADMIN_THEME', 'adminLte');
        return view('cargo::'.$adminTheme.'.pages.states.create' , compact('country_id'));
    }

    /**
     * Show the form for store a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(StateRequest $request)
    {
        try {
            if (env('DEMO_MODE') == 'On') {
                return redirect()->route('countries.index')->with(['error_message_alert' => __('view.demo_mode')]);
            }
            State::create($request->validated());
            return redirect()->route('countries.index')->with(['message_alert' => __('cargo::messages.created')]);
        } catch (\Throwable $th) {
            return redirect()->route('countries.index')->with(['error_message_alert' => __('view.somthing_wrong')]);
        }
    }
}
