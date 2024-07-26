<?php

class linePostMessage
{
    protected $access_token = ACCESS_TOKEN;
    protected $endpoint = null;
    protected $message = [];
    protected $response = null;
    protected $debug = null;

    public function __construct($endpoint = 'https://api.line.me/v2/bot/message/push')
    {
        $this->endpoint = $endpoint;
        return $this;
    }

    /**
     * 通常テキストメッセージ
     */
    public function setTextMessage($text = '', $emojis = [])
    {
        $message['type'] = 'text';
        $message['text'] = $text;
        try {
            if (is_array($emojis) && count($emojis)) {
                $txt = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
                $hit = [];
                foreach ($txt as $key => $val) {
                    if ($val === '$') {
                        $hit[] = $key;
                    }
                }
                if (is_array($hit) && count($hit)) {
                    foreach ($emojis as $key => $emoji) {
                        $message['emojis'][$key]['index'] = $hit[$key];
                        $message['emojis'][$key]['productId'] = $emoji['productId'];
                        $message['emojis'][$key]['emojiId'] = $emoji['emojiId'];
                    }
                }
            }
            $this->message = $message;
        } catch (\Throwable $th) {
            //throw $th;
            print $th->getMessage();
        }
        return $this;
    }

    /**
     * スタンプメッセージ
     */
    public function setStickerMessage($packageId = '446', $stickerId = '1988')
    {
        $message['type'] = 'sticker';
        $message['packageId'] = $packageId;
        $message['stickerId'] = $stickerId;
        $this->message = $message;
        return $this;
    }

    /**
     * 画像メッセージ
     */
    public function setImageMessage($originalContentUrl = 'https://example.com/original.jpg', $previewImageUrl = 'https://example.com/preview.jpg')
    {
        $message['type'] = 'image';
        $message['originalContentUrl'] = $this->extensionCheck($originalContentUrl, 'png|jpeg|jpg'); //10M以下/プロトコル：HTTPS（TLS 1.2以降）
        $message['previewImageUrl'] = $this->extensionCheck($previewImageUrl, 'png|jpeg|jpg'); //1M以下/プロトコル：HTTPS（TLS 1.2以降）
        $this->message = $message;
        return $this;
    }

    /**
     *  動画メッセージ
     */
    public function setVideoMessage($originalContentUrl = 'https://example.com/original.mp4', $previewImageUrl = 'https://example.com/preview.jpg', $trackingId = null)
    {
        $message['type'] = 'video';
        $message['originalContentUrl'] = $this->extensionCheck($originalContentUrl, 'mp4'); //200M以下/プロトコル：HTTPS（TLS 1.2以降）
        $message['previewImageUrl'] = $this->extensionCheck($previewImageUrl, 'png|jpeg|jpg'); //1M以下/プロトコル：HTTPS（TLS 1.2以降）
        if ($trackingId) {
            $message['trackingId'] = $trackingId;
        }
        $this->message = $message;
        return $this;
    }

    /**
     * 音声メッセージ
     */
    public function setAudioMessage($originalContentUrl = 'https://example.com/original.m4a', $duration = 60000)
    {
        $message['type'] = 'audio';
        $message['originalContentUrl'] = $this->extensionCheck($originalContentUrl, 'mp3|m4a'); //200M以下/プロトコル：HTTPS（TLS 1.2以降）
        $message['duration'] = $duration; //FFmpegを使用してロジックを作成するか計算してミリ秒に直してください(1秒は1000ミリ秒です)
        $this->message = $message;
        return $this;
    }

    /**
     * 位置情報メッセージ
     */
    public function setLocationMessage($title = 'my location', $address = '〒102-8282 東京都千代田区紀尾井町1番3号', $latitude = 35.67966, $longitude = 139.73669)
    {
        $message['type'] = 'location';
        $message['title'] = $title;
        $message['address'] = $address;
        $message['latitude'] = $latitude;//緯度
        $message['longitude'] = $longitude;//経度

        $this->message = $message;
        return $this;
    }

    /**
     * イメージマップメッセージ fn保留
     */

    /**
     * ボタンテンプレート
     */
    public function setButtonTemplateMessage($altText = 'This is a buttons template', $template = [])
    {
        $message['type'] = 'template';
        $message['altText'] = $altText;
        $message['template'] = $template;
        $message['template']['type'] = 'buttons';
        $message['template']['thumbnailImageUrl'] = $this->extensionCheck($template['thumbnailImageUrl'], 'png|jpeg|jpg');
        foreach ($template['actions'] as $key => $action) {
            $message['template']['actions'][$key] = $this->actionCheck($action);
        }

        $this->message = $message;
        return $this;
    }

