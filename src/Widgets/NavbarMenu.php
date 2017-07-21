<?php

namespace Encore\Admin\Message\Widgets;

use Encore\Admin\Message\MessageModel;
use Illuminate\Contracts\Support\Renderable;

class NavbarMenu implements Renderable
{
    public function unreadMessages($limit = 5)
    {
        return MessageModel::with('sender')->inbox()->unread()->orderBy('id', 'desc')->take($limit)->get();
    }

    public function render()
    {
        $messages = $this->unreadMessages();

        return view('laravel-admin-message::navbar-menu', compact('messages'))->render();
    }
}