<?php
class ControllerExtensionModuleLlmsGenerator extends Controller {
    public function index() {
        $this->load->language('extension/module/llms_generator');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('module_llms_generator', $this->request->post);

            $this->generateLlmsFile();

            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/llms_generator', 'user_token=' . $this->session->data['user_token'], true)
            ]
        ];

        $data['action'] = $this->url->link('extension/module/llms_generator', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_llms_generator_status'])) {
            $data['module_llms_generator_status'] = $this->request->post['module_llms_generator_status'];
        } else {
            $data['module_llms_generator_status'] = $this->config->get('module_llms_generator_status');
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/llms_generator', $data));
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'extension/module/llms_generator')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    protected function generateLlmsFile() {
        $this->load->model('tool/llms_generator');
        
        try {
            $content = $this->model_tool_llms_generator->generateLlmsContent();
            $this->model_tool_llms_generator->saveLlmsFile($content);
        } catch (Exception $e) {
            $this->error['warning'] = $e->getMessage();
            return false;
        }
        
        return true;
    }
    
    public function generateLlms() {
        $this->load->model('tool/llms_generator');
        
        try {
            $content = $this->model_tool_llms_generator->generateLlmsContent();
            $this->model_tool_llms_generator->saveLlmsFile($content);
            
            $this->log->write('LLMS Generator: llms.txt was successfully updated.');
        } catch (Exception $e) {
            $this->log->write('LLMS Generator Error: ' . $e->getMessage());
        }
    }
    
    public function install() {
        $this->load->model('setting/event');
        
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/product/addProduct/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/product/editProduct/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/category/addCategory/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/category/editCategory/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/manufacturer/addManufacturer/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/manufacturer/editManufacturer/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/information/addInformation/after', 'extension/module/llms_generator/generateLlms');
        $this->model_setting_event->addEvent('llms_generator', 'admin/model/catalog/information/editInformation/after', 'extension/module/llms_generator/generateLlms');
        
        $this->generateLlmsFile();
    }
    
    public function uninstall() {
        $this->load->model('setting/event');
        
        $this->model_setting_event->deleteEventByCode('llms_generator');
    }
}