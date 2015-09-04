<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
	protected $data = array();
	function __construct()
	{
		parent::__construct();

        $this->config->load('app_config',TRUE);
        $this->data['website'] = $this->config->item('website_name','app_config');
        $this->data['page_title'] = $this->data['website'];
        $this->data['page_description'] = $this->config->item('website_description','app_config');;
		$this->data['before_head'] = '';
		$this->data['before_body'] = '';
	}

	protected function render($the_view = NULL, $template = 'master')
	{
		if($template == 'json' || $this->input->is_ajax_request())
		{
			header('Content-Type: application/json');
			echo json_encode($this->data);
		}
		elseif(is_null($template))
		{
			$this->load->view($the_view,$this->data);
		}
		else
		{
			$this->data['the_view_content'] = (is_null($the_view)) ? '' : $this->load->view($the_view, $this->data, TRUE);
			$this->load->view('templates/' . $template . '_view', $this->data);
		}
	}
}

class Admin_Controller extends MY_Controller
{

	function __construct()
	{
		parent::__construct();

        $this->load->library(array('session','form_validation'));
        // if user is not logged in redirect
        if (!$_SESSION['logged_in'])
        {
            //redirect them to the login page
            redirect('admin/user/login', 'refresh');
        }
        $current_user = $this->ion_auth->user()->row();
        $this->user_id = $current_user->id;
        $_SESSION['user_id'] = $this->user_id;
        $this->data['current_user'] = $current_user;
		$this->data['current_user_menu'] = '';
		if($this->ion_auth->in_group('admin'))
		{
			$this->data['current_user_menu'] = $this->load->view('templates/_parts/user_menu_admin_view.php', NULL, TRUE);
		}

		$this->data['page_title'] = 'CI App - Dashboard';
	}
	protected function render($the_view = NULL, $template = 'admin_master')
	{
		parent::render($the_view, $template);
	}
}

class Public_Controller extends MY_Controller
{
    function __construct()
	{
        parent::__construct();
        $this->load->model('banned_model');
        $ips = $this->banned_model->get_all();
        print_r($ips);
        $banned_ips = array();
        if(!empty($ips))
        {
            foreach($ips as $ip)
            {
                $banned_ips[] = $ip->ip;
            }
        }
        if(in_array($_SERVER['REMOTE_ADDR'],$banned_ips))
        {
            echo 'You are banned from this site.';
            exit;
        }
        if($this->config->item('website_status','app_config') == '0') {
            if (TRUE) {
                redirect('offline', 'refresh', 503);
            }
        }
	}

    protected function render($the_view = NULL, $template = 'public_master')
    {
        $this->load->library('menus');
        $this->data['top_menu'] = $this->menus->get_menu('top-menu','bootstrap_menu');
        parent::render($the_view, $template);
    }


}