<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Pohonku;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    /**
     * Mendapatkan statistik total user, total pohon, dan total pohon per user
     */
    public function getStats()
    {
        // Mendapatkan total user
        $totalUsers = User::count();

        // Mendapatkan total pohon
        $totalPohon = Pohonku::count();

        // Mendapatkan total pohon per user (misalnya: pohon yang ditanam oleh user tertentu)
        //$totalPohonPerUser = Pohonku::groupBy('user_id')->selectRaw('user_id, count(*) as total_pohon')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'totalUsers' => $totalUsers,
                'totalPohon' => $totalPohon,
                // 'totalPohonPerUser' => $totalPohonPerUser, // bisa diaktifkan kalau perlu
            ],
        ]);

    }
}
