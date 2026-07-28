<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;  // <-- IMPORTANTE: Agregar esta línea
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

            // Obtener la foto de perfil
            $profilePhoto = null;
            if ($contact->profile_photo_path) {
                $photoPath = $contact->profile_photo_path;
                if (file_exists(public_path('storage/' . $photoPath))) {
                    $profilePhoto = asset('storage/' . $photoPath);
                }
            }

            $unreadCount = 0;
            if ($conversation) {
                $unreadCount = \Illuminate\Support\Facades\DB::table('messages')
                    ->where('conversation_id', $conversation->id)
                    ->where('sender_id', $contact->id)
                    ->whereNull('read_at')
                    ->count();
            }

            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'avatar' => strtoupper(substr($contact->name, 0, 2)),
                'profile_photo' => $profilePhoto,
                'lastMessage' => $lastMessage ? $lastMessage->body : 'Inicia una conversación',
                'time' => $lastMessage ? \Carbon\Carbon::parse($lastMessage->created_at)->diffForHumans() : '',
                'last_message_time' => $lastMessage ? \Carbon\Carbon::parse($lastMessage->created_at)->timestamp : 0,
                'unreadCount' => $unreadCount,
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

    public function ping(Request $request)
    {
        $request->validate([
            'chat_activo_user_id' => 'required|integer'
        ]);

        $userId = Auth::id();
        $targetUserId = $request->chat_activo_user_id;

        // Validación de seguridad: Asegurar que es un contacto permitido
        $user = $this->obtenerUsuario($userId);
        $isPsicologo = ($user && $user->role === 'psicologo');
        $contacts = $this->obtenerContactosParaChat($userId, $isPsicologo);

        $isAuthorized = $contacts->contains('id', $targetUserId);

        if ($isAuthorized) {
            \App\Models\Message::registrarActividadChat($userId, $targetUserId);
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);
    }

    public function fetchMessages($targetUserId)
    {
        $userId = Auth::id();
        $conversation = \App\Models\Conversation::obtenerOUCrearConversacion($userId, $targetUserId);

        // Marcar mensajes no leídos en esta conversación (enviados por el otro usuario) como leídos
        \App\Models\Message::marcarLeidos($conversation->id, $targetUserId);

        // Opcional: limpiar notificaciones flotantes de tipo new_message de este usuario
        Notification::limpiarNotificacionesMensajes($userId, $targetUserId);

        // Cancelar notificaciones de correo retrasadas que estaban pendientes
        \App\Models\Message::cancelarNotificacionesPendientes($userId, $targetUserId);

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
        $currentUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->first();

        // Lógica de permisos
        if ($currentUser && $currentUser->role === 'paciente') {
            // El paciente solo puede enviar si ya hay conversación o si tiene citas con ese psicólogo
            $hasConversation = \Illuminate\Support\Facades\DB::table('conversations')
                ->where(function($q) use ($userId, $targetUserId) {
                    $q->where('user_one_id', $userId)->where('user_two_id', $targetUserId);
                })->orWhere(function($q) use ($userId, $targetUserId) {
                    $q->where('user_one_id', $targetUserId)->where('user_two_id', $userId);
                })->exists();

            $hasAppointment = \Illuminate\Support\Facades\DB::table('citas')
                ->where('user_id', $userId)
                ->where('psicologo_id', $targetUserId)
                ->exists();

            if (!$hasConversation && !$hasAppointment) {
                return response()->json(['error' => 'No tienes permiso para iniciar esta conversación.'], 403);
            }
        }

        $conversation = \App\Models\Conversation::obtenerOUCrearConversacion($userId, $targetUserId);

        $message = \App\Models\Message::crearMensaje($conversation->id, $userId, $request->body);

        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error broadcasting message: " . $e->getMessage());
        }



        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'is_mine' => true,
            'time' => \Carbon\Carbon::parse($message->created_at)->format('h:i A')
        ]);
    }
}
