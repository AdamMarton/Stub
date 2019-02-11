<?php declare(strict_types=1);

namespace Stub;

final class Stub
{
    /**
     * @var string
     */
    const STAT_SIZE_INPUT  = 'size_input';

    /**
     * @var string
     */
    const STAT_SIZE_OUTPUT = 'size_output';

    /**
     * @var bool
     */
    private $debugEnabled  = false;

    /**
     * @var bool
     */
    private $logEnabled    = true;

    /**
     * @var null|resource
     */
    private $logHandler    = null;

    /**
     * @var string
     */
    private $logFile       = 'stubgen.log';

    /**
     * @var string
     */
    private $rootDir       = '';

    /**
     * @var string
     */
    private $appName       = '';

    /**
     * @var string
     */
    private $outputPath    = '';

    /**
     * @var array
     */
    private $stat          = [
        'total_files' => 0,
        'size_input'  => 0,
        'size_output' => 0
    ];

    /**
     * @param  string $directory
     * @return void
     */
    public function __construct(string $directory)
    {
        $this->rootDir   = $directory;
        $appNamespace    = explode(DIRECTORY_SEPARATOR, $directory);
        $appName         = array_pop($appNamespace);

        if ($appName) {
            $this->appName = $appName;
        }

        if ($this->logEnabled) {
            $logHandler = fopen($this->logFile, 'w');
            if (is_resource($logHandler)) {
                $this->logHandler = $logHandler;
            }
        }
    }

    /**
     * @param  string    $outputPath
     * @param  Generator $generator
     * @return Generator $generator
     */
    public function generate(string $outputPath, Generator $generator)
    {
        print "
         __       ___.     ________          0.0.1
  ______/  |_ __ _\_ |__  /  _____/  ____   ____  
 /  ___|   __\  |  \ __ \/   \  ____/ __ \ /    \ 
 \___ \ |  | |  |  / \_\ \    \_\  \  ___/|   |  \
/____  >|__| |____/|___  /\______  /\___  >___|  /
     \/                \/        \/     \/     \/ \n\n";

        $start            = $start = microtime(true);
        $ratio            = 0;
        $sourceFiles      = $this->getPhpFiles();
        $counter          = 1;
        $totalFiles       = sizeof($sourceFiles);
        $outputPath       = realpath($outputPath);

        if ($outputPath) {
            $this->outputPath = $outputPath;
        }

        $this->stat['total_files'] = $totalFiles;
        $logCallback      = function ($args) {
            return call_user_func_array([$this, 'log'], [$args]);
        };

        $executionTime = number_format(($start = microtime(true) - $start), 2);
        $originalSize  = (float) number_format((float) ($this->getInputSize()/1000000), 2);
        $stubSize      = (float) number_format((float) ($this->getOutputSize()/1000000), 2);
        if ($originalSize > $stubSize) {
            $ratio = number_format((($stubSize/$originalSize) * 100), 2);
        }

        foreach ($sourceFiles as $file) {
            if ($file instanceof \SplFileInfo) {
                $filePath  = $file->getPath();
                $message   = "Processing file ({$counter} of {$totalFiles}): " . $file->getBasename();
                print $message . str_pad('', strlen($message), ' ') . "\r";
                $this->prepareDir($filePath);
                $source    = $this->getContent($file);
                $this->increaseInput(strlen($source));
                $tokenizer = new Tokenizer(
                    $source,
                    $logCallback
                );
                $stub      = $tokenizer->parse();
                $this->increaseOutput(strlen($stub));
                $this->saveStub($file, $stub);
                $generator->push($stub);
                $counter++;
            }
        }

        print "\n\nParsed a total of {$totalFiles} files in {$executionTime} secs.\r\n";
        print "Original ({$originalSize} Mbytes) to Stub ({$stubSize} Mbytes) size ratio is {$ratio}%.\r\n\n";

        if (is_resource($this->logHandler)) {
            fclose($this->logHandler);
        }
        return $generator;
    }

    private function generator($sourceFiles, $logCallback)
    {
        $counter = 1;
        $totalFiles       = sizeof($sourceFiles);
    }

    /**
     * @return array
     */
    private function getPhpFiles() : array
    {
        $phpFiles = [];
        $iterator = $this->getIterator();

        foreach ($iterator as $path) {
            if ($path instanceof \SplFileInfo && $path->getExtension() === 'php') {
                $phpFiles[] = $path;
            }
        }

        return $phpFiles;
    }

    /**
     * @return \RecursiveIteratorIterator
     */
    private function getIterator() : \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->rootDir)
        );
    }

    /**
     * @param  string $path
     * @return void
     */
    private function prepareDir(string $path = '')
    {
        $baseDir = $this->outputPath . DIRECTORY_SEPARATOR;
        $path    = $this->appName . DIRECTORY_SEPARATOR . trim(
            str_replace($this->rootDir, '', $path),
            DIRECTORY_SEPARATOR
        );

        if (!is_dir($baseDir . $path)) {
            $this->log('path ' . $baseDir . $path . ' does not exists');
            $structure = array_filter(explode(DIRECTORY_SEPARATOR, $path));

            while (sizeof($structure)) {
                $baseDir .= array_shift($structure) . DIRECTORY_SEPARATOR;

                if (!is_dir($baseDir)) {
                    $this->log('path ' . $baseDir . ' does not exists, attempt to create it');
                    mkdir($baseDir);
                }
            }
        }
    }

    /**
     * @param  \SplFileInfo $file
     * @return string
     */
    private function getContent(\SplFileInfo $file) : string
    {
        $content = '';
        $handler = fopen($file->getPathname(), 'r');

        if ($handler) {
            $content = fread($handler, $file->getSize());
            fclose($handler);
        }

        return $content ?: '';
    }

    /**
     * @param  \SplFileInfo $file
     * @param  string       $stub
     * @return void
     */
    private function saveStub(\SplFileInfo $file, string $stub)
    {
        $handler = fopen(
            $this->getOutputPath($file),
            'w'
        );

        if ($handler) {
            fwrite($handler, $stub);
            fclose($handler);
        }
    }

    /**
     * @param  \SplFileInfo $file
     * @return string
     */
    private function getOutputPath(\SplFileInfo $file) : string
    {
        return $this->outputPath . DIRECTORY_SEPARATOR . $this->appName .
            str_replace($this->rootDir, '', $file->getPathname());
    }

    /**
     * @param  string $message
     * @return void
     */
    private function log(string $message)
    {
        if ($this->logEnabled && is_resource($this->logHandler)) {
            fwrite($this->logHandler, $message . Tokenizer::LINE_BREAK);
        }

        if ($this->debugEnabled) {
            print_r($message);
        }
    }

    /**
     * @param  int $size
     * @return void
     */
    private function increaseInput(int $size)
    {
        $inputSize = (int) $this->stat[self::STAT_SIZE_INPUT];
        $this->stat[self::STAT_SIZE_INPUT] = $inputSize + $size;
    }

    /**
     * @return int
     */
    private function getInputSize() : int
    {
        return (int) $this->stat[self::STAT_SIZE_INPUT];
    }

    /**
     * @param  int $size
     * @return void
     */
    private function increaseOutput(int $size)
    {
        $outputSize = (int) $this->stat[self::STAT_SIZE_OUTPUT];
        $this->stat[self::STAT_SIZE_OUTPUT] = $outputSize + $size;
    }

    /**
     * @return int
     */
    private function getOutputSize() : int
    {
        return (int) $this->stat[self::STAT_SIZE_OUTPUT];
    }
}
