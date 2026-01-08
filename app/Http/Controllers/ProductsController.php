<?php

namespace App\Http\Controllers;

use App\Models\products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    // GET ALL PRODUCTS
    public function index()
    {
        $products = products::all();
        return response()->json($products, 200);
    }

    // STORE NEW PRODUCT (STORE FULL HTTP URL)
    public function store(Request $request)
    {
        $request->validate([
            "name"  => "required|string",
            "price" => "required|numeric",
            "image" => "required|image|mimes:jpg,jpeg,png,webp,gif"
        ]);

        $imageUrl = null;

        if ($request->hasFile("image")) {
            // store image
            $path = $request->file("image")->store("Items", "public");

            // convert to full URL
            $imageUrl = url('storage/' . $path);
        }

        $product = products::create([
            "name"  => $request->name,
            "price" => $request->price,
            "image" => $imageUrl
        ]);

        return response()->json($product, 201);
    }

    // UPDATE PRODUCT
    public function update(Request $request, $id)
    {
        $product = products::findOrFail($id);

        $request->validate([
            "name"  => "sometimes|string",
            "price" => "sometimes|numeric",
            "image" => "sometimes|image|mimes:jpg,jpeg,png,webp,gif"
        ]);

        // update image
        if ($request->hasFile("image")) {

            // delete old image
            if ($product->image) {
                $oldPath = str_replace(url('storage/') . '/', '', $product->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file("image")->store("Items", "public");
            $product->image = url('storage/' . $path);
        }

        $product->name  = $request->name  ?? $product->name;
        $product->price = $request->price ?? $product->price;
        $product->save();

        return response()->json($product, 200);
    }

    // DELETE PRODUCT
    public function destroy($id)
    {
        $product = products::findOrFail($id);

        // delete image file
        if ($product->image) {
            $path = str_replace(url('storage/') . '/', '', $product->image);
            Storage::disk('public')->delete($path);
        }

        $product->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }
}
