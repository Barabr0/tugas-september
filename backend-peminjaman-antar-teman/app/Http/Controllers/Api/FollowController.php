<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class FollowController extends Controller
{
    // Daftar SEMUA user (untuk halaman "Cari Teman"), tandai mana yang sudah difollow
    public function index(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $followingIds = $request->user()->followings()->pluck('users.id')->toArray();

            $users = User::where('id', '!=', $userId)
                ->select('id', 'name', 'email')
                ->get()
                ->map(function ($user) use ($followingIds) {
                    $user->is_following = in_array($user->id, $followingIds);
                    return $user;
                });

            return response()->json(['status' => true, 'data' => $users], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Detail 1 teman: profil + barang yang dia punya
    public function show(Request $request, $id)
    {
        try {
            $user = User::select('id', 'name', 'email', 'skor_reputasi')->find($id);

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User tidak ditemukan'], 404);
            }

            $isFollowing = $request->user()->followings()->where('following_id', $id)->exists();
            $barangs = $user->barangs()->where('status', 'T')->with('kategori')->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'user' => $user,
                    'is_following' => $isFollowing,
                    'barangs' => $barangs
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Ambil daftar teman yang sudah kita follow
    public function myFriends(Request $request)
    {
        $friends = $request->user()->followings()->select('users.id', 'name', 'email')->get();
        return response()->json(['status' => true, 'data' => $friends], 200);
    }

    public function follow(Request $request, $id)
    {
        try {
            if ($request->user()->id == $id) {
                return response()->json(['status' => false, 'message' => 'Tidak bisa follow diri sendiri'], 400);
            }
            $request->user()->followings()->syncWithoutDetaching([$id]);
            return response()->json(['status' => true, 'message' => 'Berhasil mengikuti teman'], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => 'Sudah mengikuti atau gagal'], 400);
        }
    }

    public function unfollow(Request $request, $id)
    {
        $request->user()->followings()->detach($id);
        return response()->json(['status' => true, 'message' => 'Berhasil berhenti mengikuti'], 200);
    }
}