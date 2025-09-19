<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;


class UserManagementController extends Controller
{
    public function __construct()
    {
        // Protege cada ação com a permissão apropriada
        $this->middleware(['auth', 'permission:users.view'])->only(['index']);
        $this->middleware(['auth', 'permission:users.create'])->only(['create', 'store']);
        $this->middleware(['auth', 'permission:users.update'])->only(['edit', 'update']);
        $this->middleware(['auth', 'permission:users.delete'])->only(['destroy']);
        $this->middleware(['auth', 'permission:users.assign-roles|users.assign-permissions'])
            ->only(['editRolesPermissions', 'updateRolesPermissions']);
    }

    public function index()
    {
        $users = User::with(['roles', 'permissions'])->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'roles' => ['array'],
            'roles.*' => ['integer'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // Converte IDs -> nomes
        $roleNames = Role::whereIn('id', $data['roles'] ?? [])->pluck('name')->all();

        // Filtra pelo Gate (alvo = $user recém criado)
        $rolesAplicar = array_values(array_filter($roleNames, function ($roleName) use ($user) {
            return Gate::allows('assign-role', $roleName, $user);
        }));

        if ($rolesAplicar) {
            $user->syncRoles($rolesAplicar);
        }

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado');
    }

    public function editRolesPermissions(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('admin.users.roles-perms', compact('user', 'roles', 'permissions'));
    }

    public function updateRolesPermissions(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['integer'],
            'permissions' => ['array'],
            'permissions.*' => ['integer'],
        ]);

        // IDs -> nomes aceitos pela Spatie
        $roleNames = Role::whereIn('id', $data['roles'] ?? [])->pluck('name')->all();
        $permNames = Permission::whereIn('id', $data['permissions'] ?? [])->pluck('name')->all();

        // ----- ROLES (aplica só os permitidos) -----
        $rolesAplicar = array_values(array_filter($roleNames, function ($roleName) use ($user) {
            return Gate::allows('assign-role', $roleName, $user);
        }));

        // ----- PERMISSÕES (whitelist only) -----
        $permsAplicar = array_values(array_filter($permNames, function ($permName) {
            return Gate::allows('assign-permission', $permName);
        }));

        // Sincroniza apenas o que passou nas regras
        $user->syncRoles($rolesAplicar);
        $user->syncPermissions($permsAplicar);

        // (opcional) logar tentativas bloqueadas comparando arrays

        return back()->with('success', 'Papéis e permissões atualizados');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Usuário removido');
    }
}
