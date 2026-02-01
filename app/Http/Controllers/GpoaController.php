<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Models\User;
use App\Models\Position;
use App\Models\Gpoa;
use App\Models\GpoaActivity;
use App\Models\AcademicTerm;
use App\Models\AcademicPeriod;
use Illuminate\Support\Carbon;
use App\Services\Format;
use App\Http\Requests\SaveGpoaRequest;
use WeasyPrint\Facade as WeasyPrint;
use App\Services\PagedView;
use Illuminate\Support\Facades\Storage;
use App\Events\GpoaStatusChanged;
use App\Events\GpoaUpdated;
use App\Jobs\MakeGpoaReport;
use App\Jobs\MakeClosingGpoaReport;
use App\Jobs\MakeClosingAccomReport;

class GpoaController extends Controller implements HasMiddleware
{
    private static $gpoa;

    public function __construct()
    {
        self::$gpoa = Gpoa::active()->first();
    }

    public static function middleware(): array
    {
        return [
            new Middleware('auth.index:viewAny,' . GpoaActivity::class,
                only: ['index']),
            new Middleware('auth.index:create,' . Gpoa::class,
                only: ['create', 'store']),
            new Middleware('auth.index:update,' . Gpoa::class,
                only: ['edit', 'update']),
            new Middleware('auth.index:close,' . Gpoa::class,
                only: ['confirmClose', 'close']),
            new Middleware('auth.index:genPdf,' . Gpoa::class,
                only: ['genPdf', 'streamPdf']),
        ];
    }

    public function index()
    {
        $gpoa = self::$gpoa;
        if (!$gpoa) {
            return view('gpoa.index', ['gpoa' => $gpoa, 'activities' => null]);
        }
        switch(auth()->user()->position_name) {
        case 'president':
            $activities = $gpoa->activities()->forPresident();
            break;
        case 'adviser':
            $activities = $gpoa->activities()->forAdviser();
            break;
        default:
            $activities = $gpoa->activities();
        }
        return view('gpoa.index', [
            'gpoa' => $gpoa,
            'activities' => $activities->orderBy('updated_at', 'desc')
                ->paginate(15),
            'closeRoute' => route('gpoa.close'),
        ]);
    }

    public function oldIndex()
    {
        $gpoas = Gpoa::closed()->withApprovedActivity()
            ->orderBy('closed_at', 'desc')->paginate(15);
        return view('gpoa.index-old', [
            'gpoas' => $gpoas,
            'backRoute' => route('gpoa.index'),
        ]);
    }

    public function create()
    {
        return view('gpoa.create', [
            'update' => false,
            'terms' => AcademicTerm::all(),
            'gpoa' => null
        ]);
    }

    public function store(SaveGpoaRequest $request)
    {
        self::storeOrUpdate($request);
        GpoaStatusChanged::dispatch();
        return redirect()->route('gpoa.index');
    }

    private static function storeOrUpdate(Request $request, Gpoa $gpoa = null)
    {
        if (!$gpoa) $gpoa = new Gpoa();
        $term = AcademicTerm::find($request->academic_term);
        if ($gpoa->academicPeriod()->exists()) {
            $period = $gpoa->academicPeriod;
        } else {
            $period = new AcademicPeriod();
        }
        $period->start_date = $request->start_date;
        $period->end_date = $request->end_date;
        $period->term()->associate($term);
        $period->head_of_student_services = $request->head_of_student_services;
        $period->branch_director = $request->branch_director;
        $period->save();
        $gpoa->academicPeriod()->associate($period);
        $gpoa->creator()->associate(auth()->user());
        $gpoa->save();
    }

    public function show(Gpoa $gpoa)
    {
        return view('gpoa.show', [
            'reportRoute' => route('gpoas.report.show', [
                'gpoa' => $gpoa->public_id
            ]),
            'accomReportRoute' => route('gpoas.accom-report.show', [
                'gpoa' => $gpoa->public_id
            ]),
            'createdBy' => $gpoa->creator?->full_name,
            'closedBy' => $gpoa->closer?->full_name,
            'closedAt' => Format::date($gpoa->closed_at),
            'academicPeriod' => $gpoa->full_academic_period,
            'activityCount' => $gpoa->activities()->approved()->count(),
            'accomReportCount' => $gpoa->events()->approved()->count(),
            'backRoute' => route('gpoas.old-index'),
        ]);
    }

    public function showFinalReport(Gpoa $gpoa)
    {
        $fileRoute = null;
        $hasFile = $gpoa->report_filepath;
        if ($hasFile) {
            $fileRoute = route('gpoas.report-file.show', [
                'gpoa' => $gpoa->public_id
            ], false);
        }
        $response = response()->view('gpoa.show-final-report', [
            'fileRoute' => $fileRoute,
            'backRoute' => route('gpoas.show', [
                'gpoa' => $gpoa->public_id
            ]),
            'prepareMessage' => Format::documentPrepareMessage(),
        ]);
        if ($hasFile) {
            return $response;
        }
        return $response->header('Refresh', '5');
    }
    
