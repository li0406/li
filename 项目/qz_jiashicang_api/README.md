#qz_jiashicang



![](https://box.kancloud.cn/5a0aaa69a5ff42657b5c4715f3d49221) 

ThinkPHP 5.1��LTS�汾�� ���� 12�س��ģ���ֵ��������PHP���
===============

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/top-think/framework/badges/quality-score.png?b=5.1)](https://scrutinizer-ci.com/g/top-think/framework/?branch=5.1)
[![Build Status](https://travis-ci.org/top-think/framework.svg?branch=master)](https://travis-ci.org/top-think/framework)
[![Total Downloads](https://poser.pugx.org/topthink/framework/downloads)](https://packagist.org/packages/topthink/framework)
[![Latest Stable Version](https://poser.pugx.org/topthink/framework/v/stable)](https://packagist.org/packages/topthink/framework)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D5.6-8892BF.svg)](http://www.php.net/)
[![License](https://poser.pugx.org/topthink/framework/license)](https://packagist.org/packages/topthink/framework)

ThinkPHP5.1�Եײ�ܹ����˽�һ���ĸĽ�����������������Ҫ���԰�����

 + ��������ͳһ�������
 + ֧��Facade
 + ע��·��֧��
 + ·�ɿ�������֧��
 + ���ú�·��Ŀ¼����
 + ȡ��ϵͳ����
 + ���ֺ�����ǿ
 + ����������
 + ����������ѯ
 + �Ľ���ѯ����
 + ���ò��ö���
 + ����ע������
 + ֧��`PSR-3`��־�淶
 + �м��֧�֣�V5.1.6+��
 + Swoole/Workerman֧�֣�V5.1.18+��


> ThinkPHP5�����л���Ҫ��PHP5.6���ϡ�

## ��װ

ʹ��composer��װ

~~~
composer create-project topthink/think tp
~~~

��������

~~~
cd tp
php think run
~~~

Ȼ��Ϳ�����������з���

~~~
http://localhost:8000
~~~

���¿��
~~~
composer update topthink/framework
~~~


## �����ֲ�

+ [��ȫ�����ֲ�](https://www.kancloud.cn/manual/thinkphp5_1/content)
+ [����ָ��](https://www.kancloud.cn/manual/thinkphp5_1/354155) 

## Ŀ¼�ṹ

��ʼ��Ŀ¼�ṹ���£�

~~~
www  WEB����Ŀ¼��������Ŀ¼��
����application           Ӧ��Ŀ¼
��  ����common             ����ģ��Ŀ¼�����Ը��ģ�
��  ����module_name        ģ��Ŀ¼
��  ��  ����common.php      ģ�麯���ļ�
��  ��  ����controller      ������Ŀ¼
��  ��  ����model           ģ��Ŀ¼
��  ��  ����view            ��ͼĿ¼
��  ��  ���� ...            �������Ŀ¼
��  ��
��  ����command.php        �����ж����ļ�
��  ����common.php         ���������ļ�
��  ����tags.php           Ӧ����Ϊ��չ�����ļ�
��
����config                Ӧ������Ŀ¼
��  ����module_name        ģ������Ŀ¼
��  ��  ����database.php    ���ݿ�����
��  ��  ����cache           ��������
��  ��  ���� ...            
��  ��
��  ����app.php            Ӧ������
��  ����cache.php          ��������
��  ����cookie.php         Cookie����
��  ����database.php       ���ݿ�����
��  ����log.php            ��־����
��  ����session.php        Session����
��  ����template.php       ģ����������
��  ����trace.php          Trace����
��
����route                 ·�ɶ���Ŀ¼
��  ����route.php          ·�ɶ���
��  ����...                ����
��
����public                WEBĿ¼���������Ŀ¼��
��  ����index.php          ����ļ�
��  ����router.php         ���ٲ����ļ�
��  ����.htaccess          ����apache����д
��
����thinkphp              ���ϵͳĿ¼
��  ����lang               �����ļ�Ŀ¼
��  ����library            ������Ŀ¼
��  ��  ����think           Think����Ŀ¼
��  ��  ����traits          ϵͳTraitĿ¼
��  ��
��  ����tpl                ϵͳģ��Ŀ¼
��  ����base.php           ���������ļ�
��  ����console.php        ����̨����ļ�
��  ����convention.php     ��ܹ��������ļ�
��  ����helper.php         ���ֺ����ļ�
��  ����phpunit.xml        phpunit�����ļ�
��  ����start.php          �������ļ�
��
����extend                ��չ���Ŀ¼
����runtime               Ӧ�õ�����ʱĿ¼����д���ɶ��ƣ�
����vendor                ���������Ŀ¼��Composer�����⣩
����build.php             �Զ����ɶ����ļ����ο���
����composer.json         composer �����ļ�
����LICENSE.txt           ��Ȩ˵���ļ�
����README.md             README �ļ�
����think                 ����������ļ�
~~~

> ����ʹ��php�Դ�webserver���ٲ���
> �л�����Ŀ¼���������php think run

## �����淶

`ThinkPHP5`��ѭPSR-2�����淶��PSR-4�Զ����ع淶������ע�����¹淶��

### Ŀ¼���ļ�

*   Ŀ¼��ǿ�ƹ淶���շ��Сд+�»���ģʽ��֧�֣�
*   ��⡢�����ļ�ͳһ��`.php`Ϊ��׺��
*   ����ļ������������ռ䶨�壬���������ռ��·��������ļ�����·��һ�£�
*   ���������ļ�������һ�£�ͳһ�����շ巨����������ĸ��д����

### �������ࡢ��������

*   ������������շ巨����������ĸ��д������ `User`��`UserType`��Ĭ�ϲ���Ҫ��Ӻ�׺������`UserController`Ӧ��ֱ������Ϊ`User`��
*   ����������ʹ��Сд��ĸ���»��ߣ�Сд��ĸ��ͷ���ķ�ʽ������ `get_client_ip`��
*   ����������ʹ���շ巨����������ĸСд������ `getUserName`��
*   ���Ե�����ʹ���շ巨����������ĸСд������ `tableName`��`instance`��
*   ��˫�»��ߡ�__����ͷ�ĺ����򷽷���Ϊħ������������ `__call` �� `__autoload`��

### ����������

*   �����Դ�д��ĸ���»������������� `APP_PATH`�� `THINK_PATH`��
*   ���ò�����Сд��ĸ���»������������� `url_route_on` ��`url_convert`��

### ���ݱ���ֶ�

*   ���ݱ���ֶβ���Сд���»��߷�ʽ��������ע���ֶ�����Ҫ���»��߿�ͷ������ `think_user` ��� `user_name`�ֶΣ�������ʹ���շ��������Ϊ���ݱ��ֶ�������

## ���뿪��

����� [ThinkPHP5 ���Ŀ�ܰ�](https://github.com/top-think/framework)��

## ��Ȩ��Ϣ

ThinkPHP��ѭApache2��ԴЭ�鷢�������ṩ���ʹ�á�

����Ŀ�����ĵ�����Դ��Ͷ������ļ�֮��Ȩ��Ϣ���б�ע��

��Ȩ����Copyright ? 2006-2018 by ThinkPHP (http://thinkphp.cn)

All rights reserved��

ThinkPHP? �̱������Ȩ������Ϊ�Ϻ�������Ϣ�Ƽ����޹�˾��

����ϸ�ڲ��� [LICENSE.txt](LICENSE.txt)
