<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class RendogController extends Controller
{
    public function thankyou(): Response
    {
        Gate::authorize('rendog');

        return Inertia::render('Rendog/Thankyou');
    }
}
