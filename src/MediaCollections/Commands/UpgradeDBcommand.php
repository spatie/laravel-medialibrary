<?php

namespace App\Console\Commands;

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Class GenerateMissingMetaTitle.
 */
class GenerateMissingDataOnMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media-library:db-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing data for media';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $medias = Media::all();
        

        $this->info('');
        $this->info('');
        $this->info('      xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
        $this->info('      x                                                                                       x');
        $this->info('      x   Before you run this command please make sure you backup your existing media table.  x');
        $this->info('      x                         There is no reset for this command.                           x');
        $this->info('      x                                                                                       x');
        $this->info('      xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
 

        if($this->confirm('Do you wish to continue?', true)) {    

            $bar = $this->output->createProgressBar(count($medias)); 

            $namespace = $this->choice('Which Namespace do you want to use?', ['App\\Models\\', 'App\\'], );

            $bar->start();
            
            foreach ($medias as $media) {

                
                $class = class_basename($media->model_type);

                if (empty($media->uuid)) {
                    DB::table('media')
                      ->update([
                        'conversions_disk' => 'public',
                        'uuid' => Str::uuid(),
                        'model_type' => $namespace . $class
                    ]);
                    
                }

                if (empty($media->conversion_disk)) {
                    DB::table('media')
                      ->update([
                        'conversions_disk' => 'local'
                    ]);
                }

                DB::table('media')->update([ 'model_type' => $namespace . $class ]);

                Log::info('updated: '.$media->name. ' Namespace set to: '. $namespace . $class);

                $bar->advance();
            }
            $bar->finish();

            $this->info('Fix Successful. For more details of what happened check your log or telescope');

            Log::info('Media-library db fix executed');
        } else {
            $this->info('Fix Process Cancelled.');
        }
        
    }
    
}
