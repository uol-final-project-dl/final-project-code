<?php

/** @noinspection SpellCheckingInspection */

namespace App\Http\Controllers\Prototypes;

use App\Http\Controllers\Controller;
use App\Models\Prototype;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ViewPrototypeController extends Controller
{
    public function getProjects(): array
    {
        $user = User::safeInstance(auth()->user());

        return [
            'projects' => $user->projects()->get(),
            'result' => 1,
        ];
    }

    public function getPrototypeFile(Prototype $prototype, $file = 'index.html'): Response
    {
        if ($prototype->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this prototype.');
        }

        $distDirectory = "jobs/$prototype->uuid/dist";
        $filePath = "{$distDirectory}/{$file}";

        $disk = Storage::disk('local');
        abort_unless($disk->exists($filePath), 404);

        $fileContent = $disk->get($filePath);
        $mimeType = $disk->mimeType($filePath);


        // I found this after researching how to set CSP headers in Laravel
        return response($fileContent,
            200,
            [
                'Content-Type' => $mimeType,
                'Content-Security-Policy' => "default-src 'self';script-src  'self';connect-src 'self';style-src   'self' 'unsafe-inline';img-src  'self' data:;frame-ancestors 'self'; form-action 'self';",
                'X-Frame-Options' => 'SAMEORIGIN',
                'Referrer-Policy' => 'no-referrer',
            ]
        );
    }

    public function viewPrototype(Prototype $prototype): View
    {
        $user = User::safeInstance(auth()->user());

        if ($prototype->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this prototype.');
        }

        return view('prototypes.viewer', ['prototype' => $prototype]);
    }
}



