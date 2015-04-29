<?php

require_once 'Converter.php';

class ConsoleMng
{
    private $converterApi;

    public function __construct($converterApi)
    {
        $this->converterApi = $converterApi;
    }

    public function start()
    {
        echo "you are in converter console\n";
        $handle = fopen("php://stdin", "r");
        while (true) {
            $command = $this->parseCommand(fgets($handle));
            $this->handleCommand($command['command'], $command['params']);
        }
    }

    private function parseCommand($command)
    {
        $command = preg_replace('/\s+/', ' ', $command);
        $commandParts = split(' ', $command);

        return [
            'command' => $commandParts[0],
            'params' => array_slice($commandParts, 1)
        ];
    }

    private function handleCommand($command, array $params)
    {
        switch ($command) {
            case 'convert':
                $this->handleConvert($params);
                break;
            case 'getProgress':
                $this->handleGetProgress();
                break;
            case 'download':
                $this->handleDownload($params);
                break;
            default:
                printf("command %s is not found \n", $command);
        }
    }

    private function handleConvert($params)
    {
        try {
            $filePath = isset($params[0]) ? $params[0] : null;
            $format = isset($params[1]) ? $params[1] : null;

            $this->converterApi->addConvertTask($filePath, $format);

            $statusUrl = $this->converterApi->getHost().$this->converterApi->getStatusCheckPageUrl();
            $downloadUrl = $this->converterApi->getHost().$this->converterApi->getDownloadResultUrl();
            printf("link to check status - %s\nlink download result - %s\n", $statusUrl, $downloadUrl);
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
        }
    }

    private function handleGetProgress()
    {
        try {
            echo $this->converterApi->getProgress()."%\n";
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
        }
    }

    private function handleDownload($params)
    {
        try {
            $saveTo = isset($params[0]) ? $params[0] : null;
            $this->converterApi->downloadResult($saveTo);
        } catch (Exception $e) {
            echo $e->getMessage()."\n";
        }
    }
}
