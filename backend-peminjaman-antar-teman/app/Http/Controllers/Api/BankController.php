<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BankController extends Controller
{
    // Ratakan data bank + saldo pivot supaya mudah dipakai frontend
    private function transform($bank): array
    {
        return [
            'id' => $bank->id,
            'nama_bank' => $bank->nama_bank,
            'nomor_rekening' => $bank->nomor_rekening,
            'atas_nama' => $bank->atas_nama,
            'saldo' => (int) ($bank->pivot->saldo ?? 0),
            'jumlah_topup' => (int) ($bank->pivot->jumlah_topup ?? 0),
        ];
    }

    // Ambil 1 bank milik user yang login (404 jika bukan miliknya)
    private function findUserBank(Request $request, $id)
    {
        return $request->user()->banks()->where('banks.id', $id)->firstOrFail();
    }

    public function index(Request $request)
    {
        try {
            $banks = $request->user()->banks()->latest('banks.created_at')->get()
                ->map(fn ($bank) => $this->transform($bank))
                ->values();

            return response()->json(['status' => true, 'data' => $banks], 200);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validated = $request->validate([
                'nama_bank' => 'required|string|max:100',
                'nomor_rekening' => [
                    'required',
                    'string',
                    'max:30',
                    Rule::unique('banks', 'nomor_rekening')->where('user_id', $user->id),
                ],
                'atas_nama' => 'required|string|max:100',
            ], [
                'nomor_rekening.unique' => 'Nomor rekening ini sudah kamu tambahkan.',
            ]);

            $bank = DB::transaction(function () use ($user, $validated) {
                $bank = Bank::create($validated + ['user_id' => $user->id]);
                $user->banks()->attach($bank->id, ['saldo' => 0, 'jumlah_topup' => 0]);
                return $bank;
            });

            $bank = $this->findUserBank($request, $bank->id);

            return response()->json([
                'status' => true,
                'message' => 'Bank berhasil ditambahkan.',
                'data' => $this->transform($bank),
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function topUp(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nominal' => 'required|integer|min:1000|max:10000000',
            ]);

            $bank = $this->findUserBank($request, $id);
            $nominal = (int) $validated['nominal'];

            DB::transaction(function () use ($request, $bank, $nominal) {
                DB::table('bank_user')
                    ->where('user_id', $request->user()->id)
                    ->where('bank_id', $bank->id)
                    ->increment('saldo', $nominal, [
                        'jumlah_topup' => DB::raw('jumlah_topup + ' . $nominal),
                        'updated_at' => now(),
                    ]);
            });

            $bank = $this->findUserBank($request, $id);

            return response()->json([
                'status' => true,
                'message' => 'Top up berhasil.',
                'data' => $this->transform($bank),
            ], 200);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Bank tidak ditemukan.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $bank = $this->findUserBank($request, $id);

            if ((int) $bank->pivot->saldo > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rekening tidak bisa dihapus karena saldo masih tersisa.',
                ], 409);
            }

            DB::transaction(function () use ($request, $bank) {
                $request->user()->banks()->detach($bank->id);
                $bank->delete();
            });

            return response()->json(['status' => true, 'message' => 'Bank dihapus.'], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['status' => false, 'message' => 'Bank tidak ditemukan.'], 404);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
}