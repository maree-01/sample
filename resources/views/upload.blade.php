<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File to S3</title>
</head>
<body>
    @if ($message = Session::get('success'))
        <div>
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div>
            {{ $message }}
        </div>
    @endif

    <form action="{{ route('upload.submit') }}" method="post" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html>
