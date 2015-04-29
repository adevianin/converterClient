<?php

class Converter
{
    private $host;
    private $statusCheckUrl;
    private $statusCheckPageUrl;
    private $downloadResultUrl;

    public function __construct($host)
    {
        $this->host = $host;
    }

    public function addConvertTask($filePath, $format)
    {
        if (!file_exists($filePath)) {
            throw new Exception('file "'.$filePath.'" is not found');
        }

        if (!$format) {
            throw new Exception('format is required');
        }

        $url = $this->host.'/api/upload?format='.$format;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('audio' => '@'.$filePath));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode == 200) {
            $data = json_decode($result);

            $this->statusCheckUrl = $data->progressCheck;
            $this->statusCheckPageUrl = $data->progressCheckPage;
            $this->downloadResultUrl = $data->download;
        } else {
            throw new Exception($this->formatErrors(json_decode($result)));
        }
    }

    public function getProgress()
    {
        if (!$this->statusCheckUrl) {
            throw new Exception('add converting task file first');
        }
        $ch = curl_init($this->host.$this->statusCheckUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        return intval($result);
    }

    public function downloadResult($saveTo)
    {
        if (!$this->downloadResultUrl) {
            throw new Exception('add converting task file first');
        }
        if (!$saveTo) {
            throw new Exception('saveTo is required');
        }
        if ($this->getProgress() != 100) {
            throw new Exception('file is still converting');
        }
        $ch = curl_init($this->host.$this->downloadResultUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        curl_close($ch);

        file_put_contents($saveTo, $result);
    }

    public function getStatusCheckPageUrl()
    {
        return $this->statusCheckPageUrl;
    }

    public function getDownloadResultUrl()
    {
        return $this->downloadResultUrl;
    }

    public function getHost()
    {
        return $this->host;
    }

    private function formatErrors(array $errors)
    {
        $result = "errors: \n";
        $i = 1;
        foreach ($errors as $error) {
            $result .= sprintf('%d. %s', $i, $error);
            $i++;
        }

        return $result;
    }
}
