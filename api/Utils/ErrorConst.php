<?php
namespace App\Utils;

use DI\Container;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class ErrorConst
{
    use EnableDIAnnotations; //启用通过@inject标记注入依赖

    /**
     * @inject
     * @var Container
     */
    protected $container;

    public function getMessage($code)
    {
        $message = $this->container->get('Error.message');

        return isset($message[$code])?$message[$code]:$code;
    }

    /**
     * 模块报错前缀：
     * 公共：10
     * 产品: 11
     * 文章  12
     * 用户  13
     * 图文  14
     * 行业 15
     * 广告主 16
     * 虚拟商品 17
     * 邮件 18
     */
    const SYSTEM_ERROR = '000000';
    const COMMON_PARAMETER_MISSING = '100001';
    const NOT_LEGAL_JSON = '100002';
    const ID_CARD_NUMBER_ERROR = '100003';
    const MOBILE_ERROR = '100004';
    const FIELD_CAN_NOT_NULL = '100005';
    const DUPLICATE_INSERT = '100006';
    const MESSAGE_SENDING_FREQUENTLY = '100010';
    const VERIFICATION_ORDER_DOES_NOT_EXIST = '100008';
    const VERIFICATION_CODE_ERROR = '100009';
    const INSERT_ERROR = '100007';
    const VERIFY_MOBILE_MISMATCH = '100011';
    const UPDATE_ERROR = '100012';
    const SIGNATURE_ERROR = '100013';
    const PARAM_MISS = '100014';
    const TOKEN_MISS = '100015';
    const ZIDUAN_ERROR = '100016';
    const TOKEN_ERROR = '100017';
    const NAME_EXIST = '100018';
    const CURL_ERROR = '100019';
    const AUTH_ERROR = '100020';
    const TOKEN_SYS_ERROR = '100022';
    const REGION_NOT_FOUND = '100021';
    const LITE_NAME_ERROR = '100023';
    //11
    const CHANGE_STATUS_ERROR = '110001';
    const INSERT_PRODUCT_REGION_ERROR = '110002';
    const NO_MATCH_REGION_NAME = '110003';
    const PRODUCT_EXIST_REGION = '110004';
    const DELET_REGION_ERROR = '110005';
    const PRODUCT_NOT_EXIST  = '110006';
    const INSUFFICIENT_STOCK = '110007';
    const ORDER_CHACK_ERROR = '110008';
    const NO_SPLIT_METHODS = '110009';
    const NOT_FOUND_RELATE_WITH_ARTICLE = '110010';
    const CATEGORY_EXIST_REGION = '110011';
    const DELETE_REGIONCATEGORY_REGION = '110012';
    const GOODS_REPEAT_ERROR = '110013';
    const REPEAT_IMPORT = '110014';
    const REPEAT_ADD = '110015';
    const PRODUCT_BANNER_EXIST = '110016';
    //12
    const ARTICLE_INSERT_ERROR = '120001';
    const ARTICLE_CHANGE_STATUS_ERROR = '120002';
    const ARTICLE_CATEGORY_CHANGE_STATUS_ERROR = '120003';
    //13
    const MOBILE_EXIST = '130001';
    const CHANGE_PASS_FAIL = '130002';
    const SMS_VERIFY_EXPIRED = '130003';
    const LOGIN_ERROR = '130004';
    const ACCOUNT_LOCKED = '130005';
    const USER_CREATE_ERROR = '130006';
    const ADMIN_USER_DEL_FAIL = '130007';
    const INPUT_PASS_NOT_MATCH = '130008';
    const USER_NOT_FOUND = '130010';
    const NEWPASS_MATCH_OLDPASS = '130011';
    const RESETPASS_ERROR = '130012';
    const BANK_NUMBER_EXIST = '130013';
    const BANK_USER_INSERT_ERROR = '130014';
    const BANK_INFO_ERROR = '130015';
    const BANK_BINDED_NOTEXIST = '130016';
    const LACKOFBALANCE_ERROR = '130017';
    const USER_EXIST = '130018';
    const USER_ACCOUNT_NOT_FOUND = '130019';
    const USER_COMMISSION_RECORD_NOT_FOUND = '130020';
    const RESETPASS_CHECK_MOBILE_NOT_EXIST = '130021';
    const INVITE_SPREAD_UID_NOT_EXIST = '130022';
    const WITHDRAWAL_ERROR = '130023';
    const WITHDRAWAL_FREQUENT_ERROR = '130024';
    const AUTHENTICATION_ERROR = '130025';
    const BANK_INFO_NOT_EXIST = '130026';
    const BANK_BIND_EXIST = '130027';
    const SMS_AUHENT_EXPIRED = '130028';
    const USER_ADVERT_EXPIRED = '130029';
    const USER_ADVERT_NOT_EXPIRED = '130030';
    const MOBILE_NEEDED = '130031';
    const EMAIL_EXIT = '130032';
    const ADVERT_RELATIVE_NOT_EXIST = '130033';
    const ADVERT_RELATIVE_REVIEW_STATUS_ERROR = '130034';
    const ADVERT_RELATIVE_NAME_EXIST = '130035';
    const USER_NAME_EXIST = '130036';
    const USER_INVITE_BIND = '130037';
    const USER_NOT_EXIST = '130038';
    const EMPLOYEE_EXIST = '130039';
    const USER_INVITE_EACH = '130040';
    const ADDRESS_NOT_EXIST = '130041';
    const LITE_OPEN_NOT_FOUND = '130042';
    const UNION_ORDER_FAIL = '130043';
    const AMOUNT_ERROR = '130044';
    const CONTINUE_ORDER_NOT_FOUND = '130045';
    //14
    const IMGTITLE_DEL_ERROR = '140001';
    //15
    const PARENT_NOT_FOUND = '150001';
    const UPDATE_STATUS_ERROR = '150002';
    const INDUSTRY_USER_EXIST = '150003';
    //16
    const sORDER_NOT_FOUND = '160001';
    const RECORD_NOT_FOUND = '160002';

    //17
    const VIRTUAL_BIND_ERROR = '170001';//绑定商品失败
    const VIRTUAL_NO_GRANT = '170002';//没有获得绑定的激活码
    const VIRTUAL_USED = '170003';//激活码被使用
    const VIRTUAL_ADD_DIFF = '170004';//添加分类失败
    const VIRTUAL_DEL_DIFF = '170005';//删除分类失败
    const VIRTUAL_BIND_ADVERT = '170006';//查找分类绑定的代理商失败
    const VIRTUAL_STATISTICS = '170007';//查找统计失败
    const VIRTUAL_CODE_ERROR = '170008';//验证码错误
    const VIRTUAL_GOOD_GET_ERROR = '170009';//通过广告主获取虚拟商品失败
    const VIRTUAL_BUYER_CODE_ERROR = '170010';//获取购买者与激活码关系失败
    const VIRTUAL_TWO_ERROR = '170011';//输入二级分类不正确

    const VIRTUAL_BIND_EXIST = '170012';
    const VIRTUAL_BIND_NOT_EXIST = '170013';
    const VIRTUAL_INSERT_DETAIL_FAIL = '170014';

    const CODE_ERROR = '180001';
    const EMAIL_VALIDATE_ERROR = '180002';
    const EMAIL_SEND_ERROR = '180003';
    const EMAIL_SEND_CODE_ERROR = '180004';
}