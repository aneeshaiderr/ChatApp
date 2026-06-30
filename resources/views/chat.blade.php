<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
     <script src="https://code.jquery.com/jquery-4.0.0.js" integrity="sha256-9fsHeVnKBvqh3FB2HYu7g2xseAZ5MlN6Kz/qnkASV8U=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{url('/css/chat.css')}}">
     <title>ChatApp</title>
</head>
<body>
    <div class="container content">
    <div class="row justify-content-center">
        <!-- Sidebar -->
        <div class="col-xl-3 col-lg-4 col-md-5 col-sm-12 col-12 mb-3" id="chat-sidebar" style="display: none;">
            <div class="card shadow-sm border-0">
                <div class="card-header text-white text-center bg-primary fw-bold">Active Sessions</div>
                <div class="card-body p-3" id="sidebar-sessions-list">
                    <!-- Dynamic session items will be added here -->
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-8 col-md-7 col-sm-12 col-12" id="chat-area">
        	<div class="card">
        		<div class="card-header">
                    <span>Chat</span>
                    <button class="btn-end-session" id="btn-end-session" style="display: none;" onclick="endLiveSupport()">✕ End Live Support</button>
                </div>
        		<div class="card-body height3">
        			<ul class="chat-list" id="chat-section">
                        {{-- Render saved messages from database --}}
                        @foreach($messages as $msg)
                            @php
                                $type = ($msg->username === $name) ? 'out' : 'in';
                                $time = $msg->created_at->format('h:i A');
                            @endphp
                            <li class="{{ $type }}">
                                <div class="chat-img">
                                    <img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
                                </div>
                                <div class="chat-body">
                                    <div class="chat-message">
                                        <h5>{{ $msg->username }} <span class="msg-timestamp">{{ $time }}</span></h5>
                                        <p>{{ $msg->message }}</p>
                                    </div>
                                </div>
                            </li>
                        @endforeach
        			</ul>
                    <!-- Typing Indicator -->
                    <div class="typing-indicator" id="typing-indicator">
                        <div class="typing-dots">
                            <span></span><span></span><span></span>
                        </div>
                        <span id="typing-username">Someone</span> is typing...
                    </div>
         	</div>
        </div>
           <div class="row mt-2 justify-content-center">
               <div class="col-lg-10 d-flex gap-2 justify-content-center flex-wrap">
                   @foreach($quickReplies as $reply)
                       <button class="btn btn-outline-primary btn-sm rounded-pill quick-reply-btn" onclick="handleQuickReply('{{ addslashes($reply->question) }}', '{{ addslashes($reply->answer) }}')">{{ $reply->button_text }}</button>
                   @endforeach
               </div>
           </div>
           <div class="row mt-3">
                        <div class="col-lg-10">
                            <input type="text" id="username"  value="{{ $name }}" hidden>
                            <input type="text" class="form-control " placeholder="write message here..." id="chat_message">

                    </div>
                    <div class="col-lg-2 justify-content-center">
                        <button class="btn btn-primary rounded w-100" onclick="broadcastMethod()">Send</button>
                    </div>
        		</div>
    </div>
