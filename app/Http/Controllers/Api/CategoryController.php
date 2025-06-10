<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA; //documentacion swagger

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }
        /**
     * @OA\Get(
     *     path="/api/categories",
     *     summary="Listar todas las categorias",
     *     tags={"Categorías"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Listar todas las categorias",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                
     *             )
     *         )
     *     ),
     *     @OA\Response(
    *         response=404,
    *         description="No hay categorías registradas",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", example="No hay datos, por favor registre al menos una categoría")
    *         )
    *     )
     * )
     */
    public function index()
    {
        $categories = Category::all(); 
        if(!$categories)
           return response()->json(['message' => 'No hay datos, por favor registre al menos una categoría'], 404);
        return response()->json($categories);
    }

    /**
     * @OA\Post(
     *     path="/api/categories",
     *     summary="Crear una nueva categoría. Requiere tener el rol Admin.",
     *     tags={"Categorías"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Tecnología"),
     *             @OA\Property(property="description", type="string", example="Artículos electrónicos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoría creada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría creada exitosamente"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tecnología"),
     *                 @OA\Property(property="description", type="string", example="Artículos electrónicos")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos inválidos"
     *     )
     * )
     */

    public function store(Request $request)
    {
        $val = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
        ]);
        
        $category = Category::create($val);
        return response()->json(['message' => 'Categoría creada exitosamente', 'category' => $category], 201);

    }

    /**
     * @OA\Get(
     *     path="/api/categories/{id}",
     *     summary="Obtener una categoría por ID.",
     *     tags={"Categorías"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría encontrada"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tecnología"),
     *                 @OA\Property(property="description", type="string", example="Artículos electrónicos")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada"
     *     )
     * )
     */
    public function show(string $id)
    {
        $category = Category::find($id);
        if(!$category)
           return response()->json(['message' => 'Categoría no encontrada'], 404);
    
        return response()->json(['message' => 'Categoría encontrada', 'category' => $category], status:201);
    }

    /**
     * @OA\Put(
     *     path="/api/categories/{id}",
     *     summary="Actualizar una categoría existente. Requiere tener el rol Admin.",
     *     tags={"Categorías"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "description"},
     *             @OA\Property(property="name", type="string", example="Tecnología actualizada"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoría actualizada de manera exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría actualizada de manera exitosa"),
     *             @OA\Property(property="category", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Tecnología actualizada"),
     *                 @OA\Property(property="description", type="string", example="Descripción actualizada")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Datos inválidos"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     )
     * )
     */
    
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
        ]);

        $category = Category::find($id);
        if(!$category)
          return response()->json(['message' => 'Categoría no encontrada'], 404);

        $category->name = $request->name;
        $category->description = $request->description;
        $category->save();       

        return response()->json(['message' => 'Categoría actualizada de manera exitosa', 'category' => $category], status:201);
    }

    /**
     * @OA\Delete(
     *     path="/api/categories/{id}",
     *     summary="Eliminar una categoría existente. Requiere tener el rol Admin.",
     *     tags={"Categorías"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoría eliminada exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Categoría eliminada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoría no encontrada"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="No autorizado"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);
        if(!$category)
           return response()->json(['message' => 'Categoría no encontrada'], 404);
        
        $category->delete();
        return response()->json(['message' => 'Categoría eliminada exitosamente']);
    }
}
