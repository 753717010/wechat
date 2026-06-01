<?php

use PHPUnit\Framework\TestCase;
use Cje\Wechat\helper\Xml;

class XmlTest extends TestCase
{
    /**
     * 测试XML解析
     */
    public function testXmlParse()
    {
        // 测试XML解析
        $xml = '<xml>
            <ToUserName><![CDATA[toUser]]></ToUserName>
            <FromUserName><![CDATA[fromUser]]></FromUserName>
            <CreateTime>123456789</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[Hello World]]></Content>
        </xml>';

        $result = Xml::parse($xml);

        $this->assertEquals('toUser', $result['ToUserName']);
        $this->assertEquals('fromUser', $result['FromUserName']);
        $this->assertEquals('123456789', $result['CreateTime']);
        $this->assertEquals('text', $result['MsgType']);
        $this->assertEquals('Hello World', $result['Content']);
    }

    /**
     * 测试XML生成
     */
    public function testXmlBuild()
    {
        // 测试XML生成
        $data = [
            'ToUserName' => 'toUser',
            'FromUserName' => 'fromUser',
            'CreateTime' => '123456789',
            'MsgType' => 'text',
            'Content' => 'Hello World'
        ];

        $xml = Xml::build($data);

        $this->assertStringContainsString('<ToUserName>toUser</ToUserName>', $xml);
        $this->assertStringContainsString('<FromUserName>fromUser</FromUserName>', $xml);
        $this->assertStringContainsString('<CreateTime>123456789</CreateTime>', $xml);
        $this->assertStringContainsString('<MsgType>text</MsgType>', $xml);
        $this->assertStringContainsString('<Content>Hello World</Content>', $xml);
    }
}