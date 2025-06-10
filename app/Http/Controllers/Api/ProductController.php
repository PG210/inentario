<?php

/**
 * @OA\Info(
 *     title="API de Inventario",
 *     version="1.0.0",
 *     description="Documentación de la API de Inventario"
 * )
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }
    
    /**
     * @OA\Get(
     *     path="/api/products",
     *     summary="Listar todos los productos",
     *     tags={"Productos"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de todos los productos",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="category_id", type="integer"),
     *                 @OA\Property(property="namecategory", type="string"),
     *                 @OA\Property(property="supplier_id", type="integer"),
     *                 @OA\Property(property="namesupplier", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number", format="float"),
     *                 @OA\Property(property="stock", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        $products = Product::join('categories', 'products.category_id', 'categories.id')
                    ->join('supplier', 'products.supplier_id', 'supplier.id')
                    ->select('products.id', 'products.category_id', 'categories.name as namecategory', 'products.supplier_id', 'supplier.name as namesupplier',
                            'products.name', 'products.description', 'products.price', 'products.stock')
                    ->orderBy('products.name', 'ASC')
                    ->get(); //paginate(10) 

        if(!$products)
           return response()->json(['message' => 'No hay datos, por favor registre al menos un producto'], 404);
        return response()->json($products);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     summary="Crear un nuevo producto. Requiere tener el rol Admin.",
     *     tags={"Productos"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "supplier_id", "name", "description", "price", "stock"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="supplier_id", type="integer", example=2),
     *             @OA\Property(property="name", type="string", example="Producto X"),
     *             @OA\Property(property="description", type="string", example="Descripción del producto"),
     *             @OA\Property(property="price", type="number", format="float", example=20000.00),
     *             @OA\Property(property="stock", type="integer", example=50)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Producto creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría creada exitosamente"),
     *             @OA\Property(property="product", type="object")
     *         )
     *     )
     * )
     */

    public function store(Request $request)
    {
        
        $request->validate([
            'category_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|gt:0',
            'stock' => 'required|integer|gt:0'
        ]);

        //validar las foraneas
        $category = Category::find($request->category_id);
        $supplier = Supplier::find($request->supplier_id);

        if(!$category)
           return response()->json(['message' => 'category_id no existe'], 404);

        if(!$supplier)
           return response()->json(['message' => 'supplier_id no existe'], 404);
        
        $product = New Product();
        $product->category_id = $request->category_id;
        $product->supplier_id = $request->supplier_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->save();
       
        return response()->json(['message' => 'Producto creado exitosamente', 'product' => $product], 201);
        
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     summary="Mostrar producto por id",
     *     description="Obtener la información de un producto específico por su id.",
     *     operationId="getProductoById",
     *     tags={"Productos"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID del producto",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Producto encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto encontrado"),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Producto XYZ"),
     *                 @OA\Property(property="description", type="string", example="Descripción del producto XYZ"),
     *                 @OA\Property(property="price", type="number", format="float", example=21999.99),
     *                 @OA\Property(property="stock", type="integer", example=10),
     *                 @OA\Property(property="category_id", type="integer", example=2),
     *                 @OA\Property(property="supplier_id", type="integer", example=3)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $product = Product::find($id);
        if(!$product)
           return response()->json(['message' => 'Producto no encontrado'], 404);
    
        return response()->json(['message' => 'Producto encontrado', 'producto' => $product], status:201);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     summary="Actualizar un producto existente",
     *     description="Actualiza los datos de un producto mediante su id. Requiere tener el rol Admin.",
     *     operationId="updateProducto",
     *     tags={"Productos"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id del producto a actualizar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"category_id", "supplier_id", "name", "description", "price", "stock"},
     *             @OA\Property(property="category_id", type="integer", example=1),
     *             @OA\Property(property="supplier_id", type="integer", example=2),
     *             @OA\Property(property="name", type="string", example="Producto actualizado"),
     *             @OA\Property(property="description", type="string", example="Nueva descripción del producto"),
     *             @OA\Property(property="price", type="number", format="float", example=31999.99),
     *             @OA\Property(property="stock", type="integer", example=20)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Producto actualizado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto actualizado exitosamente"),
     *             @OA\Property(property="product", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="supplier_id", type="integer", example=2),
     *                 @OA\Property(property="name", type="string", example="Producto actualizado"),
     *                 @OA\Property(property="description", type="string", example="Nueva descripción del producto"),
     *                 @OA\Property(property="price", type="number", format="float", example=31999.99),
     *                 @OA\Property(property="stock", type="integer", example=20)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product o llave foranea no encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado (si no es admin)",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No autorizado")
     *         )
     *     )
     * )
     */

    public function update(Request $request, string $id)
    {
        $request->validate([
            'category_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric|gt:0',
            'stock' => 'required|integer|gt:0'
        ]);

        $product = Product::find($id);
        if(!$product)
          return response()->json(['message' => 'Producto no encontrado'], 404);

        //validar las foraneas
        $category = Category::find($request->category_id);
        $supplier = Supplier::find($request->supplier_id);

        if(!$category)
           return response()->json(['message' => 'category_id no existe'], 404);

        if(!$supplier)
           return response()->json(['message' => 'supplier_id no existe'], 404);
        
        $product->category_id = $request->category_id;
        $product->supplier_id = $request->supplier_id;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->save();
       
        return response()->json(['message' => 'Producto actualizado exitosamente', 'product' => $product], 201);
        
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     summary="Eliminar un producto",
     *     description="Elimina un producto específico por id. Requiere tener el rol Admin.",
     *     operationId="destroyProducto",
     *     tags={"Productos"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id del producto a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Producto eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Producto no encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Producto no encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado (si no es admin)",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="No autorizado")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);
        if(!$product)
           return response()->json(['message' => 'Producto no encontrado'], 404);
        
        $product->delete();
        return response()->json(['message' => 'Producto eliminado exitosamente']);
    }
}
