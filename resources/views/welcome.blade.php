<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ChatApp-Login</title>
     
<link rel="stylesheet" href="{{url('/css/style.css')}}">
      

</head>
<body>
 
<div class="wrapper fadeInDown">
  <div id="formContent">
    <!-- Tabs Titles -->

    <!-- Icon -->
    <div class="fadeIn first">
      <img src="https://www.bootdey.com/img/Content/avatar/avatar1.png" id="icon" alt="User Icon" />
    </div>

    <!-- Login Form -->
    <form method="POST" action="{{route('chat')}}">
      @csrf
      <input type="text" id="login" class="fadeIn second" name="username" placeholder="Enter UserName">
      <input type="submit" class="fadeIn fourth" value="Log In">
    </form>

    <!-- Remind Passowrd -->
    <div id="formFooter">
      <a class="underlineHover" href="#">Forgot Password?</a>
    </div>

  </div>
</div>
    <!-- <div class="container">
        <div class="h1 mt-4">Welcome to my ChatApp</div>
        <button class="btn btn-lg btn-primary mt-3" onclick="fireEvent()">Fire Event</button>
    </div> -->
  
</body>
</html>
