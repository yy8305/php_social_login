<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['naver_login']['client_id']         = "네아로 클라이언트 ID";
$config['naver_login']['client_secret']     = "네아로 클라이언트 secret";
$config['naver_login']['redirect_uri']  = "네아로 Redirect URI";
$config['naver_login']['authorize_url'] = "https://nid.naver.com/oauth2.0/authorize";
$config['naver_login']['token_url']     = "https://nid.naver.com/oauth2.0/token";
$config['naver_login']['info_url']      = "https://openapi.naver.com/v1/nid/me";
$config['naver_login']['token_request_post'] = FALSE;

$config['facebook_login']['client_id']  = "";      // 페이스북 앱 ID 입력
$config['facebook_login']['client_secret']= "";   // 페이스북 앱 시크릿 코드
$config['facebook_login']['redirect_uri']   = "https://". $_SERVER['HTTP_HOST']."/login/facebook_login";
$config['facebook_login']['authorize_url']= "https://www.facebook.com/dialog/oauth";
$config['facebook_login']['token_url']  = "https://graph.facebook.com/v2.4/oauth/access_token";
$config['facebook_login']['info_url']       = "https://graph.facebook.com/v2.4/me";
$config['facebook_login']['token_request_post'] = FALSE;

$config['kakao_login']['client_id']     = "카카오 로그인 REST API KEY";   // REST API 키를 입력
$config['kakao_login']['client_secret'] = "";   // 카카오는 Client Secret을 사용하지 않습니다. 공백으로 지정
$config['kakao_login']['redirect_uri']  = "";
$config['kakao_login']['authorize_url'] = "https://kauth.kakao.com/oauth/authorize";
$config['kakao_login']['token_url']     = "https://kauth.kakao.com/oauth/token";
$config['kakao_login']['info_url']      = "https://kapi.kakao.com/v2/user/me";
$config['kakao_login']['token_request_post'] = FALSE;

$config['google_login']['client_id']        = "";
$config['google_login']['client_secret']    = "";
$config['google_login']['redirect_uri']     = "http://". $_SERVER['HTTP_HOST']."/login/google_login";
$config['google_login']['authorize_url']    = "https://accounts.google.com/o/oauth2/auth";
$config['google_login']['token_url']        = "https://www.googleapis.com/oauth2/v4/token";
$config['google_login']['info_url']         = "https://www.googleapis.com/oauth2/v3/userinfo";
$config['google_login']['token_request_post'] = TRUE;

$config['apple_login']['client_id']  = "apple 개발자 사이트에 나와있습니다. ex) com.test.test";
$config['apple_login']['client_secret']= ""; #따로 설정 안해도 됩니다.
$config['apple_login']['redirect_uri']   = "";
$config['apple_login']['authorize_url']= "https://appleid.apple.com/auth/authorize";
$config['apple_login']['token_url']  = "https://appleid.apple.com/auth/token";
$config['apple_login']['info_url']       = "https://appleid.apple.com/auth/authorize";
$config['apple_login']['key_url']  = "https://appleid.apple.com/auth/keys";
$config['apple_login']['token_request_post'] = 1; //post로 전송할지 여부
$config['apple_login']['kid']  = "key id";
$config['apple_login']['iss']  = "team id";
$config['apple_login']['key_file_path']  = "apple key (*.p8) -> pem으로 변환 된 파일 (*.pem)";
