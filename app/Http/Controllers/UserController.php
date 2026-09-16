<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless((int) auth()->user()->role === 2, 403);
    }

    public function index()
    {
        $this->ensureAdmin();
        $users = User::whereNull('archived_at')->get();

        return Inertia::render('User/Index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $this->ensureAdmin();
        return inertia('User/Edit', ['user' => null, 'mode' => 'create']);
    }
    public function store(Request $request)
    {
        $this->ensureAdmin();
        $isUpdate = $request->filled('id');

        $data = $request->validate([
            'name' => 'required|string|max:25',
            'email' => 'required|email|max:255',
            'status' => 'required|boolean',
            'role' => 'required|integer|in:0,1,2',
            'password' => $isUpdate ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
        ]);

        if ($isUpdate) {
            $user = User::findOrFail($request->input('id'));
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->status = $data['status'];
            $user->role = $data['role'];
            if (!empty($data['password'])) {
                $user->password = Hash::make($data['password']);
            }

            $user->save();
            return response()->json([ 
                'success' => true,
                'message' => 'Data updated successfully.',
            ]);
        } else {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'status' => $data['status'],
                'role' => $data['role'],
                'password' => Hash::make($data['password']),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully.',
            ]);
        }

    }

    public function edit($id)
    {
        $this->ensureAdmin();
        //$person = User::findOrFail(id: $id);
        
        $user = User::where('id', $id)->first();
        return inertia('User/Edit', ['user' => $user, 'mode' => 'edit']);
    }

  

    public function toggleStatus($id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        $user->status = $user->status ? 0 : 1;
        $user->save();

        return response()->json([
            'success' => true,
            'status' => $user->status,
            'message' => $user->status ? 'User activated.' : 'User deactivated.',
        ]);
    }

    public function archive($id)
    {
        $this->ensureAdmin();

        $user = User::findOrFail($id);
        abort_if($user->id === auth()->id(), 422, 'You cannot archive your own account.');
        $user->archived_at = now();
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User archived.',
        ]);
    }

    public function destroy($id)
    {
        // Delete the record
        // Redirect or return a response
    }
}