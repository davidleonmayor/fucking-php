<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audios</title>
</head>
<body>
    <h1>Lista de Audios</h1>

    @if(isset($audios) && count($audios) > 0)
        <ul>
            @foreach($audios as $audio)
                <li>
               
                    <h2><a href="{{ route('audio.show', $audio['id']) }}">{{ $audio['title'] }}</a></h2>
                </li>
            @endforeach
        </ul>
    @else
        <p>No hay audios disponibles.</p>
    @endif


    <h1>sdguds</h1>
</body>
</html>
