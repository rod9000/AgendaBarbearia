<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\CheckPagePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $pages = CheckPagePermission::availablePages();
        return view('admin.users.create', compact('pages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,attendant',
            'default_appointment_view' => 'required|in:dayGridMonth,timeGridWeek,timeGridDay',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['active'] = $request->boolean('active', true);

        $user = User::create($data);

        $pages = $request->input('pages', []);
        $pageData = [];
        foreach ($pages as $page) {
            $pageData[] = ['page' => $page];
        }
        $user->pagePermissions()->createMany($pageData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário cadastrado com sucesso!');
    }

    public function edit(User $user)
    {
        $pages = CheckPagePermission::availablePages();
        $userPages = $user->pagePermissions()->pluck('page')->toArray();
        return view('admin.users.edit', compact('user', 'pages', 'userPages'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'phone'    => 'nullable|string|max:20',
            'role'     => 'required|in:admin,attendant',
            'default_appointment_view' => 'required|in:dayGridMonth,timeGridWeek,timeGridDay',
        ]);

        if ($data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['active'] = $request->boolean('active', true);

        $user->update($data);

        $user->pagePermissions()->delete();
        $pages = $request->input('pages', []);
        $pageData = [];
        foreach ($pages as $page) {
            $pageData[] = ['page' => $page];
        }
        $user->pagePermissions()->createMany($pageData);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Você não pode excluir seu próprio usuário.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário excluído com sucesso!');
    }
}
