<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/31 0031
 * Time: 17:20
 */

namespace System\Helper;


class MessageHelper
{
    const SUCCESS_MSG = '获取数据成功';
    const SUCCESS_CODE = 1000;

    const FAILURE_MSG = '获取数据失败';
    const FAILURE_CODE = -1000;

    const COMPANY_ID_ERROR_MSG = '非法的公司ID';
    const COMPANY_ID_ERROR_CODE = -4001;

    const COMPANY_NOT_FOUND_MSG = '查无此公司';
    const COMPANY_NOT_FOUND_CODE = -4002;

    const MANAGER_ID_ERROR_MSG = '非法的经理ID';
    const MANAGER_ID_ERROR_CODE = -2001;

    const MANAGER_NOT_FOUND_MSG = '查无此经理';
    const MANAGER_NOT_FOUND_CODE = -2002;

    const MANAGER_DATA_ERROR_MSG = '经理数据异常';
    const MANAGER_DATA_ERROR_CODE = -2003;

    const PRODUCT_ID_ERROR_MSG = '非法的产品ID';
    const PRODUCT_ID_ERROR_CODE = -3001;

    const PRODUCT_NOT_FOUND_MSG = '查无此产品';
    const PRODUCT_NOT_FOUND_CODE = -3002;
}