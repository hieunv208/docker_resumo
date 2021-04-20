<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserDeviceTokensController extends Controller
{
    public function getDeviceToken(Request $request)
    {
        $input = $request->only('user_id', 'platform', 'device_token');
        try {
            $deviceToken = UserDeviceToken::whereDeviceToken($input['device_token'])->first();

            if ($deviceToken == null) {
                $platformApplicationArn = '';

                if (isset($input['platform']) && $input['platform'] == 'android') {
                    $platformApplicationArn = env('ANDROID_APPLICATION_ARN');
                }

                $client = App::make('aws')->createClient('sns');
                $result = $client->createPlatformEndpoint(array(
                    'PlatformApplicationArn' => $platformApplicationArn,
                    'Token' => $input['device_token'],
                ));

                $endPointArn = isset($result['EndpointArn']) ? $result['EndpointArn'] : '';
                $deviceToken = new UserDeviceToken();
                $deviceToken->platform = $input['platform'];
                $deviceToken->device_token = $input['device_token'];
                $deviceToken->arn = $endPointArn;
            }
            $deviceToken->user_id = $input['user_id'];
            $deviceToken->save();
        } catch (SnsException $e) {
            return response()->json(['error' => "Unexpected Error"], 500);
        }
        return response()->json(["status" => "Device token processed"], 200);
    }

    public function sendPushNotification()
    {
        $notificationTitle = "Test Notification";
        $notificationMessage = "Hi all";
        $data = [
            'type' => 'Notification'
        ];
        $userDeviceTokens = UserDeviceToken::get();

        foreach ($userDeviceTokens as $userDeviceToken) {
            $deviceToken = $userDeviceToken->device_token;
            $endPointArn = [
                'EndpointArn' => $deviceToken->arn
            ];
            try {
                $sns = App::make('aws')->createClient('sns');
                $endpointAtt = $sns->getEndpointAttributes($endPointArn);

                if ($endpointAtt != 'failed' && $endpointAtt['Attributes']['Enabled']) {
                    if ($deviceToken->platform == 'android') {
                        $fcmPayload = json_encode(
                            [
                                'notification'=>
                                    [
                                        'title' => $notificationTitle,
                                        "body" => $notificationMessage,
                                        'sound' => 'default'
                                    ],
                                'data' => $data
                            ]
                        );
                        $message = json_encode([
                            'default' => $notificationMessage,
                            'GCM' => $fcmPayload
                        ]);
                        $sns->publish([
                            'TargetArn' => $deviceToken->arn,
                            'Message' => $message,
                            'MessageStructure' => 'json'
                        ]);
                    }
                }
            } catch (SnsException $e) {
                Log::info($e->getMessage());
            }
        }
    }
    public function subscribeDeviceTokenToTopic($endPointArn)
    {
        $sns = App::make('aws')->createClient('sns');
        $result = $sns->subscribe([
            'Endpoint' => $endPointArn,
            'Protocol' => 'application',
            'TopicArn' => env('TOPIC_ARN'),
        ]);

        return $result['SubscriptionArn'] ?? '';
    }

    public function unsubscribeDeviceTokenToTopic($subscriptionArn)
    {
        $sns = App::make('aws')->createClient('sns');
        $sns->unsubscribe([
            'SubscriptionArn' => $subscriptionArn,
        ]);
    }

    public function publishToTopic($message)
    {
        $sns = App::make('aws')->createClient('sns');
        $result = $sns->publish([
            'Message' => $message,
            'MessageStructure' => 'json',
            'TopicArn' => env('TOPIC_ARN'),
        ]);

        return $result['MessageId'] ?? '';
    }

}
