class Cloudhubapi{    
    /**
     * ��֮��openapi OAuthǩ��Э�������
     */
    private $yzj_openapi_oauth_host = "https://www.yunzhijia.com";  //��Ӧ�ÿ���
    /**
     * ��֮��openapi OAuthǩ��Э���Ӧ��ID��������Ӧ��ID-��Ӧ�ÿ�����
     */
    private $yzj_openapi_oauth_app_id = "10323";
    /**
     * ��֮��openapi OAuthǩ��Э���Ӧ��secret��������Ӧ����Կ-��Ӧ�ÿ�����
     */
    private $yzj_openapi_oauth_app_secret = "xxxxxxxxxxxxxxxx";   


     /**
     * [��֮����Ӧ�ýӿ�-openapi oauthǩ��Э��]������Ϣ
     * @param $data
     * @return mixed
     */
    public function yzj_openapi_oauth_getperson($data){
        //�ӿ�url
        $yzj_url = $this->yzj_openapi_oauth_host."/openapi/third/v1/opendata-control/data/getperson";

        //����Http��Header����ͷ�е�Authorization��ֵ
        $authorization = $this->_yzj_openapi_oauth_authorization($yzj_url,$data);

        //http��ͷ��Ϣ
        $headers = array("Content-Type: application/x-www-form-urlencoded;charset=utf-8","Authorization: ".$authorization);

        //���ýӿ�
        $yzj_josn = _kis_http_restfull_curl($yzj_url,"POST",$data,$headers);

        //jsonת��Ϊ����
        $yzjobj = json_decode($yzj_josn);
        
        //����
        return $yzjobj;
    }

    /**
     * ��֮��openapi oauthǩ��Э������http��header�е�Authorization��ֵ
     * @param $url
     * @param $post_data
     * @return string
     */
    private function _yzj_openapi_oauth_authorization($url,$post_data){
        $timestamp = time();             //ʱ���
        $nonce = time().rand(10,1000);   //�����
        //ǩ������
        $params = array();
        $params["oauth_version"]          = "1.0";
        $params["oauth_signature_method"] = "HMAC-SHA1";
        $params["oauth_timestamp"]        = $timestamp;
        $params["oauth_nonce"]            = $nonce;
        $params["oauth_consumer_key"]     = $this->yzj_openapi_oauth_app_id;       
        
        //�ѿͻ��������������ǩ������
        foreach($post_data as $k=>$v){
            $params[$k] = urlencode($v);
        }

        //�������ǩ���ַ���
        $sigstr = "POST"."&".urlencode($url)."&";
        //�Բ���������ĸ���������л�
        $normalized_str = $this->_get_yzj_openapi_oauth_normalized_string($params);
        $sigstr        .= urlencode($normalized_str);

        //��Կ
        $key = $this->yzj_openapi_oauth_app_secret."&";

        //����oauth_signatureǩ��ֵ��������Ҫȷ��PHP�汾֧��hash_hmac����
        $signature = urlencode($this->_get_yzj_openapi_oauth_signature($sigstr, $key));

        //����Http��Header����ͷ�е�Authorization��ֵ
        $authorization = sprintf('OAuth oauth_consumer_key="%s",oauth_signature_method="HMAC-SHA1",oauth_timestamp="%s",oauth_nonce="%s",oauth_version="1.0",oauth_signature="%s"',$this->yzj_openapi_oauth_app_id,$timestamp,$nonce,$signature);

        //����Authorizationֵ
        return $authorization;
    }

    /**
     * ��֮��openapi oauthǩ��Э�������������
     * @param $params
     * @return string
     */
    private function _get_yzj_openapi_oauth_normalized_string($params)
    {
        //����ĸ��������
        ksort($params);
        $normalized = array();
        foreach($params as $key => $val)
        {
            $normalized[] = $key."=".$val;
        }
        //
        return implode("&", $normalized);
    }

    /**
     * ��֮��openapi oauthǩ��Э��HMAC-SHA1����
     * @param $str
     * @param $key
     * @return string
     */
    private function _get_yzj_openapi_oauth_signature($str, $key)
    {
        $signature = "";
        if (function_exists('hash_hmac')){
            $signature = base64_encode(hash_hmac("sha1", $str, $key, true));
        }
        else{
            $blocksize	= 64;
            $hashfunc	= 'sha1';
            if (strlen($key) > $blocksize)
            {
                $key = pack('H*', $hashfunc($key));
            }
            $key	= str_pad($key,$blocksize,chr(0x00));
            $ipad	= str_repeat(chr(0x36),$blocksize);
            $opad	= str_repeat(chr(0x5c),$blocksize);
            $hmac 	= pack(
                'H*',$hashfunc(
                    ($key^$opad).pack(
                        'H*',$hashfunc(
                            ($key^$ipad).$str
                        )
                    )
                )
            );
            $signature = base64_encode($hmac);
        }
        //
        return $signature;
    }


    /**
    * ͨ��curlģ��http�ύ���ݣ�֧��POST,GET,PUT,DELETE��
    * @param $URL  //������Դurl
    * @param $type  //������Դ���ͣ�֧��POST,GET,PUT,DELETE��
    * @param $data  //��������
    * @param $headers //httpͷ��Ϣ(����)
    * @return mixed
    */
    private function _kis_http_restfull_curl($URL,$type,$data,$headers=""){
    //���$data���ַ���
    if(is_string($data)){
        $params = $data;
    }
    else{
        //$data������
        $values = array();
        foreach ($data as $key=>$val){
            $values[]="$key=".urlencode($val);
        }
        //
        $params = implode("&",$values);
    }

    //��ʼcurl����
    $ch = curl_init();
   
    //��Դ��ַ
    curl_setopt ($ch, CURLOPT_URL, $URL);
    //https������֤֤�鴦��
    if(strpos(strtolower($URL),"https://") !== false)
    {
        //https���� ����֤֤���hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    //httpͷ�ļ����ʹ���
    if($headers == ""){
        curl_setopt ($ch, CURLOPT_HTTPHEADER, false); //CURLOPT_HEADERֵΪfalse����(Ĭ��x-www-from-urlencod��ʽ�ύ����)
    }
    else {
        curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
    }
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
   
    //�������ʹ���
    $type = strtoupper($type); //ת��Ϊ��д
    if($type == "GET"){
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }
    elseif($type == "POST"){
        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
    }
    elseif($type == "PUT"){
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
    }
    elseif($type == "DELETE"){
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
    }
    //
     $ret_contents = curl_exec($ch); //��÷���ֵ
     curl_close($ch);
     return $ret_contents;
   }
}


//����Demo(�Ի�ȡ��֮�Ҹ�����Ϣ�ӿ�Ϊ��)

//�������
$post_data["eid"] = "7687143";
$post_data["openId"] = "5775e284e4b0bb1fc15dd513";
//�ӿڵ���
$cloudhub = new Cloudhubapi();
$yzj_obj = $cloudhub->yzj_openapi_oauth_getperson($post_data);

print_r($yzj_obj);