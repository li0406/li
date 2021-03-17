<?php
/**
 *  跟踪订单来源
 *  Tracking order source
 */

namespace Common\Model;

use Think\Model;

class TrackingOrderSourceModel extends Model
{
    protected $autoCheckFields = false;
    //设置autoCheckFields属性为false后，就会关闭字段信息的自动检测，
    //因为ThinkPHP采用的是惰性数据库连接，只要你不进行数据库查询操作，是不会连接数据库的。

    //本函数处理检查 是否有 cookie('src_mark') 标记
    public function onHaveMarked ()
    {
        $srcMarkCookie = cookie('src_mark');
        if (!empty($srcMarkCookie)) {
            return self::getSourceBySrcToSourceArr($srcMarkCookie);
        }
        return '';
    }

    /**
     * 通过标记名称获取标记数字编号
     * @param   str $srcMarkCookie  cookie中标记的名称
     * @return int 数字编号
     */
    public function getSourceBySrcToSourceArr($srcMarkCookie)
    {
        if (!empty($srcMarkCookie)) {
            $stcArr = self::srcToSourceArr();
            return $stcArr[$srcMarkCookie];
        }
        return 0;
    }

    //获取推广Id
    public function getPromoteSrc($src){
        if(!empty($src)){
            return M('order_source')->field('*')->where(array("visible"=>array("EQ",'0'),"src" => array("EQ",$src)))->find();
        }
    }


    /**
     * 来源标记对应的数字编号
     * @return   arr
     */
    public function srcToSourceArr()
    {
        return array(
                    'video'   => '200',
                    'video0'  => '201',
                    'video1'  => '202',
                    'video2'  => '203',
                    'video3'  => '204',

                    'qqqun'   => '210',
                    'qqqun0'  => '211',
                    'qqqun1'  => '212',
                    'qqqun2'  => '213',
                    'qqqun3'  => '214',

                    'luntan'  => '220',
                    'luntan0' => '221',
                    'luntan1' => '222',
                    'luntan2' => '223',
                    'luntan3' => '224',

                    'weibo'   => '230',
                    'weibo0'  => '231',
                    'weibo1'  => '232',
                    'weibo2'  => '233',
                    'weibo3'  => '234',

                    'weixin'  => '240',
                    'weixin0' => '241',
                    'weixin1' => '242',
                    'weixin2' => '243',
                    'weixin3' => '244',

                    'youku'   => '245', // youku（优酷）
                    'jrtt'    => '246', // jrtt（今日头条）
                    'wxgzh'   => '247', // wxgzh（微信公众号）

                    //搜索营销
                    'ssyx-gdt' => '290',

                    //百度推广
                    'p-bd' => '299',
                    'm-bd' => '298',

                );
    }

}