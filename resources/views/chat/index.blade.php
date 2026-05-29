<x-app-layout>
    <div class="fixed inset-0 lg:left-16 top-16 bg-gray-100 flex overflow-hidden" 
         x-data="chatComponent">
        
        <!-- SIDEBAR IZQUIERDO (Lista de Chats) -->
        <div class="w-80 md:w-96 bg-white border-r border-gray-200 flex flex-col h-full shrink-0">
            <!-- Header Sidebar -->
            <div class="p-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900 leading-none">Chats</h1>
            </div>

            <!-- Search Bar -->
            <div class="px-4 pb-4">
                <div class="relative group">
                    <input type="text" placeholder="Buscar en Messenger" class="w-full bg-gray-100 border-none rounded-full py-2.5 pl-11 pr-4 text-sm focus:ring-0 focus:bg-gray-100 placeholder-gray-500 transition-all">
                    <svg class="w-5 h-5 absolute left-3.5 top-2.5 text-gray-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <!-- Filtros (Todos, No leídos) -->
            <div class="px-4 flex gap-2 mb-2 overflow-x-auto no-scrollbar">
                <button @click="filter = 'todos'" :class="filter === 'todos' ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-100 text-gray-600'" class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap">Todos</button>
                <button @click="filter = 'no_leidos'" :class="filter === 'no_leidos' ? 'bg-blue-50 text-blue-600' : 'hover:bg-gray-100 text-gray-600'" class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap line-through decoration-gray-400">No leídos</button>
            </div>

            <!-- Lista de Contactos (SCROLL INDEPENDIENTE 1) -->
            <div class="flex-1 overflow-y-auto px-2 space-y-0.5 custom-scrollbar">
                <template x-for="contact in contacts" :key="contact.id">
                    <div 
                        @click="selectContact(contact)"
                        class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition select-none"
                        :class="selectedContact && selectedContact.id === contact.id ? 'bg-blue-50/70' : 'hover:bg-gray-100/70'"
                    >
                        <div class="relative shrink-0">
                            <div class="w-14 h-14 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-sm" x-text="contact.avatar"></div>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="font-bold text-gray-900 truncate text-[15px]" x-text="contact.name"></h4>
                                <span class="text-[11px] text-gray-400" x-text="contact.time"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[13px] truncate" :class="contact.unread || contact.unreadCount > 0 ? 'font-bold text-gray-900' : 'text-gray-500'" x-text="contact.lastMessage"></p>
                                <div x-show="contact.unreadCount > 0" class="min-w-[20px] h-5 bg-blue-600 rounded-full shrink-0 ml-2 flex items-center justify-center text-[10px] font-bold text-white px-1.5" x-text="contact.unreadCount"></div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ÁREA PRINCIPAL (Chat Activo) -->
        <div class="flex-1 flex flex-col bg-white h-full overflow-hidden relative">
            
            <template x-if="selectedContact">
                <div class="flex-1 flex flex-col h-full">
                    <!-- Header Chat (FIJO) -->
                    <div class="h-16 px-5 border-b border-gray-100 flex items-center justify-between shrink-0 shadow-sm z-10 bg-white/95 backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="relative group cursor-pointer">
                                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm" x-text="selectedContact.avatar"></div>
                            </div>
                            <div>
                                <h2 class="font-bold text-gray-900 leading-tight" x-text="selectedContact.name"></h2>
                            </div>
                        </div>
                        <!-- Iconos estilo Facebook deshabilitados -->
                        <div class="flex items-center gap-2">
                        </div>
                    </div>

                    <!-- Mensajes (SCROLL INDEPENDIENTE 2) -->
                    <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-4 custom-scrollbar bg-white" id="messages-container">
                        
                        <div class="flex flex-col items-center py-10">
                            <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-black mb-4 shadow-xl" x-text="selectedContact.avatar"></div>
                            <h3 class="text-xl font-bold text-gray-900" x-text="selectedContact.name"></h3>
                            <p class="text-[13px] text-gray-500 mt-1">Has iniciado una conversación. Todos los mensajes son privados.</p>
                        </div>

                        <!-- Indicador de Carga -->
                        <div x-show="isLoading" class="text-center text-gray-400 text-sm py-4">Cargando mensajes...</div>

                        <!-- Loop de mensajes dinámicos -->
                        <template x-for="msg in messages" :key="msg.id">
                            <div>
                                <!-- Burbuja Enviada por mi -->
                                <template x-if="msg.is_mine">
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="max-w-[70%]">
                                            <div class="p-3.5 bg-blue-600 text-white rounded-2xl rounded-br-none text-[15px] leading-relaxed shadow-md shadow-blue-100 flex flex-col">
                                                <span x-text="msg.body"></span>
                                                <span class="text-[9px] text-blue-200 self-end mt-1" x-text="msg.time"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Burbuja Recibida -->
                                <template x-if="!msg.is_mine">
                                    <div class="flex items-end gap-2 group">
                                        <div class="w-8 h-8 rounded-full bg-gray-200 shrink-0 text-[10px] flex items-center justify-center font-bold text-gray-600" x-text="selectedContact.avatar"></div>
                                        <div class="max-w-[70%]">
                                            <div class="p-3.5 bg-gray-100 text-gray-900 rounded-2xl rounded-bl-none text-[15px] leading-relaxed shadow-sm flex flex-col">
                                                <span x-text="msg.body"></span>
                                                <span class="text-[9px] text-gray-400 self-start mt-1" x-text="msg.time"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                    </div>

                    <!-- Barra de Input (FIJA ABAJO) -->
                    <div class="p-4 bg-white border-t border-gray-100 shrink-0">
                        <div class="flex items-center gap-2 max-w-5xl mx-auto">
                            <div class="flex-1 relative">
                                <input type="text" x-model="newMessage" @keydown.enter="sendMessage" placeholder="Escribe un mensaje..." class="w-full bg-gray-100 border-none rounded-full py-2.5 px-5 text-[15px] focus:ring-0 focus:bg-gray-200 transition-all placeholder-gray-500">
                            </div>

                            <button @click="sendMessage" class="p-2 text-blue-600 hover:bg-gray-100 rounded-full transition" :class="newMessage.trim() === '' ? 'opacity-50 cursor-not-allowed' : ''">
                                <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Placeholder cuando no hay chat seleccionado -->
            <template x-if="!selectedContact">
                <div class="flex-1 flex flex-col items-center justify-center bg-gray-50 h-full text-center p-6">
                    <div class="w-24 h-24 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Tus Mensajes</h2>
                    <p class="text-gray-500 max-w-md">Selecciona un chat de la lista izquierda para iniciar o continuar una conversación privada y segura.</p>
                </div>
            </template>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e1e1e1;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d0d0d0;
            background-clip: content-box;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</x-app-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatComponent', () => ({
            selectedContact: null,
            contacts: @json($contactsData),
            filter: 'todos',
            messages: [],
            newMessage: '',
            isLoading: false,
            currentEchoChannel: null,

            init() {
                if (window.Echo) {
                    window.Echo.private('App.Models.User.' + {{ auth()->id() ?? 'null' }})
                        .listen('MessageSent', (e) => {
                            // Si el mensaje es de alguien que NO es el chat abierto actualmente
                            if (!this.selectedContact || this.selectedContact.id !== e.sender_id) {
                                let contactIndex = this.contacts.findIndex(c => c.id === e.sender_id);
                                if (contactIndex !== -1) {
                                    let contact = this.contacts[contactIndex];
                                    contact.lastMessage = e.body;
                                    contact.time = e.time;
                                    contact.unreadCount += 1;
                                    
                                    this.contacts.splice(contactIndex, 1);
                                    this.contacts.unshift(contact);
                                }
                            }
                        });
                }
            },

            selectContact(contact) {
                this.selectedContact = contact;
                contact.unreadCount = 0; // Reiniciar contador
                this.messages = [];
                this.fetchMessages();
            },

            fetchMessages() {
                this.isLoading = true;

                // Desuscribirse del canal anterior si existe uno activo
                if (this.currentEchoChannel) {
                    window.Echo.leave(this.currentEchoChannel);
                }

                axios.get(`/mensajes/${this.selectedContact.id}`)
                    .then(response => {
                        this.messages = response.data.messages;
                        const convId = response.data.conversation_id;
                        this.scrollToBottom();

                        // Suscribirse a Laravel Reverb para recibir mensajes en tiempo real
                        this.currentEchoChannel = 'chat.' + convId;
                        
                        if (window.Echo) {
                            window.Echo.private(this.currentEchoChannel)
                                .listen('MessageSent', (e) => {
                                    // Comprobar que no es mi propio mensaje rebotado
                                    if (e.sender_id !== {{ auth()->id() }}) {
                                        this.messages.push({
                                            id: e.id,
                                            body: e.body,
                                            is_mine: false,
                                            time: e.time
                                        });
                                        this.scrollToBottom();
                                        
                                        // Actualizar el LastMessage del sidebar y moverlo al inicio
                                        let contactIndex = this.contacts.findIndex(c => c.id === this.selectedContact.id);
                                        if(contactIndex !== -1) {
                                            let contact = this.contacts[contactIndex];
                                            contact.lastMessage = e.body;
                                            contact.time = e.time;
                                            contact.unread = true;
                                            
                                            // Extraer y colocar al inicio (reordenamiento en tiempo real)
                                            this.contacts.splice(contactIndex, 1);
                                            this.contacts.unshift(contact);
                                        }
                                    }
                                });
                        }
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },

            sendMessage() {
                if (!this.newMessage.trim() || !this.selectedContact) return;
                
                let text = this.newMessage;
                this.newMessage = ''; // Reset input immediately for UX

                axios.post(`/mensajes/${this.selectedContact.id}`, { body: text })
                    .then(response => {
                        this.messages.push(response.data);
                        this.scrollToBottom();

                        // Enviar mensaje mueve al usuario actual al tope de la lista
                        let contactIndex = this.contacts.findIndex(c => c.id === this.selectedContact.id);
                        if(contactIndex !== -1) {
                            let contact = this.contacts[contactIndex];
                            contact.lastMessage = text;
                            contact.time = 'Ahora';
                            
                            this.contacts.splice(contactIndex, 1);
                            this.contacts.unshift(contact);
                        }
                    });
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            }
        }));
    });
</script>
