<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Extrae y mapea los contactos permitidos del usuario
     */
    private function getContactsData()
    {
        $userId = Auth::id();
        $user = $this->obtenerUsuario($userId);
        $isPsicologo = ($user && $user->role === 'psicologo');
        $contacts = $this->obtenerContactosParaChat($userId, $isPsicologo);

        // Mapear los contactos
        return $contacts->map(function($contact) use ($userId) {
            $conversation = \App\Models\Conversation::obtenerConversacion($userId, $contact->id);
            $lastMessage = $conversation ? \App\Models\Message::obtenerUltimoMensaje($conversation->id) : null;

            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar' => strtoupper(substr($contact->name, 0, 2)),
                'lastMessage' => $lastMessage ? $lastMessage->body : 'Inicia una conversación',
                'time' => $lastMessage ? \Carbon\Carbon::parse($lastMessage->created_at)->diffForHumans() : '',
                'last_message_time' => $lastMessage ? \Carbon\Carbon::parse($lastMessage->created_at)->timestamp : 0,
                'unreadCount' => 0, 
                'status' => 'Conectado' 
            ];
        })->sortByDesc('last_message_time')->values();
    }

    /**
     * Display the full messenger view with the allowed contacts.
     */
    public function index()
    {
        $contactsData = $this->getContactsData();
        return view('chat.index', compact('contactsData'));
    }

    /**
     * Fetch contacts for global sidebar (JSON)
     */
    public function fetchContacts()
    {
        $contactsData = $this->getContactsData();
        return response()->json($contactsData);
    }

    public function fetchMessages($targetUserId)
    {
        $userId = Auth::id();
        $conversation = \App\Models\Conversation::obtenerOUCrearConversacion($userId, $targetUserId);

        // Marcar mensajes no leídos en esta conversación (enviados por el otro usuario) como leídos
        \App\Models\Message::marcarLeidos($conversation->id, $targetUserId);

        // Opcional: limpiar notificaciones flotantes de tipo new_message de este usuario
        Notification::limpiarNotificacionesMensajes($userId, $targetUserId);

        $rawMessages = \App\Models\Conversation::obtenerMensajes($conversation->id);
        $messages = $rawMessages->map(function($msg) use ($userId) {
             return [
                 'id' => $msg->id,
                 'body' => $msg->body,
                 'is_mine' => $msg->sender_id === $userId,
                 'time' => \Carbon\Carbon::parse($msg->created_at)->format('h:i A')
             ];
        });

        return response()->json([
            'messages' => $messages,
            'conversation_id' => $conversation->id
        ]);
    }

    public function sendMessage(Request $request, $targetUserId)
    {
        $request->validate(['body' => 'required|string']);
        $userId = Auth::id();

        $conversation = \App\Models\Conversation::obtenerOUCrearConversacion($userId, $targetUserId);

        $message = \App\Models\Message::crearMensaje($conversation->id, $userId, $request->body);

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        // Enviar notificación al receptor
        $targetUser = $this->instanciarUsuarioParaNotificacion($targetUserId);
        if ($targetUser) {
            $targetUser->notify(new \App\Notifications\NewMessageNotification($message));
        }

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'is_mine' => true,
            'time' => \Carbon\Carbon::parse($message->created_at)->format('h:i A')
        ]);
    }
}

