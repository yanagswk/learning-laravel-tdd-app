<?php

namespace App\Http\Controllers\Lesson;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\Reservation;
use App\Notifications\ReservationCompleted;
use Exception;
use Illuminate\Support\Facades\Auth;


class ReserveController extends Controller
{

    public function __invoke(Lesson $lesson)
    {
        $user = Auth::user();
        try {
            // 予約判定
            $user->canReserve($lesson);
        } catch (Exception $e) {
            return back()->withErrors('予約できません。：' . $e->getMessage());
        }
        Reservation::create(["lesson_id" => $lesson->id, "user_id" => $user->id]);

        // Notifications 通知
        $user->notify(new ReservationCompleted($lesson));

        return redirect()->route("lessons.show", ["lesson" => $lesson]);
    }
}
