<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FaqModel;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'API FAQ berjalan',
        ], 200);
    }
    
    public function GetAllFaq()
    {
        $faqs = FaqModel::all();

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar FAQ berhasil diambil',
            'data' => [
                'faq' => $faqs,
            ],
        ], 200);
    }

    public function GetFaq($id)
    {
        $faq = FaqModel::with('user')->find($id);

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
