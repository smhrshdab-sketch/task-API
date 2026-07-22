<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEngageRequest;
use App\Http\Requests\UpdateEngageRequest;
use App\Models\Engage;
use App\Models\Membership;
use App\Models\Task;
use App\Services\EngageService;

class EngageController extends Controller{
    protected EngageService $engageService;

    public function __construct(EngageService $engageService){
        $this->engageService = $engageService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEngageRequest $request,Membership $membership,Task $task){
        //logger('You got EngageController:store (Membership,Task): ',[$membership,$task]);
        //$engaged = $engageService->
        return response()->json(['message' => 'EngageController:store is ready','Membership' => $membership,'Task' => $task]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Engage $engage)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Engage $engage)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEngageRequest $request, Engage $engage)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Engage $engage)
    {
        //
    }
}
