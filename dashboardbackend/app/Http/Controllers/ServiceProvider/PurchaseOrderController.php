<?php
namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('serviceProvider')->get();
        return response()->json($purchaseOrders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_provider_id' => 'required|exists:service_provider_details,id',
            'po_date' => 'required|date',
            'amount' => 'required|numeric',
            'currency' => 'nullable|string|max:10',
            'status' => 'required|in:Pending,Approved,Rejected,Completed,Canceled',
            'description' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|string|max:255',
            'delivery_date' => 'nullable|date',
            'project_id' => 'required|exists:headers,id',

            'comments' => 'nullable|string|max:500',
        ]);

        // Auto-generate the PO number
        $lastPO = PurchaseOrder::orderBy('id', 'desc')->first();
        $lastPONumber = $lastPO ? intval(substr($lastPO->po_number, 3)) : 0;
        $nextPONumber = str_pad($lastPONumber + 1, 6, '0', STR_PAD_LEFT);
        $validated['po_number'] = "PO-{$nextPONumber}";

        $purchaseOrder = PurchaseOrder::create($validated);

        return response()->json($purchaseOrder, 201);
    }

    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with('serviceProvider')->findOrFail($id);
        return response()->json($purchaseOrder);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'sometimes|numeric',
            'status' => 'sometimes|in:Pending,Approved,Rejected,Completed,Canceled',
        ]);

        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update($validated);

        return response()->json($purchaseOrder);
    }
public function getNextPONumber()
{
    Log::info('getNextPONumber endpoint hit');
    $lastPO = PurchaseOrder::orderBy('id', 'desc')->first();
    $lastPONumber = $lastPO ? intval(substr($lastPO->po_number, 3)) : 0;
    $nextPONumber = str_pad($lastPONumber + 1, 6, '0', STR_PAD_LEFT);

    return response()->json(['po_number' => "PO-{$nextPONumber}"], 200);
}



    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->delete();

        return response()->json(['message' => 'Purchase Order deleted']);
    }
}
