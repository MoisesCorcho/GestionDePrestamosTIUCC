<?php

namespace App\Observers;

use Carbon\Carbon;
use App\Models\Request;
use App\Models\RequestLog;

class RequestObserver
{
    /**
     * Handle the Request "created" event.
     */
    public function created(Request $request): void
    {
        RequestLog::create([
            'request_id' => $request->id,
            'estado' => $request->estado,
            'fecha_cambio' => Carbon::now(),
        ]);
    }

    /**
     * Handle the Request "updated" event.
     */
    public function updated(Request $request): void
    {

        RequestLog::create([
            'request_id' => $request->id,
            'estado' => $request->estado,
            'fecha_cambio' => Carbon::now(),
        ]);
    }

    /**
     * Handle the Request "deleted" event.
     */
    public function deleted(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "restored" event.
     */
    public function restored(Request $request): void
    {
        //
    }

    /**
     * Handle the Request "force deleted" event.
     */
    public function forceDeleted(Request $request): void
    {
        //
    }
}
