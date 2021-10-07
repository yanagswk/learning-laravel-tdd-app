<?php

namespace Tests\Feature\Http\Controllers\Lesson;

use App\Models\Lesson;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\Factories\Traits\CreatesUser;
use Tests\TestCase;
use Exception;
use App\Notifications\ReservationCompleted;
use Illuminate\Support\Facades\Notification;


class ReserveControllerTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUser;


    public function testInvoke_正常系()
    {
        // 通知クラス
        Notification::fake();

        $lesson =factory(Lesson::class)->create();
        $user = $this->createUser();

        // ログインした状態にする
        $this->actingAs($user);

        $response = $this->post("/lessons/{$lesson->id}/reserve");

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/lessons/{$lesson->id}");

        $this->assertDatabaseHas("reservations", [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);

        // 通知が送られたかをテスト
        Notification::assertSentTo(
            $user,
            ReservationCompleted::class,
            function (ReservationCompleted $notification) use ($lesson) {
                return $notification->lesson->id === $lesson->id;
            }
        );
    }


    public function testInvoke_異常系()
    {
        Notification::fake();

        $lesson = factory(Lesson::class)->create(['capacity' => 1]);
        $anotherUser = $this->createUser();
        $lesson->reservations()->save(factory(Reservation::class)->make(['user_id' => $anotherUser->id]));

        $user = $this->createUser();
        $this->actingAs($user);

        $response = $this->from("/lessons/{$lesson->id}")
            ->post("/lessons/{$lesson->id}/reserve");

        $response->assertStatus(Response::HTTP_FOUND);
        $response->assertRedirect("/lessons/{$lesson->id}");
        $response->assertSessionHasErrors();
        // メッセージの中身まで確認したい場合は以下の2行も追加
        $error = session('errors')->first();
        $this->assertStringContainsString('予約できません。', $error);

        $this->assertDatabaseMissing('reservations', [
            'lesson_id' => $lesson->id,
            'user_id' => $user->id,
        ]);

        // 通知が送られないことをテスト
        Notification::assertNotSentTo($user, ReservationCompleted::class);
    }
}
