<?php

namespace App\Services;

use App\Models\PodEnrollmentMailing;
use App\Models\PodPrintLayoutTemplate;
use RuntimeException;

class PodMailingRenderer
{
    public function render(PodEnrollmentMailing $enrollmentMailing): string
    {
        $enrollmentMailing->loadMissing([
            'campaignMailing.pages',
            'campaignMailing.coverLetterTemplate',
            'campaignMailing.printLayoutTemplate',
            'contact',
            'enrollment.campaign',
            'overrideCoverLetterTemplate',
        ]);

        $coverHtml = $enrollmentMailing->override_cover_letter_html
            ?: $enrollmentMailing->overrideCoverLetterTemplate?->html_content
            ?: $enrollmentMailing->coverLetterTemplate?->html_content
            ?: $enrollmentMailing->campaignMailing?->coverLetterTemplate?->html_content
            ?: '';

        $variables = $this->variablesFor($enrollmentMailing);
        $parts = [];

        if (filled($coverHtml)) {
            $parts[] = $this->renderTemplate($coverHtml, $variables, 'Cover letter');
        }

        foreach ($enrollmentMailing->campaignMailing?->pages ?? [] as $page) {
            if (filled($page->html_content)) {
                $parts[] = $this->renderTemplate(
                    $page->html_content,
                    $variables,
                    $page->name ?: "Page {$page->page_number}",
                );
            }
        }

        if ($parts === []) {
            throw new RuntimeException('This planned mailing does not have cover letter or page HTML to render.');
        }

        return $this->wrapContent(implode("\n", $parts), $enrollmentMailing);
    }

    /**
     * @return array<string, string>
     */
    private function variablesFor(PodEnrollmentMailing $enrollmentMailing): array
    {
        $contact = $enrollmentMailing->contact;
        $campaign = $enrollmentMailing->enrollment?->campaign;
        $mailing = $enrollmentMailing->campaignMailing;
        $fullName = trim("{$contact?->first_name} {$contact?->last_name}");

        $contactValues = [
            'id' => (string) ($contact?->id ?? ''),
            'first_name' => $contact?->first_name ?? '',
            'last_name' => $contact?->last_name ?? '',
            'full_name' => $fullName,
            'organization' => $contact?->organization ?? '',
            'email' => $contact?->email ?? '',
            'phone' => $contact?->phone ?? '',
            'address1' => $contact?->address1 ?? '',
            'address2' => $contact?->address2 ?? '',
            'city' => $contact?->city ?? '',
            'state' => $contact?->state ?? '',
            'zip' => $contact?->zip ?? '',
            'country' => $contact?->country ?? 'US',
        ];

        $variables = $contactValues;

        foreach ($contactValues as $key => $value) {
            $variables["contact.{$key}"] = $value;
        }

        $variables['campaign.id'] = (string) ($campaign?->id ?? '');
        $variables['campaign.name'] = $campaign?->name ?? '';
        $variables['campaign.slug'] = $campaign?->slug ?? '';
        $variables['campaign.source_key'] = $campaign?->source_key ?? '';
        $variables['campaign_name'] = $campaign?->name ?? '';
        $variables['mailing.id'] = (string) ($mailing?->id ?? '');
        $variables['mailing.name'] = $mailing?->name ?? '';
        $variables['mailing.sequence'] = (string) ($mailing?->sequence ?? '');
        $variables['mailing_name'] = $mailing?->name ?? '';
        $variables['mailing_sequence'] = (string) ($mailing?->sequence ?? '');

        return $variables;
    }

    /**
     * @param  array<string, string>  $variables
     */
    private function renderTemplate(string $template, array $variables, string $label): string
    {
        preg_match_all('/{{\s*([a-zA-Z0-9_.]+)\s*}}/', $template, $matches);

        $unknownMarkers = collect($matches[1] ?? [])
            ->unique()
            ->reject(fn (string $marker): bool => array_key_exists($marker, $variables))
            ->values();

        if ($unknownMarkers->isNotEmpty()) {
            throw new RuntimeException(
                "{$label} contains unsupported merge variable(s): "
                .$unknownMarkers->map(fn (string $marker): string => "{{ {$marker} }}")->implode(', '),
            );
        }

        return preg_replace_callback(
            '/{{\s*([a-zA-Z0-9_.]+)\s*}}/',
            fn (array $match): string => e($variables[$match[1]]),
            $template,
        ) ?? $template;
    }

    private function wrapContent(string $content, PodEnrollmentMailing $enrollmentMailing): string
    {
        $layout = $enrollmentMailing->campaignMailing?->printLayoutTemplate
            ?: $this->defaultLetterLayout($enrollmentMailing);

        if (! $layout) {
            return $this->fallbackShell($content);
        }

        if (! str_contains($layout->html_shell, '{{ content }}')) {
            throw new RuntimeException("Print layout {$layout->name} must include a {{ content }} placeholder.");
        }

        return str($layout->html_shell)
            ->replace('{{ css }}', $layout->css ?? '')
            ->replace('{{ content }}', $content)
            ->toString();
    }

    private function defaultLetterLayout(PodEnrollmentMailing $enrollmentMailing): ?PodPrintLayoutTemplate
    {
        $teamId = $enrollmentMailing->team_id;

        return PodPrintLayoutTemplate::query()
            ->where('mailing_format', 'letter')
            ->where('slot', 'letter_file')
            ->where('status', 'active')
            ->where(function ($query) use ($teamId): void {
                $query->where('scope', 'system')
                    ->orWhere(function ($query) use ($teamId): void {
                        $query->where('scope', 'team')
                            ->where('team_id', $teamId);
                    });
            })
            ->orderByRaw("CASE WHEN scope = 'team' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first();
    }

    private function fallbackShell(string $content): string
    {
        return <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    {$content}
</body>
</html>
HTML;
    }
}
