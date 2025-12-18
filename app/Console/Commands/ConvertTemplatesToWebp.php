<?php

namespace App\Console\Commands;

use App\Models\CertificateTemplate;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class ConvertTemplatesToWebp extends Command
{
    protected $signature = 'templates:convert-webp {--dry-run : Preview changes without executing}';
    protected $description = 'Convert existing certificate template images to WebP format';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('🔄 Converting certificate templates to WebP...');
        
        // Get all templates
        $templates = CertificateTemplate::all();
        $converted = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($templates as $template) {
            if (!$template->background_image) {
                $this->warn("⚠️  Template '{$template->nama_template}' has no background image");
                $skipped++;
                continue;
            }
            
            // Check if already WebP
            if (str_ends_with(strtolower($template->background_image), '.webp')) {
                $this->line("✅ Template '{$template->nama_template}' already WebP");
                $skipped++;
                continue;
            }
            
            $oldPath = storage_path('app/public/' . $template->background_image);
            
            if (!file_exists($oldPath)) {
                $this->error("❌ File not found: {$oldPath}");
                $errors++;
                continue;
            }
            
            try {
                // Generate new WebP filename
                $newFileName = Str::uuid()->toString() . '.webp';
                $newPath = storage_path('app/public/certificate_templates/' . $newFileName);
                $newDbPath = 'certificate_templates/' . $newFileName;
                
                if (!$isDryRun) {
                    // Ensure directory exists
                    if (!file_exists(dirname($newPath))) {
                        mkdir(dirname($newPath), 0755, true);
                    }
                    
                    // Convert to WebP
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($oldPath);
                    $image->toWebp(80)->save($newPath);
                    
                    // Update database
                    $template->background_image = $newDbPath;
                    $template->save();
                    
                    // Delete old file
                    unlink($oldPath);
                }
                
                $this->info("🔄 Converted: '{$template->nama_template}' -> {$newFileName}");
                $converted++;
                
            } catch (\Exception $e) {
                $this->error("❌ Error converting '{$template->nama_template}': " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("   Converted: {$converted}");
        $this->line("   Skipped: {$skipped}");
        $this->line("   Errors: {$errors}");
        
        if ($isDryRun && $converted > 0) {
            $this->newLine();
            $this->comment('💡 Run without --dry-run to apply changes');
        }
        
        return 0;
    }
}
