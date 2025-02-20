<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

// Testing Purpose

class DailyQuote extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quote:daily';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respectively send an exclusive quote to everyone daily via email.';
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $quotes = [
            'Mahatma Gandhi' => 'Live as if you were to die tomorrow. Learn as if you were to live forever.',
            'Friedrich Nietzsche' => 'That which does not kill us makes us stronger.',
            'Theodore Roosevelt' => 'Do what you can, with what you have, where you are.',
            'Oscar Wilde' => 'Be yourself; everyone else is already taken.',
            'William Shakespeare' => 'This above all: to thine own self be true.',
            'Napoleon Hill' => 'If you cannot do great things, do small things in a great way.',
            'Milton Berle' => 'If opportunity doesn’t knock, build a door.'
        ];

        // Setting up a random word
        $key = array_rand($quotes);
        $data = $quotes[$key];

        $user = User::find(1);
        Mail::raw("{$key} -> {$data}", function ($mail) use ($user) {
            $mail->from('irfanchowdhury80@gmail.com');
            $mail->to($user->email)
                ->subject('Daily New Quote!');
        });

        $this->info('Successfully sent daily quote to everyone.');
    }
}
