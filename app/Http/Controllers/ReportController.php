<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index','show','download']);
        // pour les actions d'administration, tu peux utiliser middleware role:admin
    }

    // page publique listant les rapports publiés
    public function index(Request $request)
    {
        $query = Report::query()->where('status','published')->orderBy('year','desc')->orderBy('created_at','desc');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($s) use ($q){
                $s->where('title','like',"%{$q}%")
                  ->orWhere('description','like',"%{$q}%")
                  ->orWhere('year',$q);
            });
        }

        $reports = $query->paginate(12);
        return view('reports.index', compact('reports'));
    }

    // dashboard (admin) listing tout, y compris drafts
    public function adminIndex()
    {
        $this->authorize('viewAny', Report::class);
        $reports = Report::orderBy('year','desc')->paginate(20);
        return view('reports.admin.index', compact('reports'));
    }

public function create(Request $request)
{
    $query = Report::query();

    if ($request->filled('q')) {
        $query->where('title', 'like', "%{$request->q}%")
              ->orWhere('year', 'like', "%{$request->q}%");
    }

    $reports = $query->orderBy('created_at', 'desc')->paginate(10);

    return view('reports.create', compact('reports'));
}

    public function store(StoreReportRequest $request)
    {


        $this->authorize('create', Report::class);

        $file = $request->file('file');

        $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
        $path = $file->storeAs('reports/' . ($request->year ?? 'unspecified'), $filename, 'public');

        $report = Report::create([
            'title' => $request->title,
            'year' => $request->year,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => $request->status ?? 'draft',
            'published_at' => $request->status === 'published' ? Carbon::now() : null,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('reports.create')->with('success','Rapport créé avec succès.');
    }

    public function show(Report $report)
    {
        // if draft: only author or admin
        if ($report->status === 'draft') {
            $this->authorize('view', $report);
        }
        return view('reports.show', compact('report'));
    }

public function edit(Request $request, Report $report)
{

    // Vérifie si l'utilisateur est autorisé à mettre à jour ce rapport
    $this->authorize('update', $report);

    // Prépare la requête pour récupérer les rapports
    $query = Report::query();

    // Filtre si une recherche est effectuée
    if ($request->filled('q')) {
        $query->where('title', 'like', "%{$request->q}%")
              ->orWhere('year', 'like', "%{$request->q}%");
    }

    // Pagination des rapports, ordonnés par date de création
    $reports = $query->orderBy('created_at', 'desc')->paginate(10);

    // Retourne la vue de formulaire pour éditer, en passant le rapport et la liste
    return view('reports.create', [
        'reportEdit' => $report,
        'reports' => $reports,
        'searchQuery' => $request->q ?? ''
    ]);
}



    public function update(StoreReportRequest $request, Report $report)
    {

        $this->authorize('update', $report);

        if ($request->hasFile('file')) {
            // delete old
            Storage::disk('public')->delete($report->file_path);

            $file = $request->file('file');
            $filename = time() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $path = $file->storeAs('reports/' . ($request->year ?? 'unspecified'), $filename, 'public');

            $report->fill([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        $report->title = $request->title;
        $report->year = $request->year;
        $report->description = $request->description;
        $report->status = $request->status ?? $report->status;
        $report->published_at = $report->status === 'published' && !$report->published_at ? Carbon::now() : $report->published_at;
        $report->save();

        return redirect()->route('reports.create')->with('success','Rapport mis à jour.');
    }

    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        Storage::disk('public')->delete($report->file_path);
        $report->delete();

        return back()->with('success','Rapport supprimé.');
    }

    // Télécharger (stream)
    public function download(Report $report)
    {
        if ($report->status === 'draft') {
            $this->authorize('view', $report);
        }

        $disk = Storage::disk('public');
        $path = $report->file_path;

        if (!$disk->exists($path)) {
            abort(404);
        }

        return $disk->download($path, $report->file_name);
    }

    // basculer published / draft — action rapide
    public function togglePublish(Report $report)
    {
        $this->authorize('publish', $report);

        if ($report->status === 'published') {
            $report->status = 'draft';
            $report->published_at = null;
        } else {
            $report->status = 'published';
            $report->published_at = Carbon::now();
        }
        $report->save();

        return back()->with('success','Statut mis à jour.');
    }
}
