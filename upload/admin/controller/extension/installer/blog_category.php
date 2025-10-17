<?php
class ControllerExtensionInstallerBlogCategory extends Controller {
    public function index() {
        $this->load->language('extension/installer');
        
        // Add menu item
        $this->load->model('setting/module');
        $module_data = array(
            'name' => 'Blog Category',
            'code' => 'blog_category',
            'status' => 1
        );
        $this->model_setting_module->addModule('blog_category', $module_data);
        
        // Add permissions
        $this->load->model('user/user_group');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/blog_category');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/blog_category');
    }
}