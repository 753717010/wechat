<?php
/**
 * 消息基类
 * Author: 风ysm
 */
namespace Cje\Wechat\officialAccount;

use Cje\Wechat\bases\Message as BaseMessage;

/**
 * 官方账号消息基类
 * @property-read string $original
 * @property string $FromUserName
 * @property string $ToUserName
 * @property string $Encrypt
 * @property string $encryptStr
 * @property string $MsgType
 * @property string $Event
 * @property string $CreateTime
 */
class Message extends BaseMessage
{

}
