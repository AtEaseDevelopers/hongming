<?php

namespace App\Http\Controllers;

use App\DataTables\MachineRentalDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateMachineRentalRequest;
use App\Http\Requests\UpdateMachineRentalRequest;
use App\Repositories\MachineRentalRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MachineRental;
use App\Models\MachineRentalItem;
use App\Models\Customer;
use App\Models\Company;
use App\Models\Product;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MachineRentalController extends AppBaseController
{
    /** @var MachineRentalRepository $machineRentalRepository*/
    private $machineRentalRepository;

    public function __construct(MachineRentalRepository $machineRentalRepo)
    {
        $this->machineRentalRepository = $machineRentalRepo;
    }

    /**
     * Display a listing of the MachineRental.
     *
     * @param MachineRentalDataTable $machineRentalDataTable
     *
     * @return Response
     */
    public function index(MachineRentalDataTable $machineRentalDataTable)
    {
        return $machineRentalDataTable->render('machine_rental.index');
    }

    /**
     * Show the form for creating a new MachineRental.
     *
     * @return Response
     */
    public function create()
    {
        $customers = Customer::pluck('company', 'id');
        $company = Company::pluck('name', 'id');
        $productItems = Product::where('type', 1)->pluck('name', 'id');
        
        // Get products with their UOMs for JavaScript
        $productsWithUoms = Product::where('type', 1)
            ->select('id', 'name', 'uoms')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'uoms' => $product->uoms ?? []
                ];
            });

        return view('machine_rental.create', compact('customers', 'company', 'productItems', 'productsWithUoms'));
    }

    /**
     * Store a newly created MachineRental in storage.
     *
     * @param CreateMachineRentalRequest $request
     *
     * @return Response
     */
    public function store(CreateMachineRentalRequest $request)
    {
        $input = $request->all();
        $input['date'] = Carbon::parse($input['date'])->format('Y-m-d');

        DB::transaction(function () use ($request, $input) {
            // Create machine rental
            $machineRental = MachineRental::create([
                'company_id' => $input['company_id'],
                'delivery_order_number' => $input['delivery_order_number'],
                'customer_id' => $input['customer_id'],
                'date' => $input['date'],
                'lorry_number' => $input['lorry_number'] ?? null,
                'issued_by' => $request->user()->id ?? null,
                'total_amount' => $input['total_amount'],
                'remark' => $input['remark'] ?? null,
            ]);

            // Create rental items
            if (isset($input['product_id']) && is_array($input['product_id'])) {
                foreach ($input['product_id'] as $index => $productId) {
                    if (!empty($productId)) {
                        MachineRentalItem::create([
                            'machine_rental_id' => $machineRental->id,
                            'product_id' => $productId,
                            'uom' => $input['uom'][$index] ?? null,
                            'description' => $input['description'][$index] ?? null,
                            'quantity' => $input['quantity'][$index],
                            'unit_price' => $input['unit_price'][$index],
                            'amount' => $input['quantity'][$index] * $input['unit_price'][$index],
                        ]);
                    }
                }
            }
        });

        Flash::success('Machine Rental Order saved successfully.');

        return redirect(route('machineRentals.index'));
    }

    public function getNextRentalNumber(Request $request)
    {
        try {
            $companyId = $request->get('company_id');
            
            if (!$companyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company ID is required'
                ], 400);
            }

            $rentalNumber = $this->generateNextRentalNumber($companyId);
            
            if (!$rentalNumber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate rental number'
                ], 500);
            }
            
            return response()->json([
                'success' => true,
                'rental_number' => $rentalNumber
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating rental number: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate next sequential rental number for a company
     *
     * @param int $companyId
     * @return string|null
     */
    private function generateNextRentalNumber($companyId)
    {
        $company = Company::find($companyId);
        if (!$company) {
            return null;
        }
        
        $prefix = $company->machine_prefix;
        
        if (empty($prefix)) {
            $prefix = 'MC';
        }
        
        // Get the last machine rental number for this company with the same prefix
        $lastRental = MachineRental::where('company_id', $companyId)
            ->where('delivery_order_number', 'like', $prefix . '-%')
            ->orderBy('id', 'desc')
            ->first();
        
        $nextNumber = 1;
        
        if ($lastRental && $lastRental->delivery_order_number) {
            // Extract the number part using the prefix
            $numberPart = str_replace($prefix . '-', '', $lastRental->delivery_order_number);
            $lastNumber = intval($numberPart);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }
        
        $rentalNumber = $prefix . '-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        
        return $rentalNumber;
    }

/**
     * Display the specified MachineRental.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $machineRental = MachineRental::with(['customer', 'company', 'items.product'])->find($id);

        if (empty($machineRental)) {
            Flash::error('Machine Rental Order not found');

            return redirect(route('machineRentals.index'));
        }

        return view('machine_rental.show')->with('machineRental', $machineRental);
    }

    /**
     * Show the form for editing the specified MachineRental.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $machineRental = MachineRental::with(['items.product'])->find($id);

        if (empty($machineRental)) {
            Flash::error('Machine Rental Order not found');

            return redirect(route('machineRentals.index'));
        }

        $customers = Customer::pluck('company', 'id');
        $company = Company::pluck('name', 'id');
        $productItems = Product::where('type', 1)->pluck('name', 'id');
        
        // Get products with their UOMs for JavaScript
        $productsWithUoms = Product::where('type', 1)
            ->select('id', 'name', 'uoms')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'uoms' => $product->uoms ?? []
                ];
            });
        
        return view('machine_rental.edit', compact('machineRental', 'customers', 'company', 'productItems', 'productsWithUoms'));    
    }

    /**
     * Update the specified MachineRental in storage.
     *
     * @param int $id
     * @param UpdateMachineRentalRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateMachineRentalRequest $request)
    {
        $id = Crypt::decrypt($id);
        $machineRental = $this->machineRentalRepository->find($id);

        if (empty($machineRental)) {
            Flash::error('Machine Rental Order not found');

            return redirect(route('machineRentals.index'));
        }

        $input = $request->all();
        $input['date'] = Carbon::parse($input['date'])->format('Y-m-d');

        // Update machine rental
        DB::transaction(function () use ($request, $machineRental, $input) {
            // Update machine rental
            $machineRental->update([
                'company_id' => $input['company_id'],
                'delivery_order_number' => $input['delivery_order_number'],
                'customer_id' => $input['customer_id'],
                'date' => $input['date'],
                'lorry_number' => $input['lorry_number'] ?? null,
                'issued_by' => $request->user()->id ?? null,
                'total_amount' => $input['total_amount'],
                'remark' => $input['remark'] ?? null,
            ]);

            // Delete existing items
            $machineRental->items()->delete();

            // Create new rental items
            if (isset($input['product_id']) && is_array($input['product_id'])) {
                foreach ($input['product_id'] as $index => $productId) {
                    if (!empty($productId)) {
                        MachineRentalItem::create([
                            'machine_rental_id' => $machineRental->id,
                            'product_id' => $productId,
                            'uom' => $input['uom'][$index] ?? null,
                            'description' => $input['description'][$index] ?? null,
                            'quantity' => $input['quantity'][$index],
                            'unit_price' => $input['unit_price'][$index],
                            'amount' => $input['quantity'][$index] * $input['unit_price'][$index],
                        ]);
                    }
                }
            }
        });

        Flash::success('Machine Rental Order updated successfully.');

        return redirect(route('machineRentals.index'));
    }

    /**
     * Remove the specified MachineRental from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $machineRental = $this->machineRentalRepository->find($id);

        if (empty($machineRental)) {
            Flash::error('Machine Rental Order not found');

            return redirect(route('machineRentals.index'));
        }

        DB::transaction(function () use ($machineRental) {
            // Delete related items first
            $machineRental->items()->delete();
            // Delete the machine rental
            $this->machineRentalRepository->delete($machineRental->id);
        });

        Flash::success('Machine Rental Order deleted successfully.');

        return redirect(route('machineRentals.index'));
    }

    /**
     * Print the Machine Rental Delivery Order
     *
     * @param int $id
     *
     * @return Response
     */
    public function print($id)
    {
        $id = Crypt::decrypt($id);
        $machineRental = MachineRental::with([
            'customer', 
            'company', 
            'items.product',
            'user'
        ])->findOrFail($id);

        try {
            $pdf = Pdf::loadView('machine_rental.print', [
                'machineRental' => $machineRental,
                'company' => $machineRental->company
            ]);

            return $pdf->setPaper('A4', 'portrait')
                ->setOptions([
                    'isPhpEnabled' => true, 
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'Arial'
                ])
                ->stream('machine_rental_' . $machineRental->delivery_order_number . '.pdf');
        } catch(Exception $e) {
            abort(404);
        }
    }
}