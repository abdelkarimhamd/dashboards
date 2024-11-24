<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Http\Controllers\Controller;
use App\Models\Header;
use App\Models\ServiceProvider\ServiceProviderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;

class ServiceProviderController extends Controller
{
    /**
     * Display a paginated list of service providers with related data.
     */
    public function index()
    {
        $serviceProviders = ServiceProviderDetail::with(['purchaseOrders', 'variationOrders', 'project'])->paginate(10);
        Log::info('Fetched service providers');
        Log::info($serviceProviders);
        return response()->json($serviceProviders, 200);
    }

    /**
     * Store a newly created service provider in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:service_provider_details,email',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'username' => 'required|string|max:100|unique:service_provider_details,username',
            'project_id' => 'nullable|exists:headers,id', // Ensure the project exists
            'subscribed_plan' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'terms' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:50',
            'billing_history' => 'nullable|array',
            'invoices' => 'nullable|array',
        ]);

        $serviceProvider = ServiceProviderDetail::create($validated);

        return response()->json($serviceProvider, 201);
    }
public function project(){

    $projects = Header::all();
    Log::info($projects);
    return response()->json(['projects' => $projects], 200);
}
    /**
     * Display the specified service provider with related data.
     */
    public function show($id)
    {
        $serviceProvider = ServiceProviderDetail::with(['purchaseOrders', 'variationOrders', 'project:id,projectName'])->findOrFail($id);

        return response()->json($serviceProvider, 200);
    }

    /**
     * Update the specified service provider in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:service_provider_details,email,' . $id,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'username' => 'sometimes|string|max:100|unique:service_provider_details,username,' . $id,
            'project_id' => 'nullable|exists:headers,id', // Ensure the project exists
            'subscribed_plan' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'terms' => 'nullable|boolean',
            'payment_method' => 'nullable|string|max:50',
            'billing_history' => 'nullable|array',
            'invoices' => 'nullable|array',
        ]);

        $serviceProvider = ServiceProviderDetail::findOrFail($id);
        $serviceProvider->update($validated);

        return response()->json($serviceProvider, 200);
    }

    /**
     * Remove the specified service provider from storage.
     */
    public function destroy($id)
    {
        $serviceProvider = ServiceProviderDetail::with(['purchaseOrders', 'variationOrders'])->findOrFail($id);

        // Prevent deletion if there are related orders
        if ($serviceProvider->purchaseOrders()->exists() || $serviceProvider->variationOrders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete service provider with existing orders'
            ], 400);
        }

        $serviceProvider->delete();

        return response()->json(['message' => 'Service Provider deleted'], 200);
    }
}
