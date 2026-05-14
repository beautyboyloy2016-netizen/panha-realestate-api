<?php

namespace App\Http\Controllers\Backend;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentMethodController extends BaseController
{
    protected string $resource = 'payment_method';

    /**
     * Display a listing of payment methods.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.payment-methods.index');
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $query = PaymentMethod::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('icon_display', function ($method) {
                return '<span class="fs-4">' . ($method->icon ? '<i class="' . $method->icon . '"></i>' : '<i class="fas fa-credit-card"></i>') . '</span>';
            })
            ->addColumn('name_display', function ($method) {
                $code = '<small class="text-muted d-block">' . $method->code . '</small>';
                return '<strong>' . $method->name . '</strong>' . $code;
            })
            ->addColumn('type_badge', function ($method) {
                $badges = [
                    'online' => 'bg-primary',
                    'offline' => 'bg-secondary',
                    'crypto' => 'bg-warning',
                ];
                $class = $badges[$method->type] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($method->type) . '</span>';
            })
            ->addColumn('fee_display', function ($method) {
                return $method->formatted_fee;
            })
            ->addColumn('status', function ($method) {
                $class = $method->is_active ? 'bg-success' : 'bg-danger';
                $text = $method->is_active ? 'Active' : 'Inactive';
                return '<span class="badge ' . $class . '">' . $text . '</span>';
            })
            ->addColumn('transactions_count', function ($method) {
                return '<span class="badge bg-info">' . $method->transactions()->count() . '</span>';
            })
            ->addColumn('actions', function ($method) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-item" data-id="' . $method->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-item" data-id="' . $method->id . '" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-item" data-id="' . $method->id . '" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword === 'active') {
                    $query->where('is_active', true);
                } elseif ($keyword === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->filterColumn('type_badge', function ($query, $keyword) {
                $query->where('type', $keyword);
            })
            ->rawColumns(['icon_display', 'name_display', 'type_badge', 'status', 'transactions_count', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created payment method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'type' => 'required|in:online,offline,crypto',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'processing_fee' => 'nullable|numeric|min:0',
            'processing_fee_type' => 'required|in:fixed,percentage',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['processing_fee'] = $validated['processing_fee'] ?? 0;
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $method = PaymentMethod::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method created successfully!',
                'data' => $method,
            ]);
        }

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method created successfully!');
    }

    /**
     * Display the specified payment method.
     */
    public function show(PaymentMethod $paymentMethod, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $paymentMethod,
            ]);
        }

        return view('admin.payment-methods.show', compact('paymentMethod'));
    }

    /**
     * Show the form for editing the specified payment method.
     */
    public function edit(PaymentMethod $paymentMethod, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $paymentMethod,
            ]);
        }

        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified payment method.
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods,code,' . $paymentMethod->id,
            'type' => 'required|in:online,offline,crypto',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'processing_fee' => 'nullable|numeric|min:0',
            'processing_fee_type' => 'required|in:fixed,percentage',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['processing_fee'] = $validated['processing_fee'] ?? 0;

        $paymentMethod->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully!',
                'data' => $paymentMethod,
            ]);
        }

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method updated successfully!');
    }

    /**
     * Remove the specified payment method.
     */
    public function destroy(PaymentMethod $paymentMethod, Request $request)
    {
        // Check if payment method has transactions
        if ($paymentMethod->transactions()->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete payment method with existing transactions.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Cannot delete payment method with existing transactions.');
        }

        $paymentMethod->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully!',
            ]);
        }

        return redirect()->route('admin.payment-methods.index')->with('success', 'Payment method deleted successfully!');
    }

    /**
     * Toggle payment method status.
     */
    public function toggleStatus(PaymentMethod $paymentMethod, Request $request)
    {
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment method status updated!',
                'is_active' => $paymentMethod->is_active,
            ]);
        }

        return redirect()->back()->with('success', 'Payment method status updated!');
    }
}
