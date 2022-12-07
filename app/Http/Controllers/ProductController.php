<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Spatie\UrlSigner\Laravel\Facades\UrlSigner;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::latest()->paginate(10), 200);
    }

    public function store(Request $request)
    {
        // Validate data
        $this->validate($request, [
            'name' => ['required', 'string', 'max:50'],
            // or we can use other logic to validate
            // 'name' => 'required|string|max:50'
            'description' => ['required', 'string', 'max:250'],
            'file' => ['required', 'image', 'mimes:jpg,png,jpeg', 'max:5000'],
            'type' => ['required', 'integer', Rule::in([1, 2, 3])]
        ]);

        $fileNameToStore = '';

        // Handle file upload
        if($request->hasFile('file')) {
            // Get file name with extension
            $filenameWithExt = $request->file('file')->getClientOriginalName();
            // Get just file name
            $fileName = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            // Get just ext
            $extension = $request->file('file')->getClientOriginalExtension();

            // File name to store
            $fileNameToStore = $fileName .'_'.time().'.'.$extension;

            // Upload Image
            $filePathToStore = $request->file('file')->storeAs('public/files', $fileNameToStore);

            // $fileNameToStore = 'files/' . $fileNameWithTimeAndExt;
        }

        // Create new product
        $product = new Product;
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->file = $fileNameToStore;
        $product->type = $request->input('type');
        $product->save();


        return response()->json($product, 201);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);

        // This support for AWS S3 bucket driver
        // $url = Storage::temporaryUrl('public/files/' . $product->file, now()->addMinutes(1));

        // $image = Storage::get('public/files/' . $product->file);

        // We need to run storage:link artisan command to access all private files
        // I use a package called spatie/laravel-url-signer to generate temporary url
        // Here is link to spatie/laravel-url-signer github page https://github.com/spatie/laravel-url-signer
        $url =  UrlSigner::sign(url('').'/storage/files/' . $product->file, now()->addMinutes(10));

        $data = collect();
        $data->put('name', $product->name);
        $data->put('description', $product->description);
        $data->put('type', $product->type);
        $data->put('id', $product->id);
        $data->put('url', $url);        

        return response()->json($data, 200);
    }
}
