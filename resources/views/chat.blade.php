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
        <div class="col-xl-8 col-lg-8 col-md-6 col-sm-12 col-12">
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
         
        function broadcastMethod(){
      $.ajax({
    headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
    },
    url: "{{ route('broadcast.chat') }}",
    type: 'POST',
    data:{username : $("#username").val(),msg:$('#chat_message').val()},
    success:function(result){
       
        

    },
    error :function(error){
        consol.log(error)
    }
});
}
function listenForMessages(){
    if (!window.Echo) {
        setTimeout(listenForMessages, 100);
        return;
    }

    window.Echo.channel('chatMessage').listen('Chat',(data)=>{
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
    });
}

listenForMessages();
    </script>
</html>