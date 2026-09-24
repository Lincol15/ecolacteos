<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMilkDeliveryRequest;
use App\Models\CollectionRoute;
use App\Models\CollectorPayment;
use App\Models\MilkDelivery;
use App\Models\Notification;
use App\Models\PlantConfig;
use App\Models\RouteStop;
use App\Services\MilkDeliveryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CollectorController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $myRoute = CollectionRoute::where('collector_id', $user->id)
            ->where('day', $today->localeDayOfWeek === 0 ? 'Domingo' : match ($today->localeDayOfWeek) {
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
                default => 'Lunes'
            })
            ->whereIn('status', ['planeada', 'en_curso'])
            ->with('stops.producer.user')
            ->first();

        $todayDeliveries = MilkDelivery::where('collector_id', $user->id)
            ->where('delivery_date', $today)
            ->sum('liters');

        $weekDeliveries = MilkDelivery::where('collector_id', $user->id)
            ->whereBetween('delivery_date', [$today->copy()->startOfWeek(), $today])
            ->sum('liters');

        $recentDeliveries = MilkDelivery::where('collector_id', $user->id)
            ->with('producer.user', 'qualityReport')
            ->latest()->limit(10)->get();

        $myRoutes = CollectionRoute::where('collector_id', $user->id)
            ->withCount('stops')
            ->latest()->limit(5)->get();

        $notifications = Notification::visibleForUser($user)->limit(4)->get();

        return view('collector.dashboard', compact(
            'myRoute', 'todayDeliveries', 'weekDeliveries',
            'recentDeliveries', 'myRoutes', 'notifications'
        ));
    }

    public function routes()
    {
        $user = Auth::user();
        $routes = CollectionRoute::where('collector_id', $user->id)
            ->with('stops.producer.user')
            ->latest()->paginate(10);

        return view('collector.routes', compact('routes'));
    }

    public function routeShow(CollectionRoute $route)
    {
        if ($route->collector_id !== Auth::id()) {
            abort(403);
        }
        $route->load('stops.producer.user', 'milkDeliveries.producer.user');

        return view('collector.route-show', compact('route'));
    }

    public function routeUpdateStop(Request $request, RouteStop $stop)
    {
        if ($stop->collectionRoute->collector_id !== Auth::id()) {
            abort(403);
        }
        $data = $request->validate([
            'status' => 'required|in:pendiente,en_camino,visitado,ausente,rechazado',
            'estimated_liters' => 'nullable|numeric|min:0',
        ]);
        $update = $data;
        if ($data['status'] === 'visitado' && ! $stop->visited_at) {
            $update['visited_at'] = now();
        }
        $stop->update($update);

        return back()->with('success', 'Parada actualizada.');
    }

    public function deliveries(Request $request)
    {
        $user = Auth::user();
        $query = MilkDelivery::where('collector_id', $user->id)
            ->with('producer.user', 'qualityReport', 'collectionRoute');
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
            'liters' => $deliveries->total() > 0 ? (clone $query)->sum('liters') : 0,
            'amount' => $deliveries->total() > 0 ? (clone $query)->sum('total_amount') : 0,
        ];

        return view('collector.deliveries', compact('deliveries', 'totals'));
    }

    public function deliveryCreate(Request $request)
    {
        $route_stop_id = $request->route_stop_id;
        $collection_route_id = $request->collection_route_id;
        $routeStop = RouteStop::with('producer.user', 'collectionRoute')->find($route_stop_id);
        $producers = $this->myProducers()->sortBy('code')->values();
        if ($routeStop?->producer && ! $producers->contains('id', $routeStop->producer->id)) {
            $producers->push($routeStop->producer);
        }

        $pricePerLiter = (float) PlantConfig::getValue('precio_litro_leche', 1.70);

        return view('collector.delivery-create', compact('routeStop', 'producers', 'route_stop_id', 'collection_route_id', 'pricePerLiter'));
    }

    /**
     * Productores que este acopiador puede atender: ver User::resolveAssignedProducers().
     */
    private function myProducers()
    {
        return Auth::user()->resolveAssignedProducers();
    }

    public function deliveryStore(StoreMilkDeliveryRequest $request, MilkDeliveryService $deliveryService)
    {
        try {
            $delivery = $deliveryService->create($request->validated(), Auth::id());

            return redirect()
                ->route('collector.deliveries')
                ->with('success', "Entrega de {$delivery->liters} L registrada correctamente.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Error al registrar entrega: '.$e->getMessage());
        }
    }

    public function producers()
    {
        $today = Carbon::today();
        $startMonth = $today->copy()->startOfMonth();

        $producers = $this->myProducers()
            ->map(function ($producer) use ($startMonth, $today) {
                $producer->month_liters = $producer->getTotalLitersByPeriod($startMonth, $today);

                return $producer;
            })
            ->sortBy(fn ($p) => $p->user->name ?? '');

        return view('collector.producers', compact('producers'));
    }

    public function journal()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $myRoute = CollectionRoute::where('collector_id', $user->id)
            ->where('day', $today->localeDayOfWeek === 0 ? 'Domingo' : match ($today->localeDayOfWeek) {
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado',
                default => 'Lunes'
            })
            ->whereIn('status', ['planeada', 'en_curso'])
            ->with('stops.producer.user')
            ->first();

        $todayDeliveries = MilkDelivery::where('collector_id', $user->id)
            ->where('delivery_date', $today)
            ->with('producer.user', 'qualityReport')
            ->latest()
            ->get();

        $totals = [
            'liters' => $todayDeliveries->sum('liters'),
            'amount' => $todayDeliveries->sum('total_amount'),
            'count' => $todayDeliveries->count(),
        ];

        return view('collector.journal', compact('myRoute', 'todayDeliveries', 'totals'));
    }

    public function payments()
    {
        $user = Auth::user();
        $payments = $user->collectorPayments()->latest('period_month')->paginate(12);
        $summary = [
            'monthly_salary' => (float) $user->monthly_salary,
            'paid_this_year' => $user->collectorPayments()->where('status', 'pagado')->whereYear('period_month', now()->year)->sum('total_amount'),
            'pending' => $user->collectorPayments()->where('status', '!=', 'pagado')->sum('total_amount'),
        ];
        $latestPayment = $user->collectorPayments()->latest('period_month')->first();

        return view('collector.payments', compact('payments', 'summary', 'latestPayment'));
    }

    public function paymentReceipt(CollectorPayment $collectorPayment)
    {
        if ($collectorPayment->collector_id !== Auth::id()) {
            abort(403);
        }
        $collectorPayment->load('collector', 'processedBy');

        return view('payments.collector-receipt', [
            'payment' => $collectorPayment,
            'backUrl' => route('collector.payments'),
        ]);
    }

    public function profile()
    {
        $user = Auth::user();
        $assignedProducers = $user->resolveAssignedProducers()->sortBy(fn ($p) => $p->user->name ?? '');
        $hasIndividualAssignment = $user->assignedProducers()->exists();

        return view('collector.profile', compact('user', 'assignedProducers', 'hasIndividualAssignment'));
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
