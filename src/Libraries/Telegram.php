<?php

namespace Esoftdream\Syloer\Libraries;

use Config\Services;

class Telegram
{
    private $bugsCenter;
    private $senderToken;
    private $threadId;

    public function __construct(string $bugsCenter = '', string $senderToken = '', string $threadId = '') {
        $this->bugsCenter  = $bugsCenter;
        $this->senderToken = $senderToken;
        $this->threadId    = $threadId;
    }

    /**
     * Fungsi Kirim pesan ke Telegram
     *
     * @param string $message
     */
    public function send(string $message)
    {
        if ($this->bugsCenter !== '' && $this->senderToken !== '') {
            $url     = 'https://api.telegram.org/bot' . $this->senderToken . '/sendMessage?message_thread_id=' . $this->threadId . '&chat_id=' . $this->bugsCenter;
            $content = [
                'text'       => $message,
                'parse_mode' => 'markdown',
            ];

            $client = Services::curlrequest();

            $client->request('POST', $url, [
                'form_params' => $content,
                'http_errors' => false,
            ]);
        }
    }
}
