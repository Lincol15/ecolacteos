<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Producer;
use App\Models\QualityReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProducerController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $producer = $user->producer ?? Producer::firstOrCreate(['user_id' => $user->id], [
            'code' => 'PROD-' . str_pad((string)$user->id, 5, '0', STR_PAD_LEFT),
            'zone' => 'Huata', 'district' => 'Huata', 'province' => 'Huata', 'region' => 'Ancash',
            'status' => 'activo', 'registration_date' => now(),
        ]);
        $today = Carbon::today();
        $startMonth = $today->copy()->startOfMonth();
        $startWeek = $today->copy()->startOfWeek();
        $endWeek = $today->copy()->endOfWeek();

        $litersMonth = MilkDelivery::where('producer_id', $producer->id)
            ->whereBetween('delivery_date', [$startMonth, $today])->sum('liters');
        $litersWeek = MilkDelivery::where('producer_id', $producer->id)
            ->whereBetween('delivery_date', [$startWeek, $endWeek])->sum('liters');
        $pendingPayment = Payment::where('producer_id', $producer->id)->where('status', 'pendiente')->sum('total_amount');
        $paidPayment = Payment::where('producer_id', $producer->id)->where('status', 'pagado')->sum('total_amount');
        $lastDelivery = MilkDelivery::where('producer_id', $producer->id)->latest('delivery_date')->first();
        $lastQuality = QualityReport::whereHas('milkDelivery', fn ($q) => $q->where('producer_id', $producer->id))
            ->latest()->with('milkDelivery')->first();
        $deliveries = MilkDelivery::where('producer_id', $producer->id)
            ->with('qualityReport', 'collector')
            ->latest('delivery_date')->limit(8)->get();
        $notifications = Notification::visibleForUser($user)->limit(5)->get();

        $chartLabels = [];
        $chartLiters = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i);
            $chartLabels[] = $day->format('d/m');
            $chartLiters[] = MilkDelivery::where('producer_id', $producer->id)
                ->where('delivery_date', $day->toDateString())->sum('liters');
        }

        return view('producer.dashboard', compact(
            'producer', 'litersMonth', 'litersWeek', 'pendingPayment', 'paidPayment',
            'lastDelivery', 'lastQuality', 'deliveries', 'notifications',
            'chartLabels', 'chartLiters'
        ));
    }

    public function deliveries(Request $request)
    {
        $producer = Auth::user()->producer;
        $query = MilkDelivery::where('producer_id', $producer->id)
            ->with('qualityReport', 'collector', 'collectionRoute');
        if ($from = $request->from) {
            $query->where('delivery_date', '>=', $from);
        }
        if ($to = $request->to) {
            $query->where('delivery_date', '<=', $to);
        }
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        $deliveries = $query->latest('delivery_date')->paginate(20)->withQueryString();
        $totals = [
            'liters' => $deliveries->total() > 0 ? $query->sum('liters') : 0,
            'amount' => $deliveries->total() > 0 ? $query->sum('total_amount') : 0,
        ];
        return view('producer.deliveries', compact('deliveries', 'totals'));
    }

    public function quality(Request $request)
    {
        $producer = Auth::user()->producer;
        $query = QualityReport::whereHas('milkDelivery', fn ($q) => $q->where('producer_id', $producer->id))
            ->with(['milkDelivery.producer.user']);
        if ($from = $request->from) {
            $query->whereHas('milkDelivery', fn ($q) => $q->where('delivery_date', '>=', $from));
        }
        if ($to = $request->to) {
            $query->whereHas('milkDelivery', fn ($q) => $q->where('delivery_date', '<=', $to));
        }
        $reports = $query->latest()->paginate(15)->withQueryString();

        $paramAverages = [];
        foreach (QualityReport::QUALITY_PARAMS as $param => $spec) {
            $paramAverages[$param] = $query->avg($param);
        }

        return view('producer.quality', compact('reports', 'paramAverages'));
    }

    public function payments(Request $request)
    {
        $producer = Auth::user()->producer;
        $query = Payment::where('producer_id', $producer->id)->with('items.milkDelivery');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        $payments = $query->latest('period_end')->paginate(10)->withQueryString();
        $summary = [
            'total_pagado' => Payment::where('producer_id', $producer->id)->where('status', 'pagado')->sum('total_amount'),
            'total_pendiente' => Payment::where('producer_id', $producer->id)->where('status', 'pendiente')->sum('total_amount'),
            'total_bonos' => Payment::where('producer_id', $producer->id)->sum(\DB::raw('quality_bonus + production_bonus')),
            'total_liters' => Payment::where('producer_id', $producer->id)->sum('total_liters'),
        ];
        return view('producer.payments', compact('payments', 'summary'));
    }

    public function paymentShow(Payment $payment)
    {
        if ($payment->producer_id !== Auth::user()->producer?->id) {
            abort(403);
        }
        $payment->load('items.milkDelivery.qualityReport', 'processedBy');
        return view('producer.payment-show', compact('payment'));
    }

    public function complaints()
    {
        $producer = Auth::user()->producer;
        $complaints = Complaint::where('producer_id', $producer->id)
            ->with('assignedTo')->latest()->paginate(10);
        return view('producer.complaints', compact('complaints'));
    }

    public function complaintsCreate()
    {
        return view('producer.complaints-create');
    }

    public function complaintsStore(Request $request)
    {
        $producer = Auth::user()->producer;
        $data = $request->validate([
            'category' => 'required|in:precio,pesaje,calidad,pago,atencion,ruta,otro',
            'priority' => 'required|in:baja,normal,alta,urgente',
            'subject' => 'required|string|max:200',
            'description' => 'required|string|max:2000',
        ]);
        Complaint::create([
            ...$data,
            'ticket_number' => 'TK-' . now()->year . '-' . str_pad((string)(Complaint::count() + 1), 5, '0', STR_PAD_LEFT),
            'producer_id' => $producer->id,
            'user_id' => Auth::id(),
            'status' => 'abierto',
        ]);
        return redirect()->route('producer.complaints')->with('success', 'Reclamo enviado correctamente. Se le notificará cuando haya respuesta.');
    }

    public function complaintShow(Complaint $complaint)
    {
        if ($complaint->producer_id !== Auth::user()->producer?->id) {
            abort(403);
        }
        return view('producer.complaints-show', compact('complaint'));
    }

    public function profile()
    {
        $user = Auth::user();
        $producer = $user->producer;
        return view('producer.profile', compact('user', 'producer'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'lastname' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'farm_name' => 'nullable|string|max:150',
            'zone' => 'nullable|string|max:100',
            'cows_count' => 'nullable|integer|min:0',
        ]);
        $user->update($data);
        if ($user->producer) {
            $user->producer->update($data);
        }
        return back()->with('success', 'Datos actualizados correctamente.');
    }
}
