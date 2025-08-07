<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;


/**
 * @OA\Tag(
 *     name="Products",
 *     description="API untuk manajemen produk"
 * )
 */
/**
 * @OA\Schema(
 *     schema="Product",
 *     type="object",
 *     title="Product",
 *     required={"id", "name", "price", "stock"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Meja Belajar"),
 *     @OA\Property(property="slug", type="string", example="meja-belajar"),
 *     @OA\Property(property="category_id", type="integer", example=2),
 *     @OA\Property(property="description", type="string", example="Meja kayu jati"),
 *     @OA\Property(property="price", type="number", format="float", example=120000.00),
 *     @OA\Property(property="stock", type="integer", example=10),
 *     @OA\Property(property="image", type="string", example="uploads/products/image.jpg"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */

class ProductController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/products",
 *     tags={"Products"},
 *     summary="Get list of products",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="search",
 *         in="query",
 *         required=false,
 *         description="Cari produk berdasarkan nama atau deskripsi",
 *         @OA\Schema(type="string")
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of products",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="List Produk"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Product")
 *             )
 *         )
 *     )
 * )
 */

    public function index(Request $request)
    {
        $query = Product::with('category');

        // Tambahkan fitur search
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Ambil hasil
        $products = $query->get();

        return response()->json([
            'message' => 'List Produk',
            'data' => $products
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Create a new product",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name", "category_id", "price", "stock"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="stock", type="integer"),
     *                 @OA\Property(property="image", type="file"),
     *                 @OA\Property(property="is_active", type="boolean")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->merge([
            'is_active' => filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        ]);

        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|numeric',
            'image' => 'nullable|file|image|mimes:jpg,jpeg,png|max:2048',
            'is_active' => 'required|boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('uploads/products', 'public');
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'image' => $imagePath,
            'is_active' => $request->is_active,
        ]);

        return response()->json([
            'message' => 'Product created successfully',
            'data' => $product,
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Get product by ID",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product detail",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'message' => 'Detail Produk',
            'data' => $product
        ]);
    }

 /**
 * @OA\Post(
 *     path="/api/products/{id}",
 *     tags={"Products"},
 *     summary="Update product",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the product",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 required={"_method", "name", "category_id", "description", "price", "stock", "is_active"},
 *                 @OA\Property(property="_method", type="string", example="PUT",),
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="category_id", type="integer"),
 *                 @OA\Property(property="description", type="string"),
 *                 @OA\Property(property="price", type="number", format="float"),
 *                 @OA\Property(property="stock", type="integer"),
 *                 @OA\Property(property="image", type="file"),
 *                 @OA\Property(property="is_active", type="boolean")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string"),
 *             @OA\Property(property="data", type="object")
 *         )
 *     )
 * )
 */



public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);

    $request->merge([
        'is_active' => filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
    ]);

    $validated = $request->validate([
        'name' => 'required|string',
        'category_id' => 'required|exists:categories,id',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'stock' => 'required|integer',
        'is_active' => 'required|boolean',
        'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($request->hasFile('image')) {
        // Hapus gambar lama jika ada
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        // Simpan gambar baru
        $imagePath = $request->file('image')->store('uploads/products', 'public');
        $validated['image'] = $imagePath;
    }

    $validated['slug'] = Str::slug($request->name); // Tambahkan slug jika ingin diperbarui

    $product->update($validated);

    return response()->json([
        'message' => 'Produk berhasil diperbarui',
        'data' => $product
    ]);
}





    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Delete product",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }
}
