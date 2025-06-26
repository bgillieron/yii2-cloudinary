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

        if (empty($data['public_id'])) {
            throw new \yii\web\BadRequestHttpException('Missing required Cloudinary data.');
        }

        // Log incoming data
        Yii::info($data, 'yii2cloudinary.uploadHandler');

        // Save using the component's logic
        $media = Yii::$app->yii2cloudinary->saveUploadRecord($data);

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
