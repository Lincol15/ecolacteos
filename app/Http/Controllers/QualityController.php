<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQualityReportRequest;
use App\Models\Inventory;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Producer;
use App\Models\ProductionBatch;
use App\Models\Product;
use App\Models\QualityReport;
use App\Services\QualityReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QualityController extends Controller
{
    public function dashboard()
    {
        $today = Carbon::today();
        $pending = MilkDelivery::where('status', '!=', 'rechazado')
            ->where('has_quality_analysis', false)
            ->count();
        $todayAnalyzed = QualityReport::whereDate('analyzed_at', $today)->count();
        $approved = QualityReport::whereBetween('analyzed_at', [$today->copy()->startOfMonth(), $today])
            ->where('result', 'aprobado')->count();
        $totalMonth = QualityReport::whereBetween('analyzed_at', [$today->copy()->startOfMonth(), $today])->count();
        $approvedRate = $totalMonth > 0 ? round(($approved / $totalMonth) * 100, 1) : 0;

        $pendingDeliveries = MilkDelivery::with('producer.user')
            ->where('status', '!=', 'rechazado')
            ->where('has_quality_analysis', false)
            ->latest()->limit(12)->get();

        $recentReports = QualityReport::with('milkDelivery.producer.user', 'analyst')
            ->latest()->limit(10)->get();

        $paramStats = [];
        foreach (QualityReport::QUALITY_PARAMS as $key => $spec) {
            $paramStats[$key] = [
                ...$spec,
                'avg' => QualityReport::avg($key),
                'min' => QualityReport::min($key),
                'max' => QualityReport::max($key),
            ];
        }

        $weekLabels = [];
        $weekApproved = [];
        $weekRejected = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $weekLabels[] = $day->format('d/m');
            $weekApproved[] = QualityReport::whereDate('analyzed_at', $day)
                ->where('result', 'aprobado')->count();
            $weekRejected[] = QualityReport::whereDate('analyzed_at', $day)
                ->where('result', 'rechazado')->count();
        }

        $notifications = Notification::visibleForUser(Auth::user())->limit(4)->get();
        return view('quality.dashboard', compact(
            'pending', 'todayAnalyzed', 'approvedRate',
            'pendingDeliveries', 'recentReports', 'paramStats',
            'weekLabels', 'weekApproved', 'weekRejected', 'notifications'
        ));
    }

    public function reports(Request $request)
    {
        $query = QualityReport::with('milkDelivery.producer.user', 'analyst');
        if ($from = $request->from) {
            $query->where('analyzed_at', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('analyzed_at', '<=', $to);
        }
        if ($result = $request->result) {
            $query->where('result', $result);
        }
        if ($producer_id = $request->producer_id) {
            $query->whereHas('milkDelivery', fn ($q) => $q->where('producer_id', $producer_id));
        }
        $reports = $query->latest()->paginate(20)->withQueryString();
        $producers = Producer::where('status', 'activo')->with('user')->orderBy('code')->get();
        return view('quality.reports', compact('reports', 'producers'));
    }

    public function reportCreate(Request $request)
    {
        $delivery_id = $request->delivery_id;
        $delivery = MilkDelivery::with('producer.user')->find($delivery_id);
        $pendingDeliveries = MilkDelivery::with('producer.user')
            ->where('status', '!=', 'rechazado')
            ->where('has_quality_analysis', false)
            ->latest()->limit(20)->get();
        return view('quality.report-create', compact('delivery', 'pendingDeliveries'));
    }

    public function reportStore(StoreQualityReportRequest $request, QualityReportService $reportService)
    {
        try {
            $report = $reportService->create($request->validated(), Auth::id());
            
            return redirect()
                ->route('quality.reports')
                ->with('success', 'Análisis LACTOMAT registrado correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar análisis: ' . $e->getMessage());
        }
    }

    public function reportShow(QualityReport $report)
    {
        $report->load('milkDelivery.producer.user', 'analyst');
        return view('quality.report-show', compact('report'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('quality.profile', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
        ]);
        $user->update($data);
        return back()->with('success', 'Datos actualizados.');
    }
}
