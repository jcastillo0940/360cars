<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Billing\PlanResource;
use App\Models\Plan;

class PlanController extends Controller
{
    public function index()
    {
        return PlanResource::collection(
            Plan::query()->where('is_active', true)->orderBy('priority_weight')->get()
        );
    }
}
