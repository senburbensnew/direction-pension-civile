<?php

namespace App\Http\Controllers;

use App\Enums\RequestEventTypeEnum;
use App\Enums\RequestTypeEnum;
use App\Helpers\CodeGeneratorService;
use App\Helpers\Helpers;
use App\Models\CivilStatus;
use App\Models\Gender;
use App\Models\PensionRequest;
use App\Models\RequestHistory;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InstitutionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->can('viewInstitutionMenu')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    

    public function demandeAdhesion()
    {
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        return view('institution.demande_adhesion', compact('genders', 'civilStatuses'));
    }

    public function processDemandeAdhesion(Request $request)
    {
        dd($request->all());
    }
}
