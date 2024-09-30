<!-- delete.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete File</title>
</head>
<body>
    <h1>Delete File</h1>
    @if ($message = Session::get('success'))
        <div>{{ $message }}</div>
    @endif
    @if ($message = Session::get('error'))
        <div>{{ $message }}</div>
    @endif
    <form action="{{ route('file.delete') }}" method="post">
        @csrf
        <label for="file_key">Select File to Delete:</label>
        <select name="file_key" id="file_key">
        <option value="" selected disabled>Please select a file</option> <!-- Placeholder option -->
            @foreach ($fileKeys as $key)
                <option value="{{ $key }}">{{ $key }}</option>
            @endforeach
        </select>
        <button type="submit">Delete</button>
    </form>
</body>
</html>
