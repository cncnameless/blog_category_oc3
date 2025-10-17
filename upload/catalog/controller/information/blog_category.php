<?php
class ControllerInformationBlogCategory extends Controller {
    public function index() {
        $this->load->language('information/blog_category');

        $this->load->model('catalog/blog_category');
        $this->load->model('catalog/information');
        $this->load->model('tool/image');

        if (isset($this->request->get['path'])) {
            $path = $this->request->get['path'];
        } else {
            $path = '';
        }

        $parts = explode('_', (string)$path);
        $blog_category_id = (int)array_pop($parts);

        $url = '';

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $limit = 12;

        $data['heading_title'] = $this->model_catalog_blog_category->getBlogCategory($blog_category_id)['name'] ?? $this->language->get('heading_title');

        // Подкатегории
        $data['categories'] = [];
        $categories = $this->model_catalog_blog_category->getBlogCategories($blog_category_id);
        foreach ($categories as $category) {
            $thumb = $category['image'] ? $this->model_tool_image->resize($category['image'], 300, 200) : $this->model_tool_image->resize('no_image.png', 300, 200);
            $data['categories'][] = [
                'name' => $category['name'],
                'thumb' => $thumb,
                'href' => $this->url->link('information/blog_category', 'path=' . $path . '_' . $category['blog_category_id'] . $url)
            ];
        }

        // Статьи
        $data['articles'] = [];
        $filter_data = [
            'filter_blog_category_id' => $blog_category_id,
            'order' => 'DESC',
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        ];

        $article_total = $this->model_catalog_information->getTotalInformations($filter_data);
        $results = $this->model_catalog_information->getInformations($filter_data);

        foreach ($results as $result) {
            $thumb = $result['image'] ? $this->model_tool_image->resize($result['image'], 300, 200) : $this->model_tool_image->resize('no_image.png', 300, 200);

            $description = html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8');
            $short_desc = utf8_substr(strip_tags($description), 0, 200) . '..';
            $word_count = str_word_count(strip_tags($description));
            $reading_time = ceil($word_count / 200); // Минуты

            $data['articles'][] = [
                'name'         => $result['title'],
                'description'  => $short_desc,
                'href'         => $this->url->link('information/information', 'information_id=' . $result['information_id']),
                'thumb'        => $thumb,
                'date_added'   => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'author'       => $result['author'] ?? 'Admin',
                'views'        => $result['views'],
                'reading_time' => $reading_time
            ];
        }

        // Пагинация
        $pagination = new Pagination();
        $pagination->total = $article_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('information/blog_category', 'path=' . $path . $url . '&page={page}');

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($article_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($article_total - $limit)) ? $article_total : ((($page - 1) * $limit) + $limit), $article_total, ceil($article_total / $limit));

        // Стандартные данные
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_blog'),
            'href' => $this->url->link('information/blog_category')
        ];

        // Добавьте крошки для пути категорий

        $this->document->setTitle($data['heading_title']);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('information/blog_category', $data));
    }
}