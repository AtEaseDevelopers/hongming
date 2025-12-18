<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Http\Requests;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\ProductRepository;
use Flash;
use App\Http\Controllers\AppBaseController;
use Response;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\SpecialPrice;
use App\Models\foc;
use Illuminate\Support\Facades\Session;
use Exception;

class ProductController extends AppBaseController
{
    /** @var ProductRepository $productRepository*/
    private $productRepository;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepository = $productRepo;
    }

    /**
     * Display a listing of the Product.
     *
     * @param ProductDataTable $productDataTable
     *
     * @return Response
     */
    public function index(ProductDataTable $productDataTable)
    {
        return $productDataTable->render('products.index');
    }

    /**
     * Show the form for creating a new Product.
     *
     * @return Response
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Store a newly created Product in storage.
     *
     * @param CreateProductRequest $request
     *
     * @return Response
     */
    public function store(CreateProductRequest $request)
    {
        $input = $request->all();

        if(str_contains($input['name'],'"')){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain double quote');
        }

        if(str_contains($input['name'],'\'')){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain single quote');
        }

        // If product type is Material, set uoms to null
        if (isset($input['type']) && $input['type'] == 0) {
            $input['uoms'] = null;
            $input['default_price'] = 0;
        } else {
            // Validate UOMs for Machine type
            $hasDefault = false;
            $uomNames = [];
            if (isset($input['uoms']) && is_array($input['uoms'])) {
                foreach ($input['uoms'] as $uom) {
                    if (empty($uom['name']) || empty($uom['price'])) {
                        return Redirect::back()->withInput($input)->withErrors('All UOMs must have both name and price for Machine type products.');
                    }
                    
                    $uomName = strtolower(trim($uom['name']));
                    if (in_array($uomName, $uomNames)) {
                        return Redirect::back()->withInput($input)->withErrors('Each UOM name must be unique for this product.');
                    }
                    $uomNames[] = $uomName;
                }
                
                // Calculate default_price from the first UOM
                if (count($input['uoms']) > 0) {
                    $input['default_price'] = floatval($input['uoms'][0]['price']);
                }
            } else {
                return Redirect::back()->withInput($input)->withErrors('At least one UOM is required for Machine type products.');
            }
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepository->create($input);
            
            DB::commit();
            
            Flash::success($input['code'].__('products.saved_successfully'));
            return redirect(route('products.index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error saving product: ' . $e->getMessage());
            return Redirect::back()->withInput($input);
        }
    }

    /**
     * Display the specified Product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));

            return redirect(route('products.index'));
        }

        return view('products.show')->with('product', $product);
    }

    /**
     * Show the form for editing the specified Product.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));

            return redirect(route('products.index'));
        }

        return view('products.edit')->with('product', $product);
    }

    /**
     * Update the specified Product in storage.
     *
     * @param int $id
     * @param UpdateProductRequest $request
     *
     * @return Response
     */
    public function update($id, UpdateProductRequest $request)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));

            return redirect(route('products.index'));
        }

        $input = $request->all();

        if(str_contains($input['name'],'"')){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain double quote');
        }

        if(str_contains($input['name'],'\'')){
            return Redirect::back()->withInput($input)->withErrors('The name cannot contain single quote');
        }

        // If product type is Material, set uoms to null
        if (isset($input['type']) && $input['type'] == 0) {
            $input['uoms'] = null;
            $input['default_price'] = 0;
        } else {
            // Validate UOMs for Machine type
            $uomNames = [];
            if (isset($input['uoms']) && is_array($input['uoms'])) {
                foreach ($input['uoms'] as $uom) {
                    if (empty($uom['name']) || empty($uom['price'])) {
                        return Redirect::back()->withInput($input)->withErrors('All UOMs must have both name and price for Machine type products.');
                    }
                    
                    $uomName = strtolower(trim($uom['name']));
                    if (in_array($uomName, $uomNames)) {
                        return Redirect::back()->withInput($input)->withErrors('Each UOM name must be unique for this product.');
                    }
                    $uomNames[] = $uomName;
                }
                
                // Calculate default_price from the first UOM
                if (count($input['uoms']) > 0) {
                    $input['default_price'] = floatval($input['uoms'][0]['price']);
                }
            } else {
                // If updating from Material to Machine, require UOMs
                if ($product->type == 0 && $input['type'] == 1) {
                    return Redirect::back()->withInput($input)->withErrors('At least one UOM is required when changing to Machine type.');
                }
                
                // Keep existing UOMs if they exist
                if (empty($product->uoms)) {
                    return Redirect::back()->withInput($input)->withErrors('At least one UOM is required for Machine type products.');
                }
            }
        }

        DB::beginTransaction();
        try {
            $product = $this->productRepository->update($input, $id);
            
            DB::commit();
            
            Flash::success($product->code.__('products.updated_successfully'));
            return redirect(route('products.index'));
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error updating product: ' . $e->getMessage());
            return Redirect::back()->withInput($input);
        }
    }

    /**
     * Remove the specified Product from storage.
     *
     * @param int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        $id = Crypt::decrypt($id);
        $product = $this->productRepository->find($id);

        if (empty($product)) {
            Flash::error(__('products.product_not_found'));

            return redirect(route('products.index'));
        }

        // $Invoice = Invoice::where('product_id',$id)->get()->toArray();
        // if(count($Invoice)>0){
        //     Flash::error('Unable to delete '.$product->name.', '.$product->name.' is being used in Invoice');

        //     return redirect(route('products.index'));
        // }

        $SpecialPrice = SpecialPrice::where('product_id',$id)->get()->toArray();
        if(count($SpecialPrice)>0){
            Flash::error('Unable to delete '.$product->name.', '.$product->name.' is being used in Special Price');

            return redirect(route('products.index'));
        }

        $foc = foc::where('product_id',$id)->get()->toArray();
        if(count($foc)>0){
            Flash::error('Unable to delete '.$product->name.', '.$product->name.' is being used in Foc');

            return redirect(route('products.index'));
        }

        $this->productRepository->delete($id);

        Flash::success($product->code.__('products.deleted_successfully'));

        return redirect(route('products.index'));
    }

    public function massdestroy(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];

        $count = 0;

        foreach ($ids as $id) {

            $Invoice = InvoiceDetail::where('product_id',$id)->get()->toArray();
            if(count($Invoice)>0){
                continue;
            }

            $SpecialPrice = SpecialPrice::where('product_id',$id)->get()->toArray();
            if(count($SpecialPrice)>0){
                continue;
            }

            $foc = foc::where('product_id',$id)->get()->toArray();
            if(count($foc)>0){
                continue;
            }

            $count = $count + Product::destroy($id);
        }

        return $count;
    }

    public function massupdatestatus(Request $request)
    {
        $data = $request->all();
        $ids = $data['ids'];
        $status = $data['status'];

        $count = Product::whereIn('id',$ids)->update(['status'=>$status]);

        return $count;
    }
    
    public function syncXero(Request $req)
    {
        try {
            $redirect_uri = config('app.url') . '/products/sync-xero';
            $xero = new XeroController($redirect_uri);

            if ($req->has('ids')) {
                $ids = explode(',', $req->ids);
                Session::put('ids_to_sync_xero', $ids);
            }
            // Get Xero's access token
            if ($req->has('code')) {
                $res = $xero->getToken($req->code);
                if (!$res->ok()) {
                    throw new Exception('Failed to get xero access token.');
                }
            }
            // Xero auth
            $res = $xero->auth();
            if ($res !== true) {
                return $res;
            }
            // Sync products
            $ids = Session::get('ids_to_sync_xero');
            $products = Product::whereIn('id',$ids)->get();
            
            for ($i = 0; $i < count($products) ;$i++) {
                // Use default price for Xero sync
                $defaultPrice = $products[$i]->default_price;
                $res = $xero->createItem($products[$i]->code, $products[$i]->name, $defaultPrice);

                if (!$res->ok()) {  
                    throw new Exception('Failed to sync product.');
                }
            }
            
            Flash::success('Products synced to Xero.');
            return redirect(route('products.index'));
        } catch (\Throwable $th) {
            report($th);
            
            Flash::error('Something went wrong. Please contact administator.');
            return redirect(route('products.index'));
        }
    }

    /**
     * API to get product UOMs
     */
    public function getProductUoms($id)
    {
        try {
            $id = Crypt::decrypt($id);
            $product = Product::find($id);
            
            if (!$product) {
                return response()->json(['error' => 'Product not found'], 404);
            }
            
            return response()->json([
                'uoms' => $product->uoms ?? [],
                'default_price' => $product->default_price
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid product ID'], 400);
        }
    }
}