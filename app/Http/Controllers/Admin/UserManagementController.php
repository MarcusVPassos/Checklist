<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;


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
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();
        return view('admin.users.index', compact('users','roles', 'permissions'));
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

    public function update(UpdateUserRequest $request, User $user)
    {
        // Sempre atualiza nome e e-mail
        $user->name  = $request->string('name');
        $user->email = $request->string('email');

        // Se admin preencheu senha, atualiza com hash e invalida "lembrar-me"
        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password')); // <—
            $user->remember_token = Str::random(60);
            // Se usar Sanctum e quiser revogar tokens:
            // $user->tokens()->delete();
        }

        $user->save();

        return back()->with('success', 'Usuário atualizado com sucesso.');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Usuário removido');
    }
}
