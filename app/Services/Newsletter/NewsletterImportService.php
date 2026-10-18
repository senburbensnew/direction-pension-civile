<?php

namespace App\Services\Newsletter;

use App\Enums\NewsletterSubscriberStatusEnum;
use App\Models\Newsletter;
use App\Support\Newsletter\NewsletterEmailNormalizer;
use Illuminate\Http\UploadedFile;

class NewsletterImportService
{
    public function __construct(
        private readonly NewsletterSubscriptionService $subscriptions,
    ) {}

    /**
     * @return array{imported: int, skipped: int, errors: list<string>}
     */
    public function importCsv(UploadedFile $file, bool $requireConsentColumn = true): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        $handle = fopen($file->getRealPath(), 'r');
        if ($handle === false) {
            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Impossible de lire le fichier.']];
        }

        $sample = stream_get_contents($handle, 2048) ?: '';
        rewind($handle);
        $delimiter = substr_count($sample, ';') >= substr_count($sample, ',') ? ';' : ',';

        $header = fgetcsv($handle, 0, $delimiter);
        if ($header === false) {
            fclose($handle);

            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Fichier vide.']];
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $emailIdx = array_search('email', $header, true);
        $consentIdx = array_search('consent', $header, true);
        $localeIdx = array_search('locale', $header, true);
        $groupIdx = array_search('audience_group', $header, true);

        if ($emailIdx === false) {
            fclose($handle);

            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Colonne « email » requise.']];
        }

        if ($requireConsentColumn && $consentIdx === false) {
            fclose($handle);

            return ['imported' => 0, 'skipped' => 0, 'errors' => ['Colonne « consent » requise (oui/1/true).']];
        }

        $line = 1;
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;
            if ($row === [null]) {
                continue;
            }

            $email = NewsletterEmailNormalizer::normalize((string) ($row[$emailIdx] ?? ''));
            if ($email === '') {
                $skipped++;

                continue;
            }

            if ($requireConsentColumn) {
                $consent = strtolower(trim((string) ($row[$consentIdx] ?? '')));
                if (! in_array($consent, ['1', 'true', 'oui', 'yes', 'o'], true)) {
                    $skipped++;

                    continue;
                }
            }

            if (Newsletter::query()->where('email', $email)->exists()) {
                $skipped++;

                continue;
            }

            $locale = $localeIdx !== false ? trim((string) ($row[$localeIdx] ?? '')) : null;
            $group = $groupIdx !== false ? trim((string) ($row[$groupIdx] ?? '')) : 'externe';

            $result = $this->subscriptions->subscribe(
                $email,
                locale: $locale ?: 'fr',
                audienceGroup: $group ?: 'externe',
                consentSource: 'import_csv',
            );

            if ($result->type === NewsletterSubscribeResultType::Subscribed
                || $result->type === NewsletterSubscribeResultType::Pending) {
                if (config('newsletter.double_opt_in') === false) {
                    Newsletter::query()->where('email', $email)->update([
                        'status' => NewsletterSubscriberStatusEnum::Subscribed,
                        'confirmed_at' => now(),
                        'consent_source' => 'import_csv',
                    ]);
                }
                $imported++;
            } else {
                $errors[] = "Ligne {$line} ({$email}) : {$result->message}";
                $skipped++;
            }
        }

        fclose($handle);

        return compact('imported', 'skipped', 'errors');
    }
}