</div>
</div>
</body>
  @vite(['resources/js/app.js'])
    <script>
         
        let isLiveSupport = false;
        const quickReplies = @json($quickReplies);
        let typingTimer = null;
        let isCurrentlyTyping = false;

        // =============================================
        //  Notification Sound (generated via Web Audio API)
        // =============================================
        function playNotificationSound() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                oscillator.frequency.setValueAtTime(1100, audioCtx.currentTime + 0.08);
                gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.3);
                oscillator.start(audioCtx.currentTime);
                oscillator.stop(audioCtx.currentTime + 0.3);
            } catch(e) {
                // Web Audio not supported, silently ignore
            }
        }

        // =============================================
        //  Format time helper
        // =============================================
        function formatTime(date) {
            if (!date) date = new Date();
            if (typeof date === 'string') date = new Date(date);
            let hours = date.getHours();
            let minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        }

        // =============================================
        //  Append message to chat UI
        // =============================================
        function appendLocalMessage(username, message, type, timestamp) {
            const timeStr = timestamp ? formatTime(timestamp) : formatTime();
            const newMessage = `<li class="${type}">
                <div class="chat-img">
                    <img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
                </div>
                <div class="chat-body">
                    <div class="chat-message">
                        <h5>${username} <span class="msg-timestamp">${timeStr}</span></h5>
                        <p>${message}</p>
                    </div>
                </div>
            </li>`;
            $("#chat-section").append(newMessage);
            scrollToBottom();
        }

        function scrollToBottom() {
            const chatBody = document.querySelector('.height3');
            if (chatBody) {
                chatBody.scrollTop = chatBody.scrollHeight;
            }
        }

        // =============================================
        //  Typing indicator broadcast
        // =============================================
        function sendTypingStatus(isTyping) {
            const currentUser = $("#username").val();
            $.ajax({
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                url: "{{ route('broadcast.typing') }}",
                type: 'POST',
                data: { 
                    username: currentUser, 
                    is_typing: isTyping ? 1 : 0,
                    session_user: currentUser
                },
                error: function(error) { console.log('Typing broadcast error', error); }
            });
        }

        function onTypingInput() {
            if (!isCurrentlyTyping) {
                isCurrentlyTyping = true;
                sendTypingStatus(true);
            }
            clearTimeout(typingTimer);
            typingTimer = setTimeout(function() {
                isCurrentlyTyping = false;
                sendTypingStatus(false);
            }, 1500);
        }

        // =============================================
        //  End Live Support
        // =============================================
        function endLiveSupport() {
            isLiveSupport = false;
            $('#chat-sidebar').hide();
            $('#btn-end-session').hide();
            $('#sidebar-sessions-list').html('');
            appendLocalMessage("System ⚙️", "Live support session ended. You are now chatting with the Bot.", 'in');
        }

        // =============================================
        //  Main broadcast method
        // =============================================
        function broadcastMethod(){
            const msgText = $('#chat_message').val().trim();
            if (!msgText) return;

            // Stop typing indicator
            isCurrentlyTyping = false;
            clearTimeout(typingTimer);
            sendTypingStatus(false);

            const currentUser = $("#username").val();

            if (isLiveSupport) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    url: "{{ route('broadcast.chat') }}",
                    type: 'POST',
                    data: { username: currentUser, msg: msgText, is_live: 1, session_user: currentUser },
                    success:function(result){
                       $('#chat_message').val('');
                    },
                    error :function(error){
                        console.log(error);
                    }
                });
            } else {
                // All messages now go to server for persistence + AI response
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    url: "{{ route('broadcast.chat') }}",
                    type: 'POST',
                    data: { username: currentUser, msg: msgText, is_live: 0, session_user: currentUser },
                    success: function(result) {
                        $('#chat_message').val('');
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }
        }

        function sendMessage(username, message) {
            const currentUser = $("#username").val();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                url: "{{ route('broadcast.chat') }}",
                type: 'POST',
                data: { username: username, msg: message, is_live: isLiveSupport ? 1 : 0, session_user: currentUser },
                success: function(result) {
                    // Success
                },
                error: function(error) {
                    console.log(error);
                }
            });
        }

        function handleQuickReply(question, answer) {
            $('.quick-reply-btn').prop('disabled', true);
            if (question.toLowerCase() === 'i need to talk to a person') {
                isLiveSupport = true;
                $('#chat-sidebar').show();
                $('#btn-end-session').show();
                // Broadcast to server since live support is requested
                sendMessage($("#username").val(), question);
                setTimeout(function() {
                    $('.quick-reply-btn').prop('disabled', false);
                }, 1000);
            } else {
                // Send to server for persistence (quick reply match will be handled server-side)
                const currentUser = $("#username").val();
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    url: "{{ route('broadcast.chat') }}",
                    type: 'POST',
                    data: { username: currentUser, msg: question, is_live: 0, session_user: currentUser },
                    success: function(result) {
                        $('.quick-reply-btn').prop('disabled', false);
                    },
                    error: function(error) {
                        console.log(error);
                        $('.quick-reply-btn').prop('disabled', false);
                    }
                });
            }
        }

        // =============================================
        //  Listen for real-time messages & typing events
        // =============================================
        function listenForMessages(){
            if (!window.Echo) {
                setTimeout(listenForMessages, 100);
                return;
            }

            window.Echo.channel('chatMessage')
            .listen('Chat', (data) => {
                const currentUser = $("#username").val();

                // Only show messages belonging to this user's session,
                // OR if the current user is a Support Agent
                if (data.session_user !== currentUser && currentUser !== "Support Agent 👤" && currentUser !== "Support Agent") {
                    return;
                }

                // Check if it's a "talk to a person" request
                if (data.message.toLowerCase() === 'i need to talk to a person') {
                    isLiveSupport = true;
                    $('#chat-sidebar').show();
                    $('#btn-end-session').show();
                    
                    let sessionUser = "";
                    let sessionMessage = "";
                    
                    if (data.username === currentUser) {
                        sessionUser = "Support Agent 👤";
                        sessionMessage = "Connected to Live Support";
                    } else {
                        sessionUser = `${data.username} 👤`;
                        sessionMessage = "wants to talk to you / active";
                    }
                    
                    const sessionItem = `
                        <div class="d-flex align-items-center p-2 border rounded bg-light mb-2 shadow-sm">
                            <img src="https://bootdey.com/img/Content/avatar/avatar3.png" class="rounded-circle me-2" style="width: 35px; border: 2px solid #0d6efd;">
                            <div>
                                <strong class="small d-block">${sessionUser}</strong>
                                <span class="text-success small" style="font-size: 0.75rem; font-weight: 500;">${sessionMessage}</span>
                            </div>
                        </div>
                    `;
                    $('#sidebar-sessions-list').html(sessionItem);
                }

                // Display the message
                const type = (data.username === currentUser) ? 'out' : 'in';
                appendLocalMessage(data.username, data.message, type);

                // Play notification sound for incoming messages from others
                if (data.username !== currentUser) {
                    playNotificationSound();
                }
            })
            .listen('UserTyping', (data) => {
                const currentUser = $("#username").val();
                if (data.username !== currentUser) {
                    // Only react to typing in our own session or if we are the support agent
                    if (data.session_user === currentUser || currentUser === "Support Agent" || currentUser === "Support Agent 👤") {
                        if (data.is_typing) {
                            $('#typing-username').text(data.username);
                            $('#typing-indicator').addClass('active');
                        } else {
                            $('#typing-indicator').removeClass('active');
                        }
                    }
                }
            });
        }

        $(document).ready(function() {
            // Enter key to send
            $('#chat_message').on('keypress', function(e) {
                if (e.which === 13) {
                    broadcastMethod();
                }
            });

            // Typing indicator on input
            $('#chat_message').on('input', function() {
                onTypingInput();
            });

            // Scroll to bottom on page load (for history)
            scrollToBottom();
        });

        listenForMessages();
    </script>
</html>