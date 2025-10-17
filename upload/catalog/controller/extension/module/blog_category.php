<?php
class ControllerExtensionModuleBlogCategory extends Controller {
    public function index($setting) {
        $this->load->language('extension/module/blog_category');

        $data['heading_title'] = $this->language->get('heading_title');

        if (isset($this->request->get['path'])) {
            $parts = explode('_', (string)$this->request->get['path']);
        } else {
            $parts = array();
        }

        if (isset($parts[0])) {
            $data['blog_category_id'] = $parts[0];
        } else {
            $data['blog_category_id'] = 0;
        }

        if (isset($parts[1])) {
            $data['child_id'] = $parts[1];
        } else {
            $data['child_id'] = 0;
        }

        $this->load->model('catalog/blog_category');

        $data['categories'] = $this->getCategories(0);

        return $this->load->view('extension/module/blog_category', $data);
    }

    protected function getCategories($parent_id, $current_path = '') {
        $categories = [];

        $results = $this->model_catalog_blog_category->getBlogCategories($parent_id);

        foreach ($results as $result) {
            if (!$current_path) {
                $new_path = $result['blog_category_id'];
            } else {
                $new_path = $current_path . '_' . $result['blog_category_id'];
            }

            $children = $this->getCategories($result['blog_category_id'], $new_path);

            $categories[] = [
                'blog_category_id' => $result['blog_category_id'],
                'name'             => $result['name'],
                'children'         => $children,
                'href'             => $this->url->link('information/blog_category', 'path=' . $new_path)
            ];
        }

        return $categories;
    }
}