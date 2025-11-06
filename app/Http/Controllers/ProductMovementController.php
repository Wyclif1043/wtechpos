<?php

namespace App\Http\Controllers;

use App\Models\ProductMovement;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductMovementController extends Controller
{
    public function __construct()
    {
        // $this->middleware('permission:inventory.movements.view')->only(['index', 'show']);
        // $this->middleware('permission:inventory.movements.create')->only(['create', 'store']);
        // $this->middleware('permission:inventory.movements.approve')->only(['approve', 'reject']);
        // $this->middleware('permission:inventory.movements.process')->only(['ship', 'deliver', 'receive']);
        // $this->middleware('permission:inventory.movements.receive')->only(['receive']);
    }

    public function index()
    {
        $user = auth()->user();
        
        // Show transfers based on user's branch and permissions
        $movements = ProductMovement::with(['product', 'fromBranch', 'toBranch', 'requestedBy', 'approvedBy'])
            ->when($user->branch_id, function($query) use ($user) {
                // Users see transfers related to their branch
                return $query->where(function($q) use ($user) {
                    $q->where('from_branch_id', $user->branch_id)
                      ->orWhere('to_branch_id', $user->branch_id);
                });
            })
            ->latest()
            ->paginate(20);

        return view('product-movements.index', compact('movements'));
    }

    public function create()
    {
        $user = auth()->user();
        
        if (!$user->branch_id) {
            // Get the first active branch or create a default one
            $defaultBranch = Branch::active()->first();
            
            if (!$defaultBranch) {
                return redirect()->route('product-movements.index')
                    ->with('error', 'No branches available. Please contact administrator to set up branches.');
            }
            
            // Assign user to default branch
            $user->update(['branch_id' => $defaultBranch->id]);
        }

        $userBranch = Branch::findOrFail($user->branch_id);
        
        // Get products from user's branch
        $products = Product::active()
            ->where('branch_id', $user->branch_id)
            ->where('stock_quantity', '>', 0)
            ->get();

        // Get destination branches (all except user's branch)
        $branches = Branch::active()
            ->where('id', '!=', $user->branch_id)
            ->get();

        return view('product-movements.create', compact('products', 'branches', 'userBranch'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->branch_id) {
            return redirect()->back()->with('error', 'Your account is not assigned to any branch.');
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'to_branch_id' => 'required|exists:branches,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string',
            'urgent' => 'boolean'
        ]);

        // Verify product belongs to user's branch and has sufficient stock
        $sourceProduct = Product::where('id', $validated['product_id'])
            ->where('branch_id', $user->branch_id)
            ->firstOrFail();

        if (!$sourceProduct->hasSufficientStock($validated['quantity'])) {
            return redirect()->back()->with('error', 
                "Insufficient stock. Available: {$sourceProduct->stock_quantity}, Requested: {$validated['quantity']}");
        }

        DB::transaction(function () use ($validated, $user) {
            // Create transfer request
            $movement = ProductMovement::create([
                'product_id' => $validated['product_id'],
                'from_branch_id' => $user->branch_id,
                'to_branch_id' => $validated['to_branch_id'],
                'quantity' => $validated['quantity'],
                'status' => 'pending',
                'reason' => $validated['reason'],
                'requested_by' => $user->id,
                'reference_number' => ProductMovement::generateReferenceNumber(),
                'notes' => $validated['notes'],
                'is_urgent' => $validated['urgent'] ?? false
            ]);

            // Generate transfer receipt PDF
            $this->generateTransferReceipt($movement);
        });

        return redirect()->route('product-movements.index')
            ->with('success', 'Transfer request created successfully and pending approval.');
    }

    public function show(ProductMovement $productMovement)
    {
        $productMovement->load(['product', 'fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'processedBy', 'receivedBy']);
        return view('product-movements.show', compact('productMovement'));
    }

    public function approve(ProductMovement $productMovement)
    {
        if (!$productMovement->canBeApproved()) {
            return redirect()->back()->with('error', 'This transfer cannot be approved.');
        }

        DB::transaction(function () use ($productMovement) {
            // Deduct stock from source branch
            $sourceProduct = Product::where('id', $productMovement->product_id)
                ->where('branch_id', $productMovement->from_branch_id)
                ->firstOrFail();

            $sourceProduct->deductQuantity($productMovement->quantity);

            // Update movement status
            $productMovement->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // Update receipt with approval info
            $this->generateTransferReceipt($productMovement);
        });

        return redirect()->back()->with('success', 'Transfer approved successfully. Stock has been deducted from source branch.');
    }

    public function reject(ProductMovement $productMovement)
    {
        if ($productMovement->status !== 'pending') {
            return redirect()->back()->with('error', 'Only pending transfers can be rejected.');
        }

        $productMovement->update([
            'status' => 'cancelled',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return redirect()->back()->with('success', 'Transfer request rejected.');
    }

    public function ship(ProductMovement $productMovement)
    {
        if (!$productMovement->canBeShipped()) {
            return redirect()->back()->with('error', 'This transfer cannot be shipped.');
        }

        $validated = request()->validate([
            'tracking_number' => 'nullable|string|max:100'
        ]);

        $productMovement->update([
            'status' => 'shipped',
            'processed_by' => auth()->id(),
            'shipped_at' => now(),
            'tracking_number' => $validated['tracking_number'] ?? null
        ]);

        // Update receipt with shipping info
        $this->generateTransferReceipt($productMovement);

        return redirect()->back()->with('success', 'Transfer marked as shipped.');
    }

    public function receive(ProductMovement $productMovement)
    {
        $user = auth()->user();
        
        // Check if user is from the receiving branch
        if ($user->branch_id != $productMovement->to_branch_id) {
            return redirect()->back()->with('error', 'You can only receive transfers for your branch.');
        }

        if (!$productMovement->canBeReceived()) {
            return redirect()->back()->with('error', 'This transfer cannot be received.');
        }

        DB::transaction(function () use ($productMovement, $user) {
            // Add stock to destination branch
            $destinationProduct = Product::where('id', $productMovement->product_id)
                ->where('branch_id', $productMovement->to_branch_id)
                ->first();

            if (!$destinationProduct) {
                // Create product in destination branch if it doesn't exist
                $sourceProduct = Product::find($productMovement->product_id);
                $destinationProduct = $sourceProduct->replicate();
                $destinationProduct->branch_id = $productMovement->to_branch_id;
                $destinationProduct->stock_quantity = 0;
                $destinationProduct->save();
            }

            $destinationProduct->addQuantity($productMovement->quantity);

            // Update movement status
            $productMovement->update([
                'status' => 'delivered',
                'received_by' => $user->id,
                'delivered_at' => now()
            ]);

            // Update receipt with delivery info
            $this->generateTransferReceipt($productMovement);
        });

        return redirect()->back()->with('success', 'Transfer received successfully. Stock has been added to your branch.');
    }

    public function downloadReceipt(ProductMovement $productMovement)
    {
        $receiptPath = $this->getReceiptPath($productMovement);
        
        if (!Storage::disk('public')->exists($receiptPath)) {
            $this->generateTransferReceipt($productMovement);
        }

        return Storage::disk('public')->download($receiptPath);
    }

    public function viewReceipt(ProductMovement $productMovement)
    {
        $receiptPath = $this->getReceiptPath($productMovement);
        
        if (!Storage::disk('public')->exists($receiptPath)) {
            $this->generateTransferReceipt($productMovement);
        }

        return response()->file(Storage::disk('public')->path($receiptPath));
    }

    private function generateTransferReceipt(ProductMovement $movement)
    {
        $movement->load(['product', 'fromBranch', 'toBranch', 'requestedBy', 'approvedBy', 'processedBy', 'receivedBy']);
        
        $pdf = PDF::loadView('pdf.transfer-receipt', compact('movement'));
        
        $receiptPath = $this->getReceiptPath($movement);
        
        Storage::disk('public')->put($receiptPath, $pdf->output());
        
        // Update movement with receipt path
        $movement->update(['receipt_path' => $receiptPath]);
        
        return $receiptPath;
    }

    private function getReceiptPath(ProductMovement $movement)
    {
        return "transfer-receipts/{$movement->reference_number}.pdf";
    }

    public function getProductStock(Request $request)
    {
        $user = auth()->user();
        $productId = $request->input('product_id');

        $product = Product::where('id', $productId)
            ->where('branch_id', $user->branch_id)
            ->first();

        return response()->json([
            'success' => true,
            'stock' => $product ? $product->stock_quantity : 0,
            'product_name' => $product ? $product->name : 'N/A',
            'branch_name' => $product ? $product->branch->name : 'N/A'
        ]);
    }
}