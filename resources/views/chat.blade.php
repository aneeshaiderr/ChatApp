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
        		<div class="card-header">Chat</div>
        		<div class="card-body height3">
        			<ul class="chat-list" id="chat-section">
        				<li class="out">
        					<div class="chat-img">
        						<img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
        					</div>
        					<div class="chat-body">
        						<div class="chat-message">
        							<h5>Jimmy Willams</h5>
        							<p>Raw denim heard of them tofu master cleanse</p>
        						</div>
        					</div>
        				</li>
        				
        			</ul>
               
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
</body>
  @vite(['resources/js/app.js'])
    <script>
         
        let isLiveSupport = false;
        const quickReplies = @json($quickReplies);

        function appendLocalMessage(username, message, type) {
            const newMessage = `<li class="${type}">
                <div class="chat-img">
                    <img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
                </div>
                <div class="chat-body">
                    <div class="chat-message">
                        <h5>${username}</h5>
                        <p>${message}</p>
                    </div>
                </div>
            </li>`;
            $("#chat-section").append(newMessage);
            // Scroll to bottom
            const chatSec = document.getElementById('chat-section');
            if (chatSec) {
                chatSec.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }
        }

        function broadcastMethod(){
            const msgText = $('#chat_message').val().trim();
            if (!msgText) return;

            if (isLiveSupport) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    url: "{{ route('broadcast.chat') }}",
                    type: 'POST',
                    data:{username : $("#username").val(), msg: msgText},
                    success:function(result){
                       $('#chat_message').val('');
                    },
                    error :function(error){
                        console.log(error);
                    }
                });
            } else {
                const normalized = msgText.toLowerCase();
                if (normalized === 'i need to talk to a person') {
                    isLiveSupport = true;
                    $('#chat-sidebar').show();
                    sendMessage($("#username").val(), msgText);
                    $('#chat_message').val('');
                    setTimeout(function() {
                        sendMessage("ChatBot 🤖", "Connecting you to a human agent. Please wait...");
                    }, 1000);
                } else {
                    const match = quickReplies.find(r => r.question.toLowerCase() === normalized);
                    if (match) {
                        $('#chat_message').val('');
                        handleQuickReply(match.question, match.answer);
                    } else {
                        appendLocalMessage($("#username").val(), msgText, 'out');
                        $('#chat_message').val('');
                        setTimeout(function() {
                            appendLocalMessage("ChatBot 🤖", "Please select a correct option.", 'in');
                        }, 1000);
                    }
                }
            }
        }

        function sendMessage(username, message) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                url: "{{ route('broadcast.chat') }}",
                type: 'POST',
                data: { username: username, msg: message },
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
                // Broadcast to server since live support is requested
                sendMessage($("#username").val(), question);
                setTimeout(function() {
                    sendMessage("ChatBot 🤖", answer);
                    $('.quick-reply-btn').prop('disabled', false);
                }, 1000);
            } else {
                // Informative options are shown locally only
                appendLocalMessage($("#username").val(), question, 'out');
                setTimeout(function() {
                    appendLocalMessage("ChatBot 🤖", answer, 'in');
                    $('.quick-reply-btn').prop('disabled', false);
                }, 1000);
            }
        }
function listenForMessages(){
    if (!window.Echo) {
        setTimeout(listenForMessages, 100);
        return;
    }

    window.Echo.channel('chatMessage').listen('Chat',(data)=>{
        if (data.message.toLowerCase() === 'i need to talk to a person') {
            isLiveSupport = true;
            $('#chat-sidebar').show();
            
            const currentUser = $("#username").val();
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

    if(data.username==$("#username").val()){
    newMessage=`<li class="out">
        					<div class="chat-img">
        						<img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
        					</div>
        					<div class="chat-body">
        						<div class="chat-message">
        							<h5>${data.username}</h5>
        							<p>${data.message}</p>
        						</div>
        					</div>
        				</li>` 
    }else{
            newMessage=`<li class="in">
        					<div class="chat-img">
        						<img alt="Avtar" src="https://bootdey.com/img/Content/avatar/avatar1.png">
        					</div>
        					<div class="chat-body">
        						<div class="chat-message">
        							<h5>${data.username}</h5>
        							<p>${data.message}</p>
        						</div>
        					</div>
        				</li>` 
    }
  
    console.log(data);
    $("#chat-section").append(newMessage);
    // Scroll to bottom
    const chatSec = document.getElementById('chat-section');
    if (chatSec) {
        chatSec.scrollIntoView({ behavior: 'smooth', block: 'end' });
    }
    });
}

        $(document).ready(function() {
            $('#chat_message').on('keypress', function(e) {
                if (e.which === 13) {
                    broadcastMethod();
                }
            });
        });

        listenForMessages();
    </script>
</html>