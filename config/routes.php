<?php

return [
    // Auth
    'GET /login'                    => ['AuthController', 'loginForm'],
    'POST /login'                   => ['AuthController', 'login'],
    'GET /register'                 => ['AuthController', 'registerForm'],
    'POST /register'                => ['AuthController', 'register'],
    'POST /logout'                  => ['AuthController', 'logout'],

    // Dashboard
    'GET /'                         => ['DashboardController', 'index'],
    'GET /dashboard'                => ['DashboardController', 'index'],

    // Events
    'GET /events'                   => ['EventController', 'index'],
    'GET /events/create'            => ['EventController', 'create'],
    'POST /events'                  => ['EventController', 'store'],
    'GET /events/{id}'              => ['EventController', 'show'],
    'GET /events/{id}/edit'         => ['EventController', 'edit'],
    'POST /events/{id}'             => ['EventController', 'update'],
    'POST /events/{id}/delete'      => ['EventController', 'delete'],

    // Guests
    'GET /events/{id}/guests'       => ['GuestController', 'index'],
    'POST /events/{id}/guests'      => ['GuestController', 'store'],
    'POST /events/{id}/guests/import' => ['GuestController', 'import'],
    'POST /events/{id}/guests/send-invites' => ['GuestController', 'sendInvites'],
    'POST /guests/{id}/send-invite' => ['GuestController', 'sendSingle'],
    'POST /guests/{id}/delete'      => ['GuestController', 'delete'],

    // Public Invite
    'GET /convite/{token}'          => ['InviteController', 'show'],
    'POST /convite/{token}/confirm' => ['InviteController', 'confirm'],
    'POST /convite/{token}/decline' => ['InviteController', 'decline'],

    // Exports
    'GET /exports/event/{id}/attendance' => ['ExportController', 'attendance'],
    'GET /exports/event/{id}/clicks'     => ['ExportController', 'clicks'],
];
