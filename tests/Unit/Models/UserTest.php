<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\Lesson;
use Mockery;
use PHPUnit\Framework\TestCase;


class UserTest extends TestCase
{
    /**
     * 予約可能の場合のテスト
     * @param string $plan
     * @param int $remainingCount レッスン自体の残り枠数
     * @param int $reservationCount 当該ユーザーの当月予約数
     * @dataProvider dataCanReserve_正常
     */
    public function testCanReserve_正常(string $plan, int $remainingCount, int $reservationCount)
    {
        // 当月の予約数を返すスタブを用意
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive("reservationCountThisMonth")->andReturn($reservationCount);
        $user->profile = new UserProfile();
        $user->profile->plan = $plan;

        // プランによって予約の可否を判定するスタブを用意
        $lesson = Mockery::mock(Lesson::class);
        $lesson->shouldReceive('remainingCount')->andReturn($remainingCount);

        $user->canReserve($lesson);
        // 例外が出ないことを確認するアサーションがないので代わりに
        $this->assertTrue(true);
    }


    public function dataCanReserve_正常()
    {
        return [
            '予約可:レギュラー,空きあり,月の上限以下' => [
                'plan' => 'regular',
                'remainingCount' => 1,
                'reservationCount' => 4,
            ],
            '予約可:ゴールド,空きあり' => [
                'plan' => 'gold',
                'remainingCount' => 1,
                'reservationCount' => 5,
            ],
        ];
    }


    /**
     * @param string $plan
     * @param int $remainingCount
     * @param int $reservationCount
     * @param string $errorMessage
     * @dataProvider dataCanReserve_エラー
     */
    public function testCanReserve_エラー(string $plan, int $remainingCount, int $reservationCount, string $errorMessage)
    {
        $user = Mockery::mock(User::class)->makePartial();
        $user->shouldReceive('reservationCountThisMonth')->andReturn($reservationCount);
        $user->profile = new UserProfile();
        $user->profile->plan = $plan;

        $lesson = Mockery::mock(Lesson::class);
        $lesson->shouldReceive('remainingCount')->andReturn($remainingCount);

        // 例外が正しく投げられることを確認しつつメッセージも確認する
        $this->expectExceptionMessage($errorMessage);

        $user->canReserve($lesson);
    }


    public function dataCanReserve_エラー()
    {
        return [
            '予約不可:レギュラー,空きあり,月の上限' => [
                'plan' => 'regular',
                'remainingCount' => 1,
                'reservationCount' => 5,
                'errorMessage' => '今月の予約がプランの上限に達しています。',
            ],
            '予約不可:レギュラー,空きなし,月の上限以下' => [
                'plan' => 'regular',
                'remainingCount' => 0,
                'reservationCount' => 4,
                'errorMessage' => 'レッスンの予約可能上限に達しています。',
            ],
            '予約不可:ゴールド,空きなし' => [
                'plan' => 'gold',
                'remainingCount' => 0,
                'reservationCount' => 5,
                'errorMessage' => 'レッスンの予約可能上限に達しています。',
            ],
        ];
    }
}
