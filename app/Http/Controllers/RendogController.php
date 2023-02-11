<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RendogController extends Controller
{
    public function thankyou()
    {
        Gate::authorize("rendog");

        return Inertia::render("Rendog/Thankyou");
    }
}
