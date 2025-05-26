<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PostFaq;
use Illuminate\Http\Request;

class FaQController extends Controller
{
    /**
     * Menambahkan pertanyaan FAQ
     */
    public function PostFaq(Request $request)
    {
        $faq = PostFaq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'user_id' => $request->user_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ berhasil ditambahkan',
            'data' => $faq,
        ], 201);
    }

    /**
     * Mengambil semua FAQ
     */
    public function GetAllFaq()
    {
        $faq = PostFaq::with('user')->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar FAQ berhasil diambil',
            'data' => [
                'faq' => $faq,
            ],
        ], 200);
    }

    /**
     * Mengambil satu FAQ berdasarkan ID
     */
    public function GetFaq($id)
    {
        $faq = PostFaq::with('user')->find($id);

        if (!$faq) {
            return response()->json([
                'status' => 'error',
                'message' => 'FAQ tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ berhasil ditemukan',
            'data' => [
                'faq' => $faq,
            ],
        ], 200);
    }

    /**
     * Mengupdate FAQ berdasarkan ID
     */
    public function UpdateFaq(Request $request, $id)
    {
        $faq = PostFaq::find($id);

        if (!$faq) {
            return response()->json([
                'status' => 'error',
                'message' => 'FAQ tidak ditemukan',
            ], 404);
        }

        $faq->update([
            'question' => $request->question ?? $faq->question,
            'answer' => $request->answer ?? $faq->answer,
            'user_id' => $request->user_id ?? $faq->user_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ berhasil diperbarui',
            'data' => $faq,
        ], 200);
    }

    /**
     * Menghapus FAQ berdasarkan ID
     */
    public function DeleteFaq($id)
    {
        $faq = PostFaq::find($id);

        if (!$faq) {
            return response()->json([
                'status' => 'error',
                'message' => 'FAQ tidak ditemukan',
            ], 404);
        }

        $faq->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ berhasil dihapus',
        ], 200);
    }
}
