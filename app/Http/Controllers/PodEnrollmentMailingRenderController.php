<?php

namespace App\Http\Controllers;

use App\Models\PodEnrollmentMailing;
use App\Services\PodMailingRenderer;
use Illuminate\Http\Request;

class PodEnrollmentMailingRenderController extends Controller
{
    public function __invoke(Request $request, PodEnrollmentMailing $podEnrollmentMailing, PodMailingRenderer $renderer)
    {
        abort_unless(
            filled($podEnrollmentMailing->render_token)
            && hash_equals($podEnrollmentMailing->render_token, (string) $request->query('token')),
            404,
        );

        $html = $renderer->render($podEnrollmentMailing);
        $checksum = hash('sha256', $html);

        if ($podEnrollmentMailing->rendered_checksum !== $checksum) {
            $podEnrollmentMailing->forceFill([
                'rendered_checksum' => $checksum,
                'rendered_at' => now(),
            ])->save();
        }

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store');
    }
}
