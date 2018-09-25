<?php

namespace CareSet\Zermelo\Console;

use Illuminate\Console\Command;
use Illuminate\Console\DetectsApplicationNamespace;
use Illuminate\Support\Facades\File;

abstract class AbstractZermeloInstallCommand extends Command
{
    use DetectsApplicationNamespace;

    protected $view_path = '';

    protected $asset_path = '';

    protected $config_file = '';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * The views that need to be exported.
     *
     * @var array
     */
    protected $views = [];

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->createDirectories();

        $this->exportViews();

        $this->exportConfig();

        $this->exportAssets();
    }

    /**
     * Create the directories for the files.
     *
     * @return void
     */
    protected function createDirectories()
    {
        if (! is_dir($directory = resource_path('views/zermelo'))) {
            mkdir($directory, 0755, true);
        }

        if (! is_dir($directory = resource_path('views/zermelo/layouts'))) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Export the zermelo views.
     *
     * @return void
     */
    protected function exportViews()
    {
        foreach ($this->views as $value) {
            if (file_exists($view = resource_path('views/'.$value)) && ! $this->option('force')) {
                if (! $this->confirm("The [{$value}] view already exists. Do you want to replace it?")) {
                    continue;
                }
            }

            copy(
                $this->view_path.'/' .$value,
                $view
            );
        }
    }

    protected function exportConfig()
    {
        $filename = basename( $this->config_file );

        if ( file_exists( config_path( $filename ) ) && ! $this->option('force') ) {
            if (! $this->confirm("The [{$filename}] config already exists. Do you want to replace it?")) {
                return;
            }
        }

        copy(
            $this->config_file,
            config_path( $filename )
        );

    }

    protected function exportAssets()
    {
        if ( !File::exists( public_path( 'vendor/CareSet' ) ) ) {
            File::makeDirectory( public_path( 'vendor/CareSet' ), 0755, true );
        }

        if ( $this->asset_path ) {
            $new_files = File::allFiles( $this->asset_path );
            $new_pathnames = [];
            foreach ( $new_files as $new_file ) {
                $relativePathname = $new_file->getRelativePathname();
                $new_pathnames[] = $relativePathname;
                if ( file_exists( public_path( 'vendor/CareSet' ) . '/' . $relativePathname ) && !$this->option( 'force' ) ) {
                    if ( !$this->confirm( "The [{$relativePathname}] asset already exists. Do you want to replace it?" ) ) {
                        continue;
                    }
                }

                $dirname = pathinfo( public_path( 'vendor/CareSet' ) . '/' . $relativePathname, PATHINFO_DIRNAME );
                if ( !File::exists( $dirname ) ) {
                    File::makeDirectory( $dirname, 0755, true );
                }

                // If we say yes, or we're running in "force" mode, copy asset
                copy(
                    $this->asset_path . '/' . $relativePathname,
                    public_path( 'vendor/CareSet' ) . '/' . $relativePathname
                );
            }
        }
    }
}