    /**
     * 確認テンプレートメッセージ
     */
    public function setConfirmTemplateMessage($altText = 'This is a confirm template', $template = [])
    {
        $message['type'] = 'template';
        $message['altText'] = $altText;
        $message['template'] = $template;
        $message['template']['type'] = 'confirm';
        foreach ($template['actions'] as $key => $action) {
            $message['template']['actions'][$key] = $this->actionCheck($action);
        }
        $this->message = $message;
        return $this;
    }

    /**
     * カルーセルテンプレートメッセージ
     */
    public function setCarouselTemplateMessage($altText = 'This is a carousel template', $template = [])
    {
        $message['type'] = 'template';
        $message['altText'] = $altText;
        $message['template'] = $template;
        $message['template']['type'] = 'carousel';
        foreach ($template['columns'] as $index => $column) {
            $message['template']['columns'][$index]['thumbnailImageUrl'] = $this->extensionCheck($column['thumbnailImageUrl'], 'png|jpeg|jpg');
        }
        foreach ($template['columns'] as $index => $column) {
            foreach ($column['actions'] as $key => $action) {
                $message['template']['columns'][$index]['actions'][$key] = $this->actionCheck($action);
            }
        }
        $this->message = $message;
        return $this;
    }

    /**
     * 画像カルーセルテンプレートメッセージ
     */
    public function setImageCarouselTemplateMessage($altText = 'This is a image_carousel template', $template = [])
    {
        $message['type'] = 'template';
        $message['altText'] = $altText;
        $message['template'] = $template;
        $message['template']['type'] = 'image_carousel';
        foreach ($template['columns'] as $index => $column) {
            $message['template']['columns'][$index]['imageUrl'] = $this->extensionCheck($column['imageUrl'], 'png|jpeg|jpg');
            $message['template']['columns'][$index]['action'] = $this->actionCheck($column['action']);
        }
        $this->message = $message;
        return $this;
    }

    /**
     * Flexメッセージ
     */
    public function setFlexMessage($altText = 'This is a flex  template', $contents = [])
    {
        $message['type'] = 'flex';
        $message['altText'] = $altText;
        $message['contents'] = $contents;
        $message['contents']['type'] = 'bubble';
        $this->message = $message;
        return $this;
    }

    /**
     * 拡張子チェック 
     */
    public function extensionCheck($url, $pattern)
    {
        $ext = pathinfo($url, PATHINFO_EXTENSION);
        if (preg_match('/(' . $pattern . ')/', strtolower($ext))) {
            return $url;
        }
        return '';
    }

    /**
     * アクションチェック 
     */
    public function actionCheck($action)
    {
        //簡易的なチェックなので必須要件は下記から調べてください
        //https://developers.line.biz/ja/reference/messaging-api/#datetime-picker-action
        foreach (['postback', 'message', 'uri', 'datetimepicker', 'camera'] as $actionPattern) {
            if (preg_match('/(' . $actionPattern . ')/', strtolower($action['type']))) {
                return $action;
            }
        }
        return '';
    }

    /**
     * ポストメッセージ
     */
    public function postMessage($userId = null)
    {
        if($userId){
            $message = [
                'to' => $userId,
                'messages' => [$this->message]
            ];
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $this->access_token, 'Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_URL, $this->endpoint);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $this->response = curl_exec($ch);
            curl_close($ch);
        }

        return $this;
    }
}
// 通常メッセージ
// $text = '$あいう$';
// $emojis[0]['productId'] = '5ac1bfd5040ab15980c9b435';
// $emojis[0]['emojiId'] = '001';
// $emojis[1]['productId'] = '5ac1bfd5040ab15980c9b435';
// $emojis[1]['emojiId'] = '002';
// (new linePostMessage)->setTextMessage($text,$emojis)->postMessage();
//スタンプメッセージ
//(new linePostMessage)->setStickerMessage()->postMessage();
//画像メッセージ
// (new linePostMessage)->setImageMessage()->postMessage();
//動画メッセージ
//(new linePostMessage)->setVideoMessage()->postMessage();
//音声メッセージ
//(new linePostMessage)->setAudioMessage()->postMessage();
//位置情報メッセージ
//(new linePostMessage)->setLocationMessage()->postMessage();
//ボタンテンプレートメッセージ
// $template["thumbnailImageUrl"] = "https://example.com/bot/images/image.jpg";
// $template["imageAspectRatio" ]= "rectangle";
// $template["imageSize"]="cover";
// $template["imageBackgroundColor"]= "#FFFFFF";
// $template["title"]= "Menu";
// $template["text"]= "Please select";
// $template["defaultAction"]["type"] = "uri";
// $template["defaultAction"]["label"] = "View detail";
// $template["defaultAction"]["uri"] = "http://example.com/page/123";

