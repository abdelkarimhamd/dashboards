<?php
namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\VariationOrder;
use Illuminate\Http\Request;

class VariationOrderController extends Controller
{
    public function index()
    {
        $variationOrders = VariationOrder::with('serviceProvider')->get();
        return response()->json($variationOrders);
    }
    public function getNextVONumber()
    {
        $lastVO = VariationOrder::orderBy('id', 'desc')->first();
        $lastVONumber = $lastVO ? intval(substr($lastVO->vo_number, 3)) : 0;
        $nextVONumber = str_pad($lastVONumber + 1, 6, '0', STR_PAD_LEFT);

        return response()->json(['vo_number' => "VO-{$nextVONumber}"], 200);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_provider_id' => 'required|exists:service_provider_details,id',
            'project_id' => 'required|exists:headers,id',
            'vo_date' => 'required|date',
            'amount' => 'required|numeric',
            'currency' => 'nullable|string|max:10',
            'status' => 'required|in:Pending,Approved,Rejected,Completed,Canceled',
            'description' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'delivery_date' => 'nullable|date',
            'comments' => 'nullable|string',
        ]);

        // Auto-generate the VO number
        $lastVO = VariationOrder::orderBy('id', 'desc')->first();
        $lastVONumber = $lastVO ? intval(substr($lastVO->vo_number, 3)) : 0;
        $nextVONumber = str_pad($lastVONumber + 1, 6, '0', STR_PAD_LEFT);
        $validated['vo_number'] = "VO-{$nextVONumber}";

        $variationOrder = VariationOrder::create($validated);

        return response()->json($variationOrder, 201);
    }

    public function show($id)
    {
        $variationOrder = VariationOrder::with('serviceProvider')->findOrFail($id);
        return response()->json($variationOrder);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric',
            'status' => 'sometimes|in:Pending,Approved,Rejected,Completed,Canceled',
        ]);

        $variationOrder = VariationOrder::findOrFail($id);
        $variationOrder->update($validated);

        return response()->json($variationOrder);
    }

    public function destroy($id)
    {
        $variationOrder = VariationOrder::findOrFail($id);
        $variationOrder->delete();

        return response()->json(['message' => 'Variation Order deleted']);
    }
}
