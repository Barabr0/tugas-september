<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Daftar semua user
    public function index()
    {
        try {
            return response()->json([
                'status' => true,
                'data' => User::latest()->get()
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Admin: edit data user
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'no_hp' => 'nullable|string|max:20',
                'role' => 'required|in:user,admin',
                'skor_reputasi' => 'nullable|integer|min:0|max:100',
            ]);

            // Admin tidak boleh menurunkan role dirinya sendiri
            if ($user->id === $request->user()->id && $validated['role'] !== 'admin') {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak bisa mengubah role akun sendiri.'
                ], 403);
            }

            $validated['skor_reputasi'] = $validated['skor_reputasi'] ?? 0;

            $user->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Data pengguna berhasil diperbarui.',
                'data' => $user->fresh()
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Admin: hapus user
    public function destroy(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === $request->user()->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Anda tidak bisa menghapus akun sendiri.'
                ], 403);
            }

            $user->tokens()->delete();
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'User berhasil dihapus.'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak bisa dihapus, kemungkinan masih punya data terkait.'
            ], 409);
        }
    }
}