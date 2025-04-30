<?php

namespace yourname\cloudinary;

use Yii;
use yii\base\Component;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;

class CloudinaryComponent extends Component
{
    public string $cloudName;
    public string $apiKey;
    public string $apiSecret;

    private Cloudinary $cloudinary;

    public function init(): void
    {
        parent::init();

        Configuration::instance([
            'cloud' => [
                'cloud_name' => $this->cloudName,
                'api_key'    => $this->apiKey,
                'api_secret' => $this->apiSecret
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->cloudinary = new Cloudinary();
    }

    public function upload(string $filePath, array $options = []): array
    {
        return $this->cloudinary->uploadApi()->upload($filePath, $options);
    }

    public function getCloudinary(): Cloudinary
    {
        return $this->cloudinary;
    }
}