    /*
    public function showReport(Gpoa $gpoa)
    {
        $fileRoute = null;
        $updated = $gpoa->report_file_updated;
        if ($updated) {
            $fileRoute = route('gpoas.report-file.show', [
                'gpoa' => $gpoa->public_id
            ], false);
        }
        $response = response()->view('gpoa.show-gpoa-report', [
            'fileRoute' => $fileRoute,
            'backRoute' => route('gpoas.show', [
                'gpoa' => $gpoa->public_id
            ]),
            'hasApproved' => true,
            'updated' => true,
            'prepareMessage' => Format::documentPrepareMessage(),
            'updateMessage' => Format::documentUpdateMessage(),
        ]);
        if ($updated) {
            return $response;
        }
        return $response->header('Refresh', '5');
    }
    */

    public function showReportFile(Gpoa $gpoa)
    {
        $file = $gpoa->report_filepath;
        if (!$file) abort(404);
        return response()->file(Storage::path($file));
    }

    public function showAccomReport(Gpoa $gpoa)
    {
        $hasApproved = $gpoa->has_approved_accom_report;
        if (!$hasApproved) abort(404);
        $fileRoute = null;
        /*
        $hasFile = $gpoa->accom_report_filepath; 
        if ($hasFile) {
            $fileRoute = route('gpoas.accom-report-file.show', [
                'gpoa' => $gpoa->public_id
            ], false);
        }
        */
        $response = response()->view('gpoa.show-accom-report', [
            'fileRoute' => $fileRoute,
            'backRoute' => route('gpoas.show', [
                'gpoa' => $gpoa->public_id
            ]),
            'prepareMessage' => Format::documentPrepareMessage(),
        ] + $gpoa->accomReportViewData());
        return $response;
        /*
        if ($hasFile) {
        }
        return $response->header('Refresh', '5');
        */
    }

    public function showAccomReportFile(Gpoa $gpoa)
    {
        $file = $gpoa->accom_report_filepath;
        if (!$file) abort(404);
        return response()->file(Storage::path($file));
    }

    public function edit()
    {
        $gpoa = self::$gpoa;
        return view('gpoa.create', [
            'update' => true,
            'terms' => AcademicTerm::all(),
            'gpoa' => $gpoa
        ]);
    }

    public function update(SaveGpoaRequest $request)
    {
        $gpoa = self::$gpoa;
        self::storeOrUpdate($request, $gpoa);
        return redirect()->route('gpoa.index');
    }

    public function showReport(Gpoa $gpoa)
    {
        return view('gpoa.show-gpoa-report', [
            'authUser' => auth()->user(), 
            'backRoute' => route('gpoa.index'),
        ] + $gpoa->reportViewData());
    }

    public function showCurrentReport()
    {
        $gpoa = self::$gpoa;
        return view('gpoa.show-gpoa-report', [
            'authUser' => auth()->user(), 
            'backRoute' => route('gpoa.index'),
        ] + $gpoa->reportViewData());
    }

    public function genPdf(Request $request)
    {
        $gpoa = self::$gpoa;
        $fileRoute = null;
        $hasApproved = $gpoa?->has_approved_activity;
        if ($gpoa->report_filepath && $gpoa->report_file_updated) {
            $fileRoute = route('gpoa.streamPdf', [
                'id' => $gpoa->report_file_updated_at->format('ymdHis')
            ], false);
        }
        $response = response()->view('gpoa.show-gpoa-report', [
            'gpoa' => $gpoa,
            'fileRoute' => $fileRoute,
            'backRoute' => route('gpoa.index'),
            'updated' => $gpoa->report_file_updated,
            'hasApproved' => $hasApproved,
            'prepareMessage' => Format::documentPrepareMessage(),
            'updateMessage' => Format::documentUpdateMessage(),
        ]);
        if ($gpoa->report_file_updated) {
            return $response;
        }
        if (!$gpoa->report_filepath) {
        }
        return $response->header('Refresh', '5');
    }

    public function streamPdf(Request $request)
    {
        $gpoa = self::$gpoa;
        $file = $gpoa->report_filepath;
        if (!$file) abort(404);
        return response()->file(Storage::path($file));
        /*
        return WeasyPrint::prepareSource(new PagedView('gpoa.report',
            $gpoa->reportViewData()))->inline('gpoa_report.pdf');
        */
    }

    public function confirmClose(Request $request)
    {
        $gpoa = self::$gpoa;
        return view('gpoa.close', [
            'gpoa' => $gpoa,
            'backRoute' => route('gpoa.index'),
            'closeRoute' => route('gpoa.close'),
        ]);
    }

    public function close(Request $request)
    {
        $gpoa = self::$gpoa;
        $status = 'GPOA closed.';
        /*
        if (!$gpoa->has_approved_activity) {
            self::destroyGpoa();
            return redirect()->route('gpoa.index')->with('status', $status);
        }
        $gpoa->report_file_updated = false;
        */
        self::closeGpoa();
        GpoaStatusChanged::dispatch();
        return redirect()->route('gpoa.index')->with('status', $status);
    }

    private static function destroyGpoa(): void
    {
        $gpoa = self::$gpoa;
        $activities = $gpoa->activities;
        foreach ($activities as $activity) {
            $activity->eventHeads()->detach();
            $activity->delete();
        }
        $gpoa->delete();
    }

    private static function closeGpoa(): void
    {
        $gpoa = self::$gpoa;
        $gpoa->closer()->associate(auth()->user());
        $gpoa->closed_at = now();
        $gpoa->save();
    }

    public function destroy(string $id)
    {

    }
}
