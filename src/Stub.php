<?php declare(strict_types=1);

namespace AdamMarton\Stub;

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
     * @var mixed
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
        $this->rootDir = $directory;
        $appNamespace  = explode(DIRECTORY_SEPARATOR, $directory);
        $appName       = array_pop($appNamespace);

        if ($appName) {
            $this->appName = $appName;
        }

        if ($this->logEnabled) {
            $this->logHandler = fopen($this->logFile, 'w');
        }
    }

    /**
     * @param  string $outputPath
     * @return void
     */
    public function generate(string $outputPath)
    {
        print "
         __       ___.     ________          0.0.1
  ______/  |_ __ _\_ |__  /  _____/  ____   ____  
 /  ___|   __\  |  \ __ \/   \  ____/ __ \ /    \ 
 \___ \ |  | |  |  / \_\ \    \_\  \  ___/|   |  \
/____  >|__| |____/|___  /\______  /\___  >___|  /
     \/                \/        \/     \/     \/ \n\n";
        $start            = $start = microtime(true);
        $sourceFiles      = $this->getPhpFiles();
        $counter          = 1;
        $totalFiles       = sizeof($sourceFiles);
        $outputPath       = realpath($outputPath);
        if ($outputPath) {
            $this->outputPath = $outputPath;
        }
        $this->stat['total_files'] = $totalFiles;

        foreach ($sourceFiles as $file) {
            $filePath  = $file->getPath();
            // $message   = "Processing file ({$counter} of {$totalFiles}): " . $file->getBasename();
            // print $message . str_pad('', strlen($message), ' ') . "\r";
            $this->prepareDir($filePath);
            $source    = $this->getContent($file);
            $this->stat[self::STAT_SIZE_INPUT] += strlen($source);
            $tokenizer = new Tokenizer($source);
            $stub      = $tokenizer->parse();
            $this->stat[self::STAT_SIZE_OUTPUT] += strlen($stub);
            $this->saveStub($file, $stub);
            $counter++;
        }

        $executionTime = number_format($start = microtime(true) - $start, 2);
        $originalSize  = number_format($this->stat[self::STAT_SIZE_INPUT]/1000000, 2);
        $stubSize      = number_format($this->stat[self::STAT_SIZE_OUTPUT]/1000000, 2);
        $ratio         = number_format(($stubSize/$originalSize) * 100, 2);

        print "\n\n";
        print "Parsed a total of {$totalFiles} files in {$executionTime} secs.\r\n";
        print "Original ({$originalSize} Mbytes) to Stub ({$stubSize} Mbytes) size ratio is {$ratio}%.\r\n";

        if ($this->logHandler) {
            fclose($this->logHandler);
        }
    }

    /**
     * @return array
     */
    private function getPhpFiles() : array
    {
        $phpFiles = [];
        $iterator = $this->getIterator();

        foreach ($iterator as $path) {
            if ($path->getExtension() === 'php') {
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
     * @return void
     */
    private function saveStub(\SplFileInfo $file, $stub)
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
        if ($this->logEnabled) {
            fwrite($this->logHandler, $message . Tokenizer::LINE_BREAK);
        }

        if ($this->debugEnabled) {
            var_dump($message);
        }
    }
}
