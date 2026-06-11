<?php

declare(strict_types=1);

namespace App\Http\Controllers\Engineer;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Engineer/Dashboard');
    }
}
