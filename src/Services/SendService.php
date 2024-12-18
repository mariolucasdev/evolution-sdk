<?php

namespace Evolution\Services;

use Evolution\Services\Enums\MediaTypeEnum;
use Illuminate\Support\Facades\Http;

/**
 * Class SendService
 *
 * @package Evolution\Services
 */
class SendService
{
    /**
     * SendService constructor.
     *
     * @param string $instance
     * @param string $to
     * @param string $caption
     * @param string $fileName
     * @param boolean $mentions
     */
    public function __construct(
        private string $instance,
        private string $to = '',
        private string $caption = '',
        private string $fileName = '',
        private bool $mentions = false
    ) {
    }

    /**
     * set to number
     *
     * @param string $to
     * @return self
     */
    public function to(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * set caption
     *
     * @param string $caption
     * @return self
     */
    public function caption(string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * set file name
     *
     * @param string $fileName
     * @return self
     */
    public function fileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * set mentions
     *
     * @param bool $mentions
     * @return self
     */
    public function mentionsEveryOne(bool $mentions): self
    {
        $this->mentions = $mentions;

        return $this;
    }

    /**
     * send text message
     *
     * @param string $message
     * @return array
     */
    public function plainText(string $message): array
    {
        $endpoint = '/message/sendText/' . $this->instance;

        $response = Http::evolution()
            ->post($endpoint, [
                'number' => $this->to,
                'text' => $message,
            ]);

        if ($response->getStatusCode()  == 201) {
            $response = json_decode($response->getBody(), true);

            return $response;
        }

        return [
            'error' => 'error',
            'message' => 'Erro ao enviar mensagem.',
        ];
    }

    /**
     * send media message
     *
     * @param string $media
     * @param MediaTypeEnum $mediaType
     * @return array
     */
    public function media(string $media, MediaTypeEnum $mediaType): array
    {
        $endpoint = '/message/sendMedia/' . $this->instance;

        if (is_file($media) &&
            (strpos($media, 'http') === false ||
            strpos($media, 'https') === false)) {
            $media = base64_encode(file_get_contents(realpath($media)));
        }

        $data = [
            'number' => $this->to,
            'media' => $media,
            'mediatype' => $mediaType->value,
        ];

        if ($this->caption && $mediaType != MediaTypeEnum::AUDIO) {
            $data['caption'] = $this->caption;
        }

        if ($this->fileName && $mediaType == MediaTypeEnum::DOCUMENT) {
            $data['fileName'] = $this->fileName;
        }

        $response = Http::evolution()
            ->post($endpoint, $data);

        if ($response->getStatusCode()  == 201) {
            $response = json_decode($response->getBody(), true);

            return $response;
        }

        return [
            'error' => 'error',
            'message' => 'Erro ao enviar mensagem.' . $response->getBody(),
        ];
    }
}
