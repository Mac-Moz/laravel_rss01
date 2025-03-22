<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AuditItem;
use App\Services\ReferenceInfoGenerator;

class GenerateReferenceInfo extends Command
{
    protected $signature = 'audit:generate-reference';
    protected $description = 'Generate reference_info for all audit_items using recent FeedItems';

    public function handle(ReferenceInfoGenerator $generator)
    {
        $this->info('Generating reference_info for audit items...');

        AuditItem::whereNull('reference_info')->each(function ($item) use ($generator) {
            $this->info("Processing ID: {$item->id}");
            $reference = $generator->generate($item);
            if ($reference) {
                $item->reference_info = $reference;
                $item->save();
                $this->info("✔ Saved for ID: {$item->id}");
            } else {
                $this->warn("✖ No reference generated for ID: {$item->id}");
            }
        });

        $this->info('Done.');
    }
}
