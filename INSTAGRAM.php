<?php
class INSTAGRAM
{
    protected $client_id;
    protected $client_secret;
    protected $access_token;
    protected $redirect_uri;
    protected $user_id;
    protected $long_term_access_token;
    protected $media;

    public function __construct($client_id, $client_secret, $redirect_uri)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
    }

    public function getOAuthCode()
    {
        if (isset($_GET['error']) && $_GET['error']) {
            throw new Exception($_GET['error_description']);
        }

        if (isset($_GET['code']) && $_GET['code']) {
            return trim($_GET['code']);
        }

        return false;
    }

    public function getAccessToken()
    {
        $url = 'https://api.instagram.com/oauth/access_token';

        $code = $this->getOAuthCode();

        if (!$code) {
            throw new Exception('There is no code value.');
        }

        $data = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'authorization_code',
            'redirect_uri' => $this->redirect_uri,
            'code' => $code
        ];

        $result = $this->sendPostRequest($url, $data);

        $result = json_decode($result, true);

        if (!is_array($result)) {
            throw new Exception('It is not an array type.');
        }

        if (isset($result['error_type']) && $result['error_type']) {
            throw new Exception($result['error_message'], $result['code']);
        }

        if (isset($result['access_token']) && $result['access_token']) {
            $this->access_token = trim($result['access_token']);
        }

        if (isset($result['user_id']) && $result['user_id']) {
            $this->user_id = trim($result['user_id']);
        }
    }

    public function getLongTermAccessToken()
    {
        $url = 'https://graph.instagram.com/access_token?grant_type=ig_exchange_token&client_secret=' . $this->client_secret . '&access_token=' . $this->access_token;

        $result = $this->sendGetRequest($url);

        $result = json_decode($result, true);

        if (!is_array($result)) {
            throw new Exception('It is not an array type.');
        }

        if (isset($result['error_type']) && $result['error_type']) {
            throw new Exception($result['error_message'], $result['code']);
        }

        if (isset($result['access_token']) && $result['access_token']) {
            $this->long_term_access_token = trim($result['access_token']);
        }

        return $this->long_term_access_token;
    }

    public function setUserId($user_id)
    {
        if (strlen($user_id) < 1) {
            throw new Exception('There is no user_id value.');
        }

        $this->user_id = $user_id;
    }

    public function setLongTermAccessToken($token)
    {
        if (strlen($token) < 1) {
            throw new Exception('There is no long term access_token value.');
        }

        $this->long_term_access_token = $token;
    }

    public function refreshAccessToken()
    {
        $url = 'http://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=' . $this->long_term_access_token;

        $result = $this->sendGetRequest($url);

        $result = json_decode($result, true);

        if (!is_array($result)) {
            throw new Exception('It is not an array type.');
        }

        if (isset($result['error_type']) && $result['error_type']) {
            throw new Exception($result['error_message'], $result['code']);
        }

        if (isset($result['access_token']) && $result['access_token']) {
            $this->long_term_access_token = trim($result['access_token']);
        }
    }

    public function saveAccessToken()
    {
        if (!is_dir(DATA_PATH) || !is_writable(DATA_PATH)) {
            throw new Exception('No permission to write to the directory.');
        }

        $file = DATA_PATH . '/' . TOKEN_FILE;

        if (is_file($file)) {
            unlink($file);
        }

        $fp = fopen($file, 'w');

        fwrite($fp, "<?php\n");
        fwrite($fp, "\$user_id = '" . $this->user_id . "';\n");
        fwrite($fp, "\$access_token = '" . $this->long_term_access_token . "';\n");

        fclose($fp);
    }

    public function getMedia()
    {
        $file = DATA_PATH . '/' . MEDIA_FILE;

        if (!is_file($file)) {
            $this->getMediaData();
        } else {
            require $file;

            $this->media = $media;
        }

        if (!$this->media) {
            throw new Exception('There is no media data.');
        }

        return $this->media;
    }

    protected function getMediaData()
    {
        $url = 'https://graph.instagram.com/' . $this->user_id . '/media?fields=id,media_type,media_url,permalink,thumbnail_url,username,caption&access_token=' . $this->long_term_access_token;

        $headers = [
            'Accept: ' . $_SERVER['HTTP_ACCEPT'],
            'Accept-Language: ' . $_SERVER['HTTP_ACCEPT_LANGUAGE'],
            'Cache-Control: no-cache',
            'User-Agent: ' . $_SERVER['HTTP_USER_AGENT']
        ];

        $this->media = $this->sendGetRequest($url, $headers);

        $result = json_decode($this->media, true);

        if (isset($result['error']) && !empty($result['error'])) {
            throw new Exception($result['error']['message'], $result['error']['code']);
        }

        $this->saveMediaData();
    }

    protected function saveMediaData()
    {
        if (!is_dir(DATA_PATH) || !is_writable(DATA_PATH)) {
            throw new Exception('No permission to write to the directory.');
        }

        $file = DATA_PATH . '/' . MEDIA_FILE;

        $mtime = filemtime($file);

        if ($mtime < time() - MEDIA_REFRESH_LIMIT * 60 * 60) {
            unlink($file);

            $fp = fopen($file, 'w');

            fwrite($fp, "<?php\n");
            fwrite($fp, "\$media = base64_decode('" . base64_encode($this->media) . "');\n");
        }
    }

    protected function sendGetRequest($url, $headers = [])
    {
        return $this->sendRequest($url, [], false, $headers);
    }

    protected function sendPostRequest($url, $data = [], $headers = [])
    {
        return $this->sendRequest($url, $data, true, $headers);
    }

    protected function sendRequest($url, $data = [], $post = false, $headers = [])
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($ch);

        curl_close($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), curl_errno($ch));
        }

        return $result;
    }
}
