<?php

namespace App\Http\Controllers;

use App\Events\Chat;
use App\Events\UserTyping;
use App\Models\QuickReply;
use App\Models\Message;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class chatController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function notFound()
    {
        return abort(404, 'Not Found');
    }

    public function chat(Request $request)
    {
        $request->validate([
            'username' => 'required',
        ]);
        $username = $request->username;
        $quickReplies = QuickReply::all();

        // Retrieve last 100 messages for history (filtered by the user's name to isolate chats)
        $messages = Message::where('session_user', $username)
            ->orderBy('created_at', 'asc')
            ->take(100)
            ->get();

        return view('chat')->with([
            'name' => $username,
            'quickReplies' => $quickReplies,
            'messages' => $messages
        ]);
    }

    public function broadcastChat(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'msg' => 'required'
        ]);

        $username = $request->username;
        $msg = $request->msg;
        // Use provided session_user (from agents) or default to the current username
        $sessionUser = $request->input('session_user', $username);

        // 1. Save user message to database
        Message::create([
            'session_user' => $sessionUser,
            'username' => $username,
            'message' => $msg,
            'is_bot' => false
        ]);

        // 2. Broadcast the user's message
        event(new Chat($sessionUser, $username, $msg));

        // 3. If it's a chatbot session (not live support) and not a direct trigger for live support
        // We will handle AI response here if the message is NOT a quick reply
        $normalized = strtolower(trim($msg));
        if ($normalized !== 'i need to talk to a person') {
            // Check if it matches any quick reply question
            $quickReplyExists = QuickReply::whereRaw('LOWER(question) = ?', [$normalized])->exists();

            if (!$quickReplyExists) {
                // If it's not a quick reply and not live support, let's generate Gemini AI reply
                if (!$request->has('is_live') || !$request->boolean('is_live')) {
                    // Let's run a small delay or call Gemini immediately
                    $aiResponse = GeminiService::getResponse($msg);

                    // Save AI message to database
                    Message::create([
                        'session_user' => $sessionUser,
                        'username' => 'ChatBot 🤖',
                        'message' => $aiResponse,
                        'is_bot' => true
                    ]);

                    // Broadcast AI response
                    event(new Chat($sessionUser, 'ChatBot 🤖', $aiResponse));
                }
            } else {
                // It is a quick reply. The frontend handles showing local messages,
                // but we should ALSO save the bot response to the database so it's persisted in history!
                $match = QuickReply::whereRaw('LOWER(question) = ?', [$normalized])->first();
                if ($match) {
                    Message::create([
                        'session_user' => $sessionUser,
                        'username' => 'ChatBot 🤖',
                        'message' => $match->answer,
                        'is_bot' => true
                    ]);
                    // Broadcast the chatbot answer so other tabs/clients (if any) or history gets it
                    event(new Chat($sessionUser, 'ChatBot 🤖', $match->answer));
                }
            }
        } else {
            // If they said "i need to talk to a person", the bot replies
            Message::create([
                'session_user' => $sessionUser,
                'username' => 'ChatBot 🤖',
                'message' => 'Connecting you to a human agent. Please wait...',
                'is_bot' => true
            ]);
            event(new Chat($sessionUser, 'ChatBot 🤖', 'Connecting you to a human agent. Please wait...'));
        }

        return response()->json($request->all());
    }

    public function broadcastTyping(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'is_typing' => 'required|boolean'
        ]);

        $sessionUser = $request->input('session_user', $request->username);

        event(new UserTyping($sessionUser, $request->username, $request->is_typing));

        return response()->json(['status' => 'success']);
    }
}

