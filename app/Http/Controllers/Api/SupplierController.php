<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }
    private function isAdmin(){
       $user = Auth::user();
       if (Auth::user()->role !== 'admin') {
           return response()->json(['error' => 'Usuario no es admin', 'user' => $user], 403);
        }
        return null;
    }

    /**
     * @OA\Get(
     *     path="/api/suppliers",
     *     summary="Obtener todos los proveedores.",
     *     tags={"Proveedores"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de proveedores",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No hay datos, por favor registre al menos un proveedor"
     *     )
     * )
     */
    public function index()
    {
        $suppliers = Supplier::all(); //paginate(10)
        if(count($suppliers) == 0)
           return response()->json(['message' => 'No hay datos, por favor registre al menos un proveedor'], 404);
        return response()->json($suppliers);
    }

    /**
     * @OA\Post(
     *     path="/api/suppliers",
     *     summary="Crear un nuevo proveedor. Requiere tener el rol Admin.",
     *     tags={"Proveedores"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "address", "description"},
     *             @OA\Property(property="name", type="string", example="Proveedor S.A."),
     *             @OA\Property(property="email", type="string", example="proveedor@example.com"),
     *             @OA\Property(property="phone", type="string", example="123456789"),
     *             @OA\Property(property="address", type="string", example="Calle 123, Ciudad"),
     *             @OA\Property(property="description", type="string", example="Proveedor mayorista de productos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proveedor creado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proveedor creado exitosamente"),
     *             @OA\Property(property="suppliers", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado")
     * )
     */

    public function store(Request $request)
    {
        $val = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'description' => 'required|string',
        ]);

        $email = Supplier::where('email', '=', $request->email)->exists();
        if($email)
          return response()->json(['message' => 'Email ya se encuentra registrado'], 404);


        $suppliers = Supplier::create($val);
        return response()->json(['message' => 'Proveedor creado exitosamente', 'suppliers' => $suppliers], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/suppliers/{id}",
     *     summary="Obtener un proveedor por ID.",
     *     tags={"Proveedores"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proveedor encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proveedor encontrado"),
     *             @OA\Property(property="supplier", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Proveedor no encontrado")
     * )
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);
        if(!$supplier)
           return response()->json(['message' => 'Proveedor no encontrado'], 404);
    
        return response()->json(['message' => 'Proveedor encontrado', 'supplier' => $supplier], status:201);
    }

    /**
     * @OA\Put(
     *     path="/api/suppliers/{id}",
     *     summary="Actualizar un proveedor existente. Requiere tener el rol Admin.",
     *     tags={"Proveedores"},
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
     *             required={"name", "phone", "address", "description"},
     *             @OA\Property(property="name", type="string", example="Proveedor S.A. Actualizado"),
     *             @OA\Property(property="email", type="string", example="nuevo@email.com"),
     *             @OA\Property(property="phone", type="string", example="987654321"),
     *             @OA\Property(property="address", type="string", example="Av. Siempre Viva 742"),
     *             @OA\Property(property="description", type="string", example="Descripción actualizada del proveedor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Proveedor actualizado de manera exitosa",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proveedor actualizado de manera exitosa"),
     *             @OA\Property(property="category", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Proveedor no encontrado")
     * )
     */

    public function update(Request $request, string $id)
    {   
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'address' => 'required|string',
            'description' => 'required|string',
        ]);

        $supplier = Supplier::find($id);
        if(!$supplier)
          return response()->json(['message' => 'Proveedor no encontrado'], 404);
        
        $email = Supplier::where('email', '=', $request->email)->exists();
        if($email)
          return response()->json(['message' => 'Email ya se encuentra registrado'], 404);

        $supplier->name = $request->name;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->description = $request->description;
        $supplier->save();       

        return response()->json(['message' => 'Proveedor actualizado de manera exitosa', 'supplier' => $supplier], status:201);
    }

    /**
     * @OA\Delete(
     *     path="/api/suppliers/{id}",
     *     summary="Eliminar un proveedor existente. Requiere tener el rol Admin.",
     *     tags={"Proveedores"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proveedor eliminado exitosamente",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proveedor eliminado exitosamente")
     *         )
     *     ),
     *     @OA\Response(response=403, description="No autorizado"),
     *     @OA\Response(response=404, description="Proveedor no encontrado")
     * )
     */

    public function destroy(string $id)
    {
        $supplier = Supplier::find($id);
        if(!$supplier)
           return response()->json(['message' => 'Proveedor no encontrado'], 404);
        
        $supplier->delete();
        return response()->json(['message' => 'Proveedor eliminado exitosamente']);
    }
}
