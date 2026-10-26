<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSanctionRequest;
use App\Http\Resources\SanctionResource;
use App\Models\Sanction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SanctionController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Sanction::with('producer.user');
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($producerId = $request->producer_id) {
            $query->where('producer_id', $producerId);
        }

        return SanctionResource::collection($query->latest('sanction_date')->latest('id')->paginate(100)->withQueryString());
    }

    public function store(StoreSanctionRequest $request)
    {
        $sanction = Sanction::create([...$request->validated(), 'issued_by' => $request->user()->id, 'status' => 'activa']);

        return (new SanctionResource($sanction->load('producer.user')))->response()->setStatusCode(201);
    }

    public function update(Request $request, Sanction $sanction): SanctionResource
    {
        $data = $request->validate([
            'status' => 'required|in:activa,cumplida,anulada',
            'notes' => 'nullable|string|max:2000',
        ]);

        return new SanctionResource($sanction->updateStatus($data)->load('producer.user'));
    }
}
