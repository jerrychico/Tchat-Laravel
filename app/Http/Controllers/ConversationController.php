<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Notifications\MessageReceived;
use App\Repository\conversationRepository;
use App\User;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * @var conversationRepository
     */
    private $r;
    /**
     * @var AuthManager
     */
    private $auth;

    public function __construct(conversationRepository $repository ,AuthManager $auth)
    {
        $this->middleware('auth');
        $this->r = $repository;
        $this->auth = $auth;
    }

    public function index () {
        return view('conversation/index');
    // return view('conversation/index', ['users' => $this->r->getConversations($this->auth->user()->id), 'unread' => $this->r->unreadCount($this->auth->user()->id)]);
    }

    public function show (User $user) {
        $me = $this->auth->user();
        $messages = $this->r->getMessageFor($me->id, $user->id)->paginate(5);
        $unread = $this->r->unreadCount($me->id);

        if (isset($unread[$user->id])) {
            $this->r->readAllFrom($user->id,$me->id);
             unset($unread[$user->id]);
        }
        return view('conversation/show', [
            'users' => $this->r->getConversations($me->id),
            'user' => $user,
            'messages' => $messages,
            'unread' => $unread
        ]);
    }

    public function store (User $user, StoreMessageRequest $request) {
       $this->r->CreateMessage(
            $request->get('content'),
            $this->auth->user()->id,
            $user->id
        );
        return redirect(route('conversations.show', ['id' => $user->id ]));
    }
}
