<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        VerifyEmail::toMailUsing(function ($notifiable){
            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $user = User::whereEmail($notifiable->getEmailForVerification())->first();
            return (new MailMessage)
                ->subject('Aktifasi Akun Gunadarma AI-COEEE')
                ->greeting("Selamat datang di Universitas Gunadarma AI Center of Excellence.")
                ->line("Terima kasih sudah mendaftar di UG AICOE, silahkan verifikasi alamat emailmu dengan klik tautan berikut:")
                ->action('Verifikasi Alamat Email', $verifyUrl)
                ->line("Penting untuk memiliki akun dengan alamat email yang akurat karena semua informasi tentang UG AICOE akan dikirimkan ke email ini.")
                ->line("Setelah sukses melakukan aktivasi anda dapat menikmati fasilitas free jupyter notebook di mesin DGX-A100, dengan akses:")
                ->line(nl2br(
                    '<strong>' .
                        "Host : " . env('SECOND_URL') . "\n" . 
                        "Username : " . str_replace("@", "_at_", $user->email) . "\n" . 
                        "Password : passsword login anda"  .
                    '</strong>'
                ))
                ->line("Harap abaikan email ini bila anda tidak pernah mendaftar ke UG AICOE.");
        });
    }
}
