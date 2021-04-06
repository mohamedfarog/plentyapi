<?php

namespace App\Http\Controllers;

use App\Models\Timeslot;
use Illuminate\Http\Request;

class TimeslotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $timeslot = '';
        $msg = '';
        if (isset($request->id)) {
            $timeslot = Timeslot::where('id', $request->id)->first();
            switch ($request->action) {
                case 'delete':
                    $timeslot->delete();
                    $msg = 'Timeslot has been deleted';
                    return response()->json(['success' => !!$timeslot, 'message' => $msg]);
                    break;
                case 'update':
                    if (isset($request->timeslot)) {
                        $timeslot->timeslot = $request->timeslot;
                    }
                    if (isset($request->sched)) {
                        $timeslot->sched = $request->sched;
                    }
                    if (isset($request->bookings)) {
                        $timeslot->bookings = $request->bookings;
                    }
                    if (isset($request->available)) {
                        $timeslot->available = $request->available;
                    }
                    $msg = 'Timeslot has been updated';

                    $timeslot->save();
                    return response()->json(['success' => !!$timeslot, 'message' => $msg]);
                    break;
            }
        } else {
            $data = array();
            if (isset($request->timeslot)) {
                $data['timeslot'] = $request->timeslot;
            }
            if (isset($request->sched)) {
                $data['sched'] = $request->sched;
            }
            if (isset($request->bookings)) {
                $data['bookings'] = $request->bookings;
            }
            if (isset($request->available)) {
                $data['available'] = $request->available;
            }
            $timeslot = Timeslot::create($data);
            $msg = 'Timeslot has been added';
            return response()->json(['success' => !!$timeslot, 'message' => $msg]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Timeslot  $timeslot
     * @return \Illuminate\Http\Response
     */
    public function show(Timeslot $timeslot)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Timeslot  $timeslot
     * @return \Illuminate\Http\Response
     */
    public function edit(Timeslot $timeslot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Timeslot  $timeslot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Timeslot $timeslot)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Timeslot  $timeslot
     * @return \Illuminate\Http\Response
     */
    public function destroy(Timeslot $timeslot)
    {
        //
    }
}