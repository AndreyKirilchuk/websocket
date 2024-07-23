<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class TelegramController extends Controller
{
    protected function validateTelegramAuth($data)
    {
        $checkHash = $data['hash'];
        unset($data['hash']);
        $dataCheckString = implode("\n", array_map(function ($k, $v) {
            return "$k=$v";
        }, array_keys($data), $data));

        $secretKey = hash('sha256', env('TELEGRAM_BOT_TOKEN'), true);
        $hmacHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hmacHash, $checkHash) && (time() - $data['auth_date']) < 86400;
    }

    public function handleTelegramCallback(Request $request)
    {
        $data = $request->all();

        if ($this->validateTelegramAuth($data)) {
            $user = User::firstOrCreate(
                ['telegram_id' => $data['id']],
                ['name' => $data['first_name'], 'username' => $data['username'], 'password' => Hash::make(str_random(16))]
            );

            Auth::login($user);

            $messages = Message::all();

            $messages = MessageResource::collection($messages)->resolve();

            return inertia('Message/Index', compact('messages'));
        } else {
            return redirect('/login')->withErrors('Unauthorized');
        }
    }
}