// $template['actions'][0]["type"]="postback";
// $template['actions'][0]["label"]= "Buy";
// $template['actions'][0]["data"]= "action=buy&itemid=123";
// $template['actions'][1]["type"] ="postback";
// $template['actions'][1]["label"] = "Add to cart";
// $template['actions'][1]["data"] = "action=add&itemid=123";
// $template['actions'][2]["type"] = "uri";
// $template['actions'][2]["label"] = "View detail";
// $template['actions'][2]["uri"] = "http://example.com/page/123";
// (new linePostMessage)->setButtonTemplateMessage('This is a buttons template',$template)->postMessage();
//確認テンプレートメッセージ
// $template['text'] = "Are you sure?";
// $template['actions'][0]["type"]="message";
// $template['actions'][0]["label"]= "Yes";
// $template['actions'][0]["text"]= "Yes";
// $template['actions'][1]["type"] ="message";
// $template['actions'][1]["label"] = "No";
// $template['actions'][1]["text"] = "No";
// (new linePostMessage)->setConfirmTemplateMessage('This is a buttons template',$template)->postMessage();
//カルーセルテンプレートメッセージ
// $template = [
//     "columns" => [
//         [
//             "thumbnailImageUrl" => "https://example.com/bot/images/item1.jpg",
//             "imageBackgroundColor" => "#FFFFFF",
//             "title" => "this is menu",
//             "text" => "description",
//             "defaultAction" => [
//                 "type" => "uri",
//                 "label" => "View detail",
//                 "uri" => "http://example.com/page/123"
//             ],
//             "actions" => [
//                 [
//                     "type" => "postback",
//                     "label" => "Buy",
//                     "data" => "action=buy&itemid=111"
//                 ],
//                 [
//                     "type" => "postback",
//                     "label" => "Add to cart",
//                     "data" => "action=add&itemid=111"
//                 ],
//                 [
//                     "type" => "uri",
//                     "label" => "View detail",
//                     "uri" => "http://example.com/page/111"
//                 ]
//             ]
//         ],
//         [
//             "thumbnailImageUrl" => "https://example.com/bot/images/item2.jpg",
//             "imageBackgroundColor" => "#000000",
//             "title" => "this is menu",
//             "text" => "description",
//             "defaultAction" => [
//                 "type" => "uri",
//                 "label" => "View detail",
//                 "uri" => "http://example.com/page/222"
//             ],
//             "actions" => [
//                 [
//                     "type" => "postback",
//                     "label" => "Buy",
//                     "data" => "action=buy&itemid=222"
//                 ],
//                 [
//                     "type" => "postback",
//                     "label" => "Add to cart",
//                     "data" => "action=add&itemid=222"
//                 ],
//                 [
//                     "type" => "uri",
//                     "label" => "View detail",
//                     "uri" => "http://example.com/page/222"
//                 ]
//             ]
//         ]
//     ],
//     "imageAspectRatio" => "rectangle",
//     "imageSize" => "cover"
// ];
// (new linePostMessage)->setCarouselTemplateMessage('This is a buttons template', $template)->postMessage();
//画像カルーセルテンプレートメッセージ
// $template = [
//         "columns" => [
//             [
//                 "imageUrl" => "https://example.com/bot/images/item1.jpg",
//                 "action" => [
//                     "type" => "postback",
//                     "label" => "Buy",
//                     "data" => "action=buy&itemid=111"
//                 ]
//             ],
//             [
//                 "imageUrl" => "https://example.com/bot/images/item2.jpg",
//                 "action" => [
//                     "type" => "message",
//                     "label" => "Yes",
//                     "text" => "yes"
//                 ]
//             ],
//             [
//                 "imageUrl" => "https://example.com/bot/images/item3.jpg",
//                 "action" => [
//                     "type" => "uri",
//                     "label" => "View detail",
//                     "uri" => "http://example.com/page/222"
//                 ]
//             ]
//         ]
// ];
// (new linePostMessage)->setImageCarouselTemplateMessage('This is a buttons template', $template)->postMessage();
//Flex Message
// $contents = [
//     "body" => [
//         "type" => "box",
//         "layout" => "vertical",
//         "contents" => [
//             [
//                 "type" => "text",
//                 "text" => "hello"
//             ],
//             [
//                 "type" => "text",
//                 "text" => "world"
//             ]
//         ]
//     ]
// ];
// (new linePostMessage)->setFlexMessage('This is a buttons template', $contents)->postMessage();
