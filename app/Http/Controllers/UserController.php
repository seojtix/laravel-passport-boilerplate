<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class UserController extends Controller {

    public function index(Request $request) {
        $users = User::all();

        if ($request->names_ids == 'true') {
            return $users->map(function ($user) {
                return collect($user->toArray())
                    ->only([ 'id', 'name' ])
                    ->all();
            });
        }

        $users->map(function ($item, $key) {
            $item->gravatar = $item->gravatar;
            return $item;
        });

        return $users;
    }

    public function store(Request $request) {
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->access = $request->access;
        $user->password = Hash::make($request->password);

        $user->save();
        return $user;
    }

    public function show(User $user) {
        return $user;
    }

    public function update(Request $request, User $user) {
        $user->name = $request->name ? $request->name : $user->name;
        $user->email = $request->email ? $request->email : $user->email;
        $user->access = $request->access ? $request->access : $user->access;
        $user->password = $request->password ? Hash::make($request->password) : $user->password;
        $user->biography = $request->biography ? $request->biography : $user->biography;
        $user->url = $request->url ? $request->url : $user->url;
        $user->localization = $request->localization ? $request->localization : $user->localization;

        if ($user->active) {
            $user->active = !$user->active;
        }

        $user->save();
        return $user;
    }

    public function updateActive(Request $request, User $user) {
        if ($user->access == 3) {
            $users = User::all()->where('access', 3)->count();

            if ($users <= 1) {
                return redirect('/settings/members')
                ->with('lastAdminError', 'O último administrador não pode ser desativado do sistema.');
            }
        }

        $user->active = !$user->active;
        $user->save();

        if (Auth::user()->id == $user->id) {
            Auth::logout();
        }

        return redirect('/settings/members')
            ->with('successfulActivate', 'O membro ' . $user->name . ' foi ' . ($user->active == 0 ? 'desativado' : 'ativado') . ' com sucesso.');
    }

    public function destroy(User $user) {
        if ($user->access == 3) {
            $users = User::all()->where('access', 3)->count();

            if ($users <= 1) {
                return redirect('/settings/members')
                ->with('lastAdminError', 'O último administrador não pode ser excluído do sistema.');
            }
        }

        $user->delete();
        return redirect('/settings/members');
    }
}
