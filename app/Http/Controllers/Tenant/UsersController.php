<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreUserRequest;
use App\Http\Requests\Tenant\UpdateUserRequest;
use App\Models\Role;
use App\Models\Secretaria;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function index(): View
    {
        $users = User::with('role', 'secretarias')
            ->orderBy('nome')
            ->paginate(25);

        return view('tenant.users.index', compact('users'));
    }

    public function create(): View
    {
        $roles = Role::where('is_ativo', true)->orderBy('descricao')->get();
        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.users.create', compact('roles', 'secretarias'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $secretariaIds = $data['secretarias'] ?? [];
        unset($data['secretarias'], $data['password_confirmation']);

        $user = User::create($data);

        if (! empty($secretariaIds)) {
            $user->secretarias()->sync($secretariaIds);
        }

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuario cadastrado com sucesso.');
    }

    public function edit(User $user): View
    {
        $user->load('secretarias');
        $roles = Role::where('is_ativo', true)->orderBy('descricao')->get();
        $secretarias = Secretaria::orderBy('nome')->get();

        return view('tenant.users.edit', compact('user', 'roles', 'secretarias'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $secretariaIds = $data['secretarias'] ?? [];
        unset($data['secretarias'], $data['password_confirmation']);

        // Nao atualizar senha se nao preenchida
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);
        $user->secretarias()->sync($secretariaIds);

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuario atualizado com sucesso.');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Desativar em vez de deletar
        $user->update(['is_ativo' => false]);

        // Invalidar sessoes ativas do usuario (best-effort, driver database)
        $this->invalidateUserSessions($user);

        return redirect()->route('tenant.users.index')
            ->with('success', 'Usuario desativado com sucesso.');
    }

    private function invalidateUserSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            $connection = config('session.connection') ?: config('database.default');
            DB::connection($connection)
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }

        // Para drivers nao-database (redis, file), o middleware EnsureUserIsActive
        // cuida da invalidacao na proxima request do usuario.
    }
}
