<?php
/**
 * FecShop file.
 *
 * @link http://www.fecshop.com/
 * @copyright Copyright (c) 2016 FecShop Software LLC
 * @license http://www.fecshop.com/license/
 */

namespace fecshop\app\appadmin\modules\Config\block\appserverpayment;

use fec\helpers\CUrl;
use fec\helpers\CRequest;
use fecshop\app\appadmin\interfaces\base\AppadminbaseBlockEditInterface;
use fecshop\app\appadmin\modules\AppadminbaseBlockEdit;
use Yii;

/**
 * block cms\staticblock.
 * @author Terry Zhao <2358269014@qq.com>
 * @since 1.0
 */
class Manager extends AppadminbaseBlockEdit implements AppadminbaseBlockEditInterface
{
    public $_saveUrl;
    // 需要配置
    public $_key = 'appserver_payment';
    public $_type;
    protected $_attrArr;
    public $_editArr;
    
    public function init()
    {
         // 需要配置
        $this->_saveUrl = CUrl::getUrl('config/appserverpayment/managersave');
        $this->_editFormData = 'editFormData';
        $this->setService();
        $this->_param = CRequest::param();
        $this->_one = $this->_service->getByKey([
            'key' => $this->_key,
        ]);
        if ($this->_one['value']) {
            $this->_one['value'] = unserialize($this->_one['value']);
        }
        $this->_getEditArr();
    }
    
    
    
    // 传递给前端的数据 显示编辑form
    public function getLastData()
    {
        $id = ''; 
        if (isset($this->_one['id'])) {
           $id = $this->_one['id'];
        } 
        return [
            'id'            =>   $id, 
            'editBar'      => $this->getEditBar(),
            'textareas'   => $this->_textareas,
            'lang_attr'   => $this->_lang_attr,
            'saveUrl'     => $this->_saveUrl,
        ];
    }
    public function setService()
    {
        $this->_service = Yii::$service->storeBaseConfig;
    }
    protected function _getEditArr()
    {
        $deleteStatus = Yii::$service->customer->getStatusDeleted();
        $activeStatus = Yii::$service->customer->getStatusActive();
        
        $this->_editArr = [
            // 需要配置
            [
                'label' => Yii::$service->page->translate->__('Check Money'),
                'name'  => 'check_money',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => '线下支付'
            ],
            
            [
                'label' => Yii::$service->page->translate->__('Paypal Standard'),
                'name'  => 'paypal_standard',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => 'Paypal标准支付'
            ],
            [
                'label' => Yii::$service->page->translate->__('Paypal Express'),
                'name'  => 'paypal_express',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => 'Paypal快捷支付'
            ],
            
            [
                'label' => Yii::$service->page->translate->__('Alipay Standard'),
                'name'  => 'alipay_standard',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => '支付宝支付'
            ],
            
            [
                'label' => Yii::$service->page->translate->__('Wxpay Standard'),
                'name'  => 'wxpay_standard',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => '微信PC支付'
            ],
            
            [
                'label' => Yii::$service->page->translate->__('Wxpay Jsapi'),
                'name'  => 'wxpay_jsapi',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => '微信内部支付JsApi'
            ],
            
            [
                'label' => Yii::$service->page->translate->__('Wxpay Html5'),
                'name'  => 'wxpay_h5',
                'display' => [
                    'type' => 'select',
                    'data' => [
                        Yii::$app->store->enable => 'Enable',
                        Yii::$app->store->disable => 'Disable',
                    ],
                ],
                'remark' => '微信支付手机浏览器html5'
            ],
            
        ];
        $beforeEventName = 'event_before_save_payment_config';
        Yii::$service->event->trigger($beforeEventName, $this);
        foreach ($this->_editArr as $one) {
            $this->_attrArr[] = $one['name'];
        }
        
    }
    
    public function getEditArr()
    {
        return $this->_editArr;
    }
    
    public function getArrParam(){
        $request_param = CRequest::param();
        $this->_param = $request_param[$this->_editFormData];
        $param = [];
        $attrVals = [];
        foreach($this->_param as $attr => $val) {
            if (in_array($attr, $this->_attrArr)) {
                $attrVals[$attr] = $val;
            } else {
                $param[$attr] = $val;
            }
        }
        $param['value'] = $attrVals;
        $param['key'] = $this->_key;
        
        return $param;
    }
    
    /**
     * save article data,  get rewrite url and save to article url key.
     */
    public function save()
    {
        /*
         * if attribute is date or date time , db storage format is int ,by frontend pass param is int ,
         * you must convert string datetime to time , use strtotime function.
         */
        // 设置 bdmin_user_id 为 当前的user_id
        $this->_service->saveConfig($this->getArrParam());
        $errors = Yii::$service->helper->errors->get();
        if (!$errors) {
            echo  json_encode([
                'statusCode' => '200',
                'message'    => Yii::$service->page->translate->__('Save Success'),
            ]);
            exit;
        } else {
            echo  json_encode([
                'statusCode' => '300',
                'message'    => $errors,
            ]);
            exit;
        }
    }
    
    
    
    public function getVal($name, $column){
        if (is_object($this->_one) && property_exists($this->_one, $name) && $this->_one[$name]) {
            
            return $this->_one[$name];
        }
        $content = $this->_one['value'];
        if (is_array($content) && !empty($content) && isset($content[$name])) {
            
            return $content[$name];
        }
        
        return '';
    }
}