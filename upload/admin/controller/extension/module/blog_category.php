<?php
class ControllerExtensionModuleBlogCategory extends Controller {
    private $error = array();

    public function index() {
        $this->load->language('extension/module/blog_category');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_blog_category', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/blog_category', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/blog_category', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_blog_category_status'])) {
            $data['module_blog_category_status'] = $this->request->post['module_blog_category_status'];
        } else {
            $data['module_blog_category_status'] = $this->config->get('module_blog_category_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/blog_category', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/blog_category')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install() {
        $this->load->model('user/user_group');
        
        // Добавляем права доступа для админов
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/blog_category');
        $this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/blog_category');
        
        // Создаем необходимые таблицы для блога
        $this->load->model('extension/module/blog_category');
        if (method_exists($this->model_extension_module_blog_category, 'install')) {
            $this->model_extension_module_blog_category->install();
        }
        
        // Устанавливаем модуль в системе
        $this->load->model('setting/extension');
        $this->model_setting_extension->install('module', 'blog_category');

        // Добавляем запись в таблицу module
        $this->load->model('setting/module');
        $this->model_setting_module->addModule('blog_category', array(
            'name' => 'Blog Category',
            'status' => 1
        ));
    }

    public function uninstall() {
        // Удаляем модуль из системы
        $this->load->model('setting/extension');
        $this->model_setting_extension->uninstall('module', 'blog_category');
        
        // Удаляем таблицы блога если нужно
        $this->load->model('extension/module/blog_category');
        if (method_exists($this->model_extension_module_blog_category, 'uninstall')) {
            $this->model_extension_module_blog_category->uninstall();
        }

        // Удаляем запись из таблицы module
        $this->load->model('setting/module');
        $this->model_setting_module->deleteModulesByCode('blog_category');
    }
}