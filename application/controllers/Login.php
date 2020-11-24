<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function google_login(){
		$re_url = $this->input->get_post('re_url',true);
		if(!$re_url){
			$re_url = '/';
		}else{
			$this->session->set_userdata('re_url',$re_url);
		}

		if($_SESSION['re_url']){
			$re_url = $_SESSION['re_url'];
		}


		#구글 로그인
		$this->load->library("social_login/google_login");
		$result = $this->google_login->get_profile();

		#로그인 처리
		if($result){
			$result = json_decode($result , true);
			$user_id = '_gl_'.$result['sub'];

			#회원가입
			$indata['id'] = $user_id;
			$indata['name'] = $result['name'];
			$indata['nickname'] = $result['name'];
			$indata['email'] = $result['email'];
			$indata['profile_image_url'] = $result['picture'];

			if($this->db->insert('users',$indata)){
				# insert 후 로그인처리, 세션처리

				echo '<script>location.href="'.$re_url.'"</script>';
				exit;
			}
		}else{
			echo '<script>alert("로그인에 실패하였습니다. 다시 시도 해주세요.")</script>';
			exit;
		}


		echo '<script>location.href="'.$re_url.'"</script>';
		exit();
	}

	public function facebook_login(){
		$re_url = $this->input->get_post('re_url',true);
		if(!$re_url){
			$re_url = '/';
		}else{
			$this->session->set_userdata('re_url',$re_url);
		}

		if($_SESSION['re_url']){
			$re_url = $_SESSION['re_url'];
		}

		$this->load->library("social_login/facebook_login");
		$result = $this->facebook_login->get_profile();

		#로그인 처리
		if($result){
			$user_id = '_fb_'.$result['id'];
			#회원가입
			$indata['id'] = $user_id;
			$indata['name'] = $result['name'];
			$indata['nickname'] = $result['name'];
			$indata['email'] = $result['email'];
			$indata['profile_image_url'] = $result['picture']['data']['url'];

			if($this->db->insert('users',$indata)){
				# insert 후 로그인처리, 세션처리

				echo '<script>location.href="'.$re_url.'"</script>';
				exit;
			}
		}else{
			echo '<script>alert("로그인에 실패하였습니다. 다시 시도 해주세요.")</script>';
			exit;
		}


		echo '<script>location.href="'.$re_url.'"</script>';
		exit();
	}

	public function apple_login(){
        //로그인 후 돌아올 페이지 url
		$re_url = $this->input->get_post('re_url',true);
		if(!$re_url){
			$re_url = '/';
		}else{
			$this->session->set_userdata('re_url',$re_url);
		}

		if($_SESSION['re_url']){
			$re_url = $_SESSION['re_url'];
		}

        //라이브러리 load
		$this->load->library("social_login/apple_login");
		$result = $this->apple_login->get_profile();

		#로그인
		if($result){
            # 회원가입 정보
            $$indata['user_id'] = '_ap_'.$result['sub']; # sub 은 애플 고유 id 입니다.
            $indata['email'] = $result['email'] ? $result['email'] : null; # 애플에서 가져온 이메일 입니다. (그런데 특정 계정은 안불러오더라고요...)

            if($this->db->insert('users',$indata)){
                # insert 후 로그인처리, 세션처리

                echo '<script>location.href="'.$re_url.'"</script>';
                exit;
            }
		}else{
			echo '<script>alert("로그인에 실패하였습니다. 다시 시도 해주세요.")</script>';
			exit;
		}


		echo '<script>location.href="'.$re_url.'"</script>';
		exit();
	}
}
