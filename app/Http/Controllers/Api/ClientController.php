<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = User::query()
            ->with(['company', 'role'])
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['Admin', 'Member']);
            })
            ->when(Auth::user()->role->name !== 'super_admin', function ($q) {
                $q->where('company_id', Auth::user()->company_id);
            })
            ->get()
            ->map(function (User $user) {
                return [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'role'         => $user->role->name ?? null,
                    'company_name' => $user->company->name ?? null,
                ];
            });

        return response()->json([
            'data' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'     => ['required', 'string', 'min:8'],
            'role'         => ['required', Rule::in(['Admin', 'Member'])],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        $role = Role::whereRaw('LOWER(name) = ?', [Str::lower($validated['role'])])->firstOrFail();

        if($role->name == 'super_admin') {
            $company = Company::firstOrCreate(
                ['name' => $validated['company_name']],
            );
        } else {
            $company = Auth::user()->company
                ?? Company::firstOrCreate(['name' => $validated['company_name']]);
        }
        try {
            $user = User::create([
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role_id'    => $role->id,
                'company_id' => $company?->id,
            ]);
        } catch (\Throwable $e) {
            throw $e;
        }

        return response()->json([
            'data' => [
                'id'           => $user->id,
                'name'         => $user->name,
                'email'        => $user->email,
                'role'         => $role->name,
                'company_name' => $company->name ?? null,
            ],
        ], 201);
    }
}
