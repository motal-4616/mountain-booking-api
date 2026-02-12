<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <h1>Product Index Page</h1>
    {{-- <h2>title {{ $title }}</h2> --}}
    {{-- <h3>name ={{ $name }}</h3> --}}
    @foreach ($myoii as  $item)
        <h4>{{ $item }}</h4>
        
    @endforeach
</body>
</html>