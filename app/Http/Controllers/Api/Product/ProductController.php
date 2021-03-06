<?php


namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $findAllMenu = Product::orderBy('created_at', 'desc');
        if ($request->filter) {
            $findAllMenu = $findAllMenu->where('name', 'like', "%$request->filter%");
        }
        if ($request->has('per_page')) {
            $perPage = 7;
            if ($request->per_page) {
                $perPage = $request->per_page;
                $findAllMenu = $findAllMenu->paginate($perPage);
            }
        } else {
            $findAllMenu = $findAllMenu->get();
        }
        return ProductResource::collection($findAllMenu);
    }


    public function store(Request $request)
    {
        $validator = validator(request()->all(), [
            'name' => 'required|unique:products,name',
            'price' => 'required',
            'quantity' => 'required',
            'image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Task Failed', 'errors' => $validator->errors()], 400);
        }
        $payloadProduct = [
            'name' => request()->name,
            'price' => request()->price,
            'category_id' => request()->category_id,
            'quantity' => request()->quantity,
            'detail' => request()->detail,
        ];
        if (request('image')) {
            $payloadProduct['image'] = Storage::disk('s3')->put('product', request()->file('image'), 'public');
        }
        $res = Product::create(
            $payloadProduct
        );
        return response()->json($res);
        // return response()->json(["Message" => request('image')]);
    }


    public function show($id)
    {
        $data = Product::findOrFail($id);
        return new ProductResource($data);
    }


    public function update($id)
    {
        $validator = validator(request()->all(), [
            // 'name' => "required|unique:products,name,$id",
            // 'price' => 'required',
            // 'quantity' => 'required',
            // 'image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',
            'name' => "required|unique:products,name,$id",
            'price' => 'required',
            'quantity' => 'required',
            'image' => 'nullable|file|mimes:jpeg,png,jpg|max:2048',

        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Task Failed', 'errors' => $validator->errors()], 400);
        }
        $findProduct = Product::findOrFail($id);
        $payloadProduct = [
            'name' => request()->name,
            'price' => request()->price,
            'category_id' => request()->category_id,
            'quantity' => request()->quantity,
            'detail' => request()->detail,
        ];
        if (request()->file('image')) {
            if (Storage::disk('s3')->exists($findProduct->image)) {
                Storage::disk('s3')->delete($findProduct->image);
            }
            $payloadProduct['image'] = Storage::disk('s3')->put('product', request()->file('image'), 'public');
        }
        $findProduct->update(
            $payloadProduct
        );
        return response()->json($findProduct);
    }

    public function destroy($id)
    {
        $findProduct = Product::findOrFail($id);
        $deleteProduct = $findProduct->delete();

        $findProduct = Product::findOrFail($id);
        if (Storage::disk('s3')->exists($findProduct->image)) {
            Storage::disk('s3')->delete($findProduct->image);
        }
        if (!$deleteProduct) {
            return response()->json(['message' => "Delete Product Failed"], 500);
        }
        return response()->json($findProduct, 200);
    }
}