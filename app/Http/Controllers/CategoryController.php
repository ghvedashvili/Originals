<?php

namespace App\Http\Controllers;

use App\Models\Category;
// თუ ექსპორტს იყენებ, დარწმუნდი რომ ესენი დაინსტალირებულია
 use App\Exports\ExportCategories; 
use Illuminate\Http\Request;
use PDF;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // თუ Middleware 'role' უკვე გაწერე bootstrap/app.php-ში, შეგიძლია ჩართო:
        // $this->middleware('role:admin,staff');
    }

    public function index()
    {
        return view('categories.index');
    }

    public function store(Request $request)
    {
        $this->validate($request, [
       'name' => 'required|string|unique:categories,name',
    ]);

        Category::create($request->all());

        return response()->json([
           'success'    => true,
           'message'    => 'Category Created Successfully'
        ]);
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
    
    $this->validate($request, [
        // უნიკალურობის შემოწმება, ოღონდ მიმდინარე ID-ის გამოკლებით
        'name' => 'required|string|unique:categories,name,' . $id,
    ]);

    $category->update($request->all());

    return response()->json([
        'success'    => true,
        'message'    => 'Category Updated'
    ]);
    }

    public function destroy($id)
    {
        Category::destroy($id);

        return response()->json([
            'success'    => true,
            'message'    => 'Category Deleted Successfully'
        ]);
    }

    // გადაკეთებული API მეთოდი Yajra-ს გარეშე
    public function apiCategories()
    {
        $categories = Category::all();

        $data = $categories->map(function($item) {
            return [
                'id'   => $item->id,
                'name' => $item->name,
                'action' => '
                    <a onclick="editForm('. $item->id .')" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</a> ' .
                    '<a onclick="deleteData('. $item->id .')" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i> Delete</a>'
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function exportCategoriesAll()
    {
        $categories = Category::all();
        $pdf = PDF::loadView('categories.CategoriesAllPDF', compact('categories'));
        return $pdf->download('categories.pdf');
    }
}