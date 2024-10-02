<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
{
    // Inicializamos la consulta
    $query = User::query();

    // Filtro por búsqueda (nombre o email)
    if ($request->has('search') && $request->search != '') {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('email', 'like', '%' . $request->search . '%');
        });
    }

    // Filtro por rol
    if ($request->filled('role')) {
        $query->where('role', $request->role);
    }

    // Filtro por estado activo/inactivo
    if ($request->filled('active')) {
        $inactive = $request->active == '0' ? 1 : null; // Si el estado es inactivo (0), el campo 'inactive' es 1
        $query->where('inactive', $inactive);
    }

    // Obtener los usuarios filtrados con paginación
    $users = $query->paginate(10);

    return view('admin.users.index', compact('users'));
}




    public function create() {
        $roles = ['ADMIN', 'USER', 'LIMPIEZA', 'MANTENIMIENTO'];  // Añadir roles de empleado aquí
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string',  // Validar el nuevo campo 'role'
        ]);

        $data = $request->all();
        $data['password'] = bcrypt($request->password); // Encriptar la contraseña
        $user = User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado exitosamente.');
    }

    public function edit($id) {
        $user = User::findOrFail($id);
        $roles = ['ADMIN', 'USER', 'LIMPIEZA', 'MANTENIMIENTO'];  // Añadir roles de empleado aquí
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,'.$id,
        // Solo validamos el password si se llena
        'password' => 'nullable|string|min:8|confirmed',
    ]);

    // Obtenemos el usuario
    $user = User::findOrFail($id);

    // Actualizamos los campos excepto la contraseña si está vacía
    $data = $request->except('password');

    // Si se ha ingresado una nueva contraseña, la encriptamos y la actualizamos
    if ($request->filled('password')) {
        $data['password'] = bcrypt($request->password);
    }

    // Actualizamos los datos del usuario
    $user->update($data);

    return redirect()->route('admin.empleados.index')->with('status', 'Usuario actualizado exitosamente.');
}

    public function destroy($id) {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Usuario eliminado exitosamente.');
    }
}
