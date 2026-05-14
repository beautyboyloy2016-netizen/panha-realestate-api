<?php

namespace App\Http\Controllers\Backend;

use App\Models\Transaction;
use App\Models\PaymentMethod;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends BaseController
{
    protected string $resource = 'transaction';

    /**
     * Display a listing of transactions.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $paymentMethods = PaymentMethod::active()->ordered()->get();
        $users = User::all();

        return view('admin.transactions.index', compact('paymentMethods', 'users'));
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $query = Transaction::with(['user', 'paymentMethod', 'invoice']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('transaction_id_display', function ($transaction) {
                $date = '<small class="text-muted d-block">' . $transaction->created_at->format('M d, Y H:i') . '</small>';
                return '<code>' . $transaction->transaction_id . '</code>' . $date;
            })
            ->addColumn('user_display', function ($transaction) {
                if ($transaction->user) {
                    return '<strong>' . $transaction->user->name . '</strong><small class="text-muted d-block">' . $transaction->user->email . '</small>';
                }
                return '<span class="text-muted">Guest</span>';
            })
            ->addColumn('type_badge', function ($transaction) {
                $badges = [
                    'payment' => 'bg-primary',
                    'refund' => 'bg-warning',
                    'deposit' => 'bg-info',
                    'withdrawal' => 'bg-secondary',
                ];
                $class = $badges[$transaction->type] ?? 'bg-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($transaction->type) . '</span>';
            })
            ->addColumn('amount_display', function ($transaction) {
                $class = $transaction->type === 'refund' ? 'text-danger' : 'text-success';
                $prefix = $transaction->type === 'refund' ? '-' : '+';
                return '<strong class="' . $class . '">' . $prefix . $transaction->currency . ' ' . number_format($transaction->amount, 2) . '</strong>';
            })
            ->addColumn('fee_display', function ($transaction) {
                if ($transaction->fee > 0) {
                    return '<span class="text-muted">-$' . number_format($transaction->fee, 2) . '</span>';
                }
                return '-';
            })
            ->addColumn('payment_method_display', function ($transaction) {
                if ($transaction->paymentMethod) {
                    $icon = $transaction->paymentMethod->icon ? '<i class="' . $transaction->paymentMethod->icon . ' me-1"></i>' : '';
                    return $icon . $transaction->paymentMethod->name;
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('invoice_display', function ($transaction) {
                if ($transaction->invoice) {
                    return '<a href="#" class="view-invoice" data-id="' . $transaction->invoice_id . '">' . $transaction->invoice->invoice_number . '</a>';
                }
                return '-';
            })
            ->addColumn('status', function ($transaction) {
                return $transaction->status_badge;
            })
            ->addColumn('actions', function ($transaction) {
                $actions = '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-item" data-id="' . $transaction->id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </button>';

                if ($transaction->status === 'pending') {
                    $actions .= '
                        <button class="btn btn-sm btn-success approve-item" data-id="' . $transaction->id . '" title="Approve">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="btn btn-sm btn-danger reject-item" data-id="' . $transaction->id . '" title="Reject">
                            <i class="fas fa-times"></i>
                        </button>';
                }

                if ($transaction->status === 'completed' && $transaction->type === 'payment') {
                    $actions .= '
                        <button class="btn btn-sm btn-warning refund-item" data-id="' . $transaction->id . '" title="Refund">
                            <i class="fas fa-undo"></i>
                        </button>';
                }

                $actions .= '
                    </div>';

                return $actions;
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', $keyword);
            })
            ->filterColumn('type_badge', function ($query, $keyword) {
                $query->where('type', $keyword);
            })
            ->rawColumns(['transaction_id_display', 'user_display', 'type_badge', 'amount_display', 'fee_display', 'payment_method_display', 'invoice_display', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'type' => 'required|in:payment,refund,deposit,withdrawal',
            'amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|numeric|min:0',
            'currency' => 'required|string|max:3',
            'status' => 'required|in:pending,processing,completed,failed,refunded,cancelled',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validated['fee'] = $validated['fee'] ?? 0;
        $validated['net_amount'] = $validated['amount'] - $validated['fee'];
        $validated['ip_address'] = $request->ip();
        $validated['user_agent'] = $request->userAgent();

        if ($validated['status'] === 'completed') {
            $validated['processed_at'] = now();
        }

        $transaction = Transaction::create($validated);

        // If transaction is linked to invoice and completed, update invoice status
        if ($transaction->invoice_id && $transaction->status === 'completed') {
            $invoice = Invoice::find($transaction->invoice_id);
            $totalPaid = $invoice->transactions()->where('status', 'completed')->sum('amount');

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->markAsPaid();
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction created successfully!',
                'data' => $transaction->load(['user', 'paymentMethod', 'invoice']),
            ]);
        }

        return redirect()->route('admin.transactions.index')->with('success', 'Transaction created successfully!');
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction, Request $request)
    {
        $transaction->load(['user', 'paymentMethod', 'invoice']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        }

        return view('admin.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified transaction.
     */
    public function edit(Transaction $transaction, Request $request)
    {
        $transaction->load(['user', 'paymentMethod', 'invoice']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $transaction,
            ]);
        }

        $paymentMethods = PaymentMethod::active()->ordered()->get();
        $users = User::all();

        return view('admin.transactions.edit', compact('transaction', 'paymentMethods', 'users'));
    }

    /**
     * Update the specified transaction.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,refunded,cancelled',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $transaction->status;

        if ($validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $validated['processed_at'] = now();
        }

        $transaction->update($validated);

        // If transaction is linked to invoice and completed, update invoice status
        if ($transaction->invoice_id && $validated['status'] === 'completed' && $oldStatus !== 'completed') {
            $invoice = Invoice::find($transaction->invoice_id);
            $totalPaid = $invoice->transactions()->where('status', 'completed')->sum('amount');

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->markAsPaid();
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully!',
                'data' => $transaction->load(['user', 'paymentMethod', 'invoice']),
            ]);
        }

        return redirect()->route('admin.transactions.index')->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified transaction.
     */
    public function destroy(Transaction $transaction, Request $request)
    {
        // Only allow deletion of pending or failed transactions
        if (!in_array($transaction->status, ['pending', 'failed', 'cancelled'])) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete completed or processing transactions.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Cannot delete completed or processing transactions.');
        }

        $transaction->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully!',
            ]);
        }

        return redirect()->route('admin.transactions.index')->with('success', 'Transaction deleted successfully!');
    }

    /**
     * Approve a pending transaction.
     */
    public function approve(Transaction $transaction, Request $request)
    {
        if ($transaction->status !== 'pending') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending transactions can be approved.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Only pending transactions can be approved.');
        }

        $transaction->markAsCompleted();

        // Update related invoice if applicable
        if ($transaction->invoice_id) {
            $invoice = Invoice::find($transaction->invoice_id);
            $totalPaid = $invoice->transactions()->where('status', 'completed')->sum('amount');

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->markAsPaid();
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction approved successfully!',
            ]);
        }

        return redirect()->back()->with('success', 'Transaction approved successfully!');
    }

    /**
     * Reject a pending transaction.
     */
    public function reject(Transaction $transaction, Request $request)
    {
        $reason = $request->input('reason', 'Rejected by admin');

        if ($transaction->status !== 'pending') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending transactions can be rejected.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Only pending transactions can be rejected.');
        }

        $transaction->markAsFailed($reason);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Transaction rejected!',
            ]);
        }

        return redirect()->back()->with('success', 'Transaction rejected!');
    }

    /**
     * Create refund for a completed transaction.
     */
    public function refund(Transaction $transaction, Request $request)
    {
        $amount = $request->input('amount', $transaction->amount);

        if ($transaction->status !== 'completed' || $transaction->type !== 'payment') {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed payment transactions can be refunded.',
                ], 422);
            }

            return redirect()->back()->with('error', 'Only completed payment transactions can be refunded.');
        }

        $refund = $transaction->createRefund($amount);
        $refund->markAsCompleted();

        // Update original transaction
        $transaction->update(['status' => 'refunded']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Refund created successfully!',
                'refund' => $refund,
            ]);
        }

        return redirect()->back()->with('success', 'Refund created successfully!');
    }

    /**
     * Get transaction statistics.
     */
    public function stats(Request $request)
    {
        $stats = [
            'total_transactions' => Transaction::count(),
            'total_completed' => Transaction::completed()->sum('amount'),
            'total_pending' => Transaction::pending()->sum('amount'),
            'total_refunds' => Transaction::refunds()->completed()->sum('amount'),
            'today_transactions' => Transaction::whereDate('created_at', today())->count(),
            'today_amount' => Transaction::whereDate('created_at', today())->completed()->sum('amount'),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
