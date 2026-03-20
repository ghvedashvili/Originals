<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // თუ Middleware 'role' უკვე გაწერილი გაქვს bootstrap/app.php-ში, შეგიძლია ჩართო:
         $this->middleware('role:admin,staff');
    }

    public function index()
    {
        $category = Category::pluck('name', 'id'); 
        return view('products.index', compact('category'));
    }

    public function store(Request $request)
    {
        $this->validate($request , [
            'name'          => 'required|string',
            'Price_geo'     => 'required',
            'Price_usa'     => 'required',
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'category_id'   => 'required',
        ]);

        $input = $request->all();
        $input['price_geo'] = $request->Price_geo;
    $input['price_usa'] = $request->Price_usa;
        $input['image'] = null;

        if ($request->hasFile('image')){
            $filename = Str::slug($input['name'], '-') . '.' . $request->image->getClientOriginalExtension();
            $input['image'] = '/upload/products/' . $filename;
            $request->image->move(public_path('/upload/products/'), $filename);
        }

        Product::create($input);

        return response()->json([
            'success' => true,
            'message' => 'Product Created Successfully'
        ]);
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request , [
            'name'          => 'required|string',
            'Price_geo'     => 'required',
            'Price_usa'     => 'required',
            'category_id'   => 'required',
        ]);

        $input = $request->all();
        $product = Product::findOrFail($id);
        $input['price_geo'] = $request->Price_geo;
         $input['price_usa'] = $request->Price_usa;
        $input['image'] = $product->image;

        if ($request->hasFile('image')){
            if ($product->image && file_exists(public_path($product->image))){
                unlink(public_path($product->image));
            }
            $filename = Str::slug($input['name'], '-') . '.' . $request->image->getClientOriginalExtension();
            $input['image'] = '/upload/products/' . $filename;
            $request->image->move(public_path('/upload/products/'), $filename);
        }

        $product->update($input);

        return response()->json([
            'success' => true,
            'message' => 'Product Updated Successfully'
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && file_exists(public_path($product->image))){
            unlink(public_path($product->image));
        }

        Product::destroy($id);

        return response()->json([
            'success' => true,
            'message' => 'Product Deleted Successfully'
        ]);
    }

    public function apiProducts()
    {
        $products = Product::with('category')->get();

        $data = $products->map(function($product) {
            $photo = $product->image 
                ? '<img class="rounded-square" width="50" height="50" src="'. url($product->image) .'" alt="">'
                : 'No Image';

            return [
                'id'            => $product->id,
                'name'          => $product->name,
                'price_geo'     => number_format($product->price_geo, 2) . ' GEL',
                'show_photo'    => $photo,
                'category_name' => $product->category ? $product->category->name : 'N/A',
                'action'        => '
                    <a onclick="editForm('. $product->id .')" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</a> ' .
                    '<a onclick="deleteData('. $product->id .')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</a>'
            ];
        });

        return response()->json(['data' => $data]);
    }
}