<?php

namespace App\Http\Controllers;

use App\Support\SmartClassroom;
use Illuminate\View\View;

class ArchitectureController extends Controller
{
    public function index(): View
    {
        return view('architecture.index', [
            'layers' => SmartClassroom::layers(),
            'modules' => SmartClassroom::modulesForUser(auth()->user()),
            'flow' => config('smart_classroom.flow', []),
        ]);
    }
}
