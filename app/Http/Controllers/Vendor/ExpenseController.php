<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Get date range from request or default to current month
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        // Get all expenses for the date range (for the list view)
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($e) => [
                'id'       => $e->id,
                'title'    => $e->title,
                'category' => $e->category,
                'amount'   => $e->amount,
                'date'     => $e->date->toDateString(),
                'method'   => $e->method,
                'status'   => $e->status,
                'note'     => $e->note ?? '',
            ]);

        // Get inventory expenses only for the date range (for stats)
        $inventoryExpenses = Expense::where('category', 'inventory')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('supplier')
            ->get();
        
        // Calculate inventory spend statistics
        $totalInventorySpend = $inventoryExpenses->sum('amount');
        $totalInventoryTransactions = $inventoryExpenses->count();
        
        // Get inventory expenses by supplier
        $expensesBySupplier = $inventoryExpenses
            ->groupBy('supplier_id')
            ->map(function($expenses) {
                $supplier = $expenses->first()->supplier;
                return [
                    'supplier_name' => $supplier?->name ?? 'Unknown',
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5);
        
        // Get daily breakdown of inventory expenses
        $dailyBreakdown = $inventoryExpenses
            ->groupBy(fn($expense) => $expense->date->format('Y-m-d'))
            ->map(fn($expenses, $date) => [
                'date' => $date,
                'total' => $expenses->sum('amount'),
                'count' => $expenses->count(),
            ])
            ->values();
        
        // Monthly spend for chart (last 6 months, filtered by category if needed)
        $monthlySpend = collect(range(5, 0))->map(function (int $offset) use ($request) {
            $month = now()->subMonths($offset);
            $query = Expense::whereYear('date', $month->year)
                ->whereMonth('date', $month->month);
            
            // If filtering by inventory, only include inventory expenses in chart
            if ($request->get('category_filter') === 'inventory') {
                $query->where('category', 'inventory');
            }
            
            $total = $query->sum('amount');
            
            return [
                'label'  => $month->format('M'),
                'amount' => (float) $total,
            ];
        })->values();
        
        // Get all suppliers for dropdown
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('vendor/Expenses', [
            'expenses' => $expenses,
            'monthlySpend' => $monthlySpend,
            'inventoryStats' => [
                'total_spend' => $totalInventorySpend,
                'total_transactions' => $totalInventoryTransactions,
                'average_per_transaction' => $totalInventoryTransactions > 0 
                    ? $totalInventorySpend / $totalInventoryTransactions 
                    : 0,
                'largest_purchase' => $inventoryExpenses->max('amount') ?? 0,
            ],
            'expensesBySupplier' => $expensesBySupplier,
            'dailyBreakdown' => $dailyBreakdown,
            'suppliers' => $suppliers,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'category_filter' => $request->get('category_filter', 'all'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:0',
            'date'     => 'required|date',
            'method'   => 'required|string|max:100',
            'status'   => 'required|in:Paid,Pending,Scheduled',
            'note'     => 'nullable|string|max:500',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_number' => 'nullable|string|max:255',
        ]);

        // Add user_id and tenant_id if your table has these columns
        $validated['user_id'] = auth()->id();
        $validated['tenant_id'] = tenant()?->id;
        
        Expense::create($validated);

        return back()->with('success', 'Expense added successfully.');
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'amount'   => 'required|numeric|min:0',
            'date'     => 'required|date',
            'method'   => 'required|string|max:100',
            'status'   => 'required|in:Paid,Pending,Scheduled',
            'note'     => 'nullable|string|max:500',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'reference_number' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return back()->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return back()->with('success', 'Expense deleted successfully.');
    }
    
    /**
     * API endpoint to get inventory spend data for charts
     */
    public function getInventorySpendData(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $expenses = Expense::where('category', 'inventory')
            ->whereBetween('date', [$startDate, $endDate])
            ->with('supplier')
            ->get();
        
        return response()->json([
            'total_spend' => $expenses->sum('amount'),
            'total_transactions' => $expenses->count(),
            'by_supplier' => $expenses->groupBy('supplier_id')->map(function($expenses) {
                $supplier = $expenses->first()->supplier;
                return [
                    'supplier_name' => $supplier?->name ?? 'Unknown',
                    'total' => $expenses->sum('amount'),
                    'count' => $expenses->count(),
                ];
            })->values(),
            'daily' => $expenses->groupBy(fn($e) => $e->date->format('Y-m-d'))->map(function($expenses, $date) {
                return [
                    'date' => $date,
                    'total' => $expenses->sum('amount'),
                ];
            })->values(),
        ]);
    }
}