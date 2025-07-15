<?php

namespace yii2cloudinary\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\BadRequestHttpException;

class Yii2CloudinaryController extends Controller
{
    // Disable CSRF for public API-style endpoint
    public $enableCsrfValidation = false;

    public function actionUploadHandler(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $data = json_decode(Yii::$app->request->getRawBody(), true);
        Yii::info('📥 Incoming Cloudinary data: ' . print_r($data, true), 'yii2cloudinary.uploadHandler');

        if (empty($data['public_id'])) {
            throw new \yii\web\BadRequestHttpException('Missing required Cloudinary data.');
        }

        $relationKey = $data['relationKey'] ?? null;
        Yii::info("🔑 Resolved relationKey: " . var_export($relationKey, true), 'yii2cloudinary.uploadHandler');

        $relationSaver = null;

        if ($relationKey) {
            $map = Yii::$app->yii2cloudinary->relationSaverMap ?? [];

            if (isset($map[$relationKey]) && is_callable($map[$relationKey])) {
                $relationSaver = $map[$relationKey];
                Yii::info("🧩 Found relationSaver for key: $relationKey", 'yii2cloudinary.uploadHandler'); // <-- Step 3
            } else {
                Yii::warning("⚠️ Invalid or undefined relationKey: $relationKey", 'yii2cloudinary.uploadHandler');
            }
        }

        $media = Yii::$app->yii2cloudinary->saveUploadRecord($data, [
            'relationSaver' => $relationSaver,
        ]);

        if ($media === null) {
            Yii::error([
                'uploadHandlerFailure' => 'Cloudinary upload succeeded, but DB persistence failed.',
                'data' => $data,
            ], 'yii2cloudinary.upload');
            return [
                'status' => 'error',
                'message' => 'Upload was received but failed to save.',
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'Upload stored and saved successfully.',
            'media' => $media,
        ];
    }



}
