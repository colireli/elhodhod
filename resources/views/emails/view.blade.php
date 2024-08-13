<!DOCTYPE html>
<html>
<head>
    <title>{{ $array['subject'] }}</title>
	<style type="text/css">
	   .g-container{
		   padding: 15px 30px;
	   }
	</style>
</head>
<body>
	<div class="g-container">
        <h2>App Information</h2>
		@foreach($array['content'] as $key => $content)
            <ul>
                @if($key == 'Application Icon' || $key == 'Application splash screen' || $key == 'Small Notifications Icon')
                <div>
                    <h3>{{ $key }}</h3>:
                    <img src="{{ $content }}">
                </div>
                @endif

                @if($key == 'Android Google Service File' || $key == 'IOS Google Service File')
                <li>
                    <span>{{ $key }}</span>:
                    <a href="{{ $content }}" download>{{ $key }}</a>
                </li>
                @endif
                <li>{{$key}}: {{$content}}</li>
            </ul>
        @endforeach
	</div>
</body>
</html>
