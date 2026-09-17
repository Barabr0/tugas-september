<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class FollowController extends Controller
{
    // Ambil daftar teman yang bisa dipinjam (orang yang kita follow)
    public function myFriends(Request $request)
    {
        $friends = $request->user()->followings()->select('users.id', 'name', 'email')->get();
        return response()->json(['status' => true, 'data' => $friends], 200);
    }

    // Follow teman
    public function follow(Request $request, $id)
    {
        try {
            if ($request->user()->id == $id) {
                return response()->json(['message' => 'Tidak bisa follow diri sendiri'], 400);
            }
            $request->user()->followings()->attach($id);
            return response()->json(['status' => true, 'message' => 'Berhasil mengikuti teman'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'Sudah mengikuti atau gagal'], 400);
        }
    }

    // Unfollow teman
    public function unfollow(Request $request, $id)
    {
        $request->user()->followings()->detach($id);
        return response()->json(['status' => true, 'message' => 'Berhasil berhenti mengikuti'], 200);
    }
}