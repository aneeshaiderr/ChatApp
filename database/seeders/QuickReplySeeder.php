<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuickReplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\QuickReply::create([
            'button_text' => 'Features 🚀',
            'question' => 'What features does this app have?',
            'answer' => 'This is a real-time chat app using Laravel, WebSockets (Pusher/Laravel Echo), and jQuery!'
        ]);

        \App\Models\QuickReply::create([
            'button_text' => 'How it works 💡',
            'question' => 'How does real-time work?',
            'answer' => 'When you send a message, it triggers a Laravel Event which is broadcast via Pusher. Other clients listen to this channel and update instantly!'
        ]);

        \App\Models\QuickReply::create([
            'button_text' => 'Username 👤',
            'question' => 'Can I set my own username?',
            'answer' => 'Yes! You can enter any username on the welcome login page before joining the chat.'
        ]);

        \App\Models\QuickReply::create([
            'button_text' => 'Talk to Agent 📞',
            'question' => 'I need to talk to a person',
            'answer' => 'Connecting you to a human agent. Please wait...'
        ]);
    }
}
