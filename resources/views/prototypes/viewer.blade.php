<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Prototype: {{ $prototype->title }}</title>
</head>
<body>
<div>
    <h1>{{ $prototype->title }}</h1>
    <div>
        <a href="{{ "/user/app/projects/" . $prototype->project_idea->project_id  }}"
           class="text-blue-500 hover:underline">
            <i class="fas fa-arrow-left"></i> Back to Prototypes
        </a>
    </div>
</div>

<div class="p-4 h-screen">
    <div>
        <iframe
            title="Prototype Viewer"
            src="{{ "/prototype/{$prototype->id}/asset/" }}"
            sandbox="allow-scripts allow-same-origin"
            style="width: 100vw; height: 100vh; border: none;"
            referrerpolicy="no-referrer"
        ></iframe>
    </div>
</div>

</body>
</html>
