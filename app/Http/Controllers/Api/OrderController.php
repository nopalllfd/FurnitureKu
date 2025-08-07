<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     * path="/api/orders",
     * summary="Get the authenticated user's order history",
     * description="Returns a list of orders for the logged in user.",
     * tags={"Transactions"},
     * security={{"bearerAuth":{}}},
     * @OA\Response(
     * response=200,
     * description="Successful operation"
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated"
     * )
     * )
     */
    public function index(Request $request)
    {
        // Ambil order milik user yang sedang login, beserta item dan produknya
        $orders = $request->user()->orders()->with('items.product')->latest()->get();

        // Di sini bisa digunakan API Resource untuk format yang lebih rapi,
        // tapi untuk sekarang kita kembalikan langsung.
        return response()->json($orders);
    }

    /**
     * @OA\Post(
     * path="/api/orders",
     * summary="Create a new order (checkout)",
     * description="Creates a new order from items in the cart.",
     * tags={"Transactions"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     * required=true,
     * description="Order data",
     * @OA\JsonContent(
     * required={"shipping_address", "items"},
     * @OA\Property(property="shipping_address", type="string", example="Jl. Jenderal Sudirman No. 1, Jakarta"),
     * @OA\Property(
     * property="items",
     * type="array",
     * @OA\Items(
     * type="object",
     * required={"product_id", "quantity"},
     * @OA\Property(property="product_id", type="integer", example=1),
     * @OA\Property(property="quantity", type="integer", example=2)
     * )
     * )
     * )
     * ),
     * @OA\Response(response=201, description="Order created successfully"),
     * @OA\Response(response=422, description="Validation error"),
     * @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();
        $totalAmount = 0;
        
        // Ambil user yang sedang login dari request.
        $user = $request->user();

        // Gunakan DB Transaction untuk memastikan integritas data
        $order = DB::transaction(function () use ($validated, $user, &$totalAmount) {
            // 1. Ambil data produk dari DB untuk mendapatkan harga asli
            $productIds = collect($validated['items'])->pluck('product_id');
            $products = Product::find($productIds);

            // 2. Hitung total harga dari server, bukan dari client
            foreach ($validated['items'] as $item) {
                $product = $products->find($item['product_id']);
                // Cek stok
                if ($product->stock < $item['quantity']) {
                    abort(422, 'Stock for product ' . $product->name . ' is not enough.');
                }
                $totalAmount += $product->price * $item['quantity'];
            }

            // 3. Buat order utama
            $order = Order::create([
                'user_id' => $user->id, // Gunakan id dari user yang sudah diautentikasi
                'order_code' => 'INV/' . now()->format('Ymd') . '/' . strtoupper(Str::random(6)),
                'shipping_address' => $validated['shipping_address'],
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            // 4. Buat order items
            foreach ($validated['items'] as $item) {
                $product = $products->find($item['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price, // Simpan harga saat ini
                ]);
                
                // 5. Kurangi stok produk
                $product->decrement('stock', $item['quantity']);
            }

            return $order;
        });

        return response()->json([
            'message' => 'Order created successfully',
            'order_code' => $order->order_code,
            'total' => $order->total_amount,
        ], 201);
    }

        /**
     * @OA\Get(
     *     path="/api/my-orders",
     *     summary="Get my orders (buyer only)",
     *     description="Retrieve a list of orders that belong to the authenticated buyer.",
     *     tags={"Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Daftar pesanan Anda"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="user_id", type="integer", example=5),
     *                     @OA\Property(property="order_code", type="string", example="INV/20250807/ABC123"),
     *                     @OA\Property(property="shipping_address", type="string", example="Jl. Soekarno Hatta No.123"),
     *                     @OA\Property(property="total_amount", type="number", format="float", example=250000),
     *                     @OA\Property(property="status", type="string", example="pending"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-07T09:12:34.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-07T09:12:34.000000Z"),
     *                     @OA\Property(
     *                         property="product",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Meja Makan"),
     *                             @OA\Property(property="price", type="number", example=50000)
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */

     // Buyer melihat pesanan miliknya sendiri
    public function myOrders()
    {
        $orders = Order::where('user_id', Auth::id())->with('product')->get();

        return response()->json([
            'message' => 'Daftar pesanan Anda',
            'data' => $orders,
        ]);
    }
}