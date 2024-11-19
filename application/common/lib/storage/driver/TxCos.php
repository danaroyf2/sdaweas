<?php
/**
 * @copyright ©2020 来客PHP在线客服系统
 * Created by PhpStorm.
 */
namespace app\common\lib\storage\driver;

use app\common\lib\storage\Driver;
use Qcloud\Cos\Client;

class TxCos extends Driver
{
    public function __construct($options = [])
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        parent::__construct();
    }

    public function put()
    {
        $client = new Client([
            'region' => $this->options['region'],
            'credentials' => [
                'secretId' => $this->options['secret_id'],
                'secretKey' => $this->options['secret_key'],
            ],
        ]);

        $key = trim($this->saveFileFolder . '/' . $this->saveFileName, '/');
        /** @var \Guzzle\Service\Resource\Model $result */
        $result = $client->putObject([
            'Bucket' => $this->options['bucket'],
            'Key' => $key,
            'Body' => fopen($this->file->getInfo('tmp_name'), 'rb'),
        ]);
        $this->url = urldecode($result->get('ObjectURL'));
        $this->thumbUrl = $this->url;

        return $this->save();
    }
}