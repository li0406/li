<?php
/**
 * 无限级分类
 * @author LiuYuPeng <lmnily@126.com>
 * @link   http://www.lmnily.com
 */

class DpTree {

    //原始的分类数据
    private $_data  = array();

    //格式化的字符
    private $_icon  = array('&nbsp;&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;','&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;&nbsp;','&nbsp;&nbsp;&nbsp;└&nbsp;&nbsp;&nbsp;');

    //字段映射，分类id，上级分类pid,分类名称title,格式化后分类名称fulltitle
    private $_field = array();

    //格式化后的分类
    private $_formatList = array();
    private $_types =array();

    /**
     * 构造函数 - 初始化配置
     * @param  array $config 配置分类的键
     * @return $this
     */    
    public function __construct($field = array())
    {           

        $this->_field['id']       = isset($field['id']) ? $field['id'] : 'id';
        $this->_field['pid']      = isset($field['pid']) ? $field['pid'] : 'pid';
        $this->_field['name']     = isset($field['name']) ? $field['name'] : 'name';
        $this->_field['fullname'] = isset($field['fullname']) ? $field['fullname'] : 'sname';
        return $this;
    }

    public function setIcon($icon)
    {
        $this->_icon = $icon;
        return $this;
    }
  
    /**
     * 获取子类
     * @param int $pid 父ID
     * @param array $data 数据源
     */     
    public function getChild($pid, $data = array())
    {
        $childs = array();

        if (empty($data)) {
            $data = $this->_data;
        }

        foreach ($data as $cat) {
            if ($cat[$this->_field['pid']] == $pid) $childs[] = $cat;
        }

        return $childs;
    }
    
    /**
     * 获取分类树
     * @param array $data 数据源
     * @param int   $id   id
     */   
    public function getTree($data, $id = 0)
    {
        if (empty($data)) return false;

        $this->_data = $data;
        $this->_searchList($id);
        return $this->_formatList;
    }

    /**
     * 递归格式化分类前的字符
     * @param int $id
     * @param string $space
     */
    private function _searchList($id = 0, $space = '')
    {
        $childs = $this->getChild($id);

        if ( !($n = count($childs)) ) return false;

        $cnt = 1;

        for ($i = 0; $i < $n; $i++) {
            $pre = $pad = '';
            if ($n == $cnt) {
                $pre = $this->_icon[2];
                $pad = $id==0 ? '' : "&nbsp;&nbsp;&nbsp;&nbsp;";
                // $pad = $id==0 ? '' : "  ";
            } else {
                $pre = $this->_icon[1];
                $pad = $space ? $this->_icon[0] : '';
            }
            $childs[$i][$this->_field['fullname']] = ($space ? $space.$pre : '') . $childs[$i][$this->_field['name']];
            $this->_formatList[] = $childs[$i];
            $this->_searchList($childs[$i][$this->_field['id']], $space.$pad.'  ');
            $cnt++;
        }
    }

    /**
     * 获取当前分类路径
     * @param array $data 数据源
     * @param int   $id id
     * @return array
     */
    public function getPath($data, $id)
    {
        $this->_data = $data;
        while (1) {
            $id = $this->_getPid($id);
            if ($id == 0) break;
        }
        return $this->_formatList;
    }
    
    /**
     * 通过当前ID，获取父ID
     * @param int $id
     * @return int
     */
    private function _getPid($id)
    {
        foreach ($this->_data as $key => $value) {
            if ($this->_data[$key][$this->_field['id']] == $id) {
                $this->_formatList[] = $this->_data[$key];
                return $this->_data[$key][$this->_field['pid']];
            }
        }
        return 0;
    }

    /**
     * 获取分类的最大分级数的准备操作
     */
    private function _searchList0($pid=0,$path=0)
    {
         $childs = $this->getChild($pid);
         if ( !($n = count($childs)) ) return false;

         foreach ($childs as $key => $v) {
            $str='';$j=0;
            for($i=0;$i<$path;$i++){
                $str .="<select></select>"; 
                $j++;       
            }
            $v['num'] = $j;
            $this->_types[]  = $v;
            $this->_searchList0($v[$this->_field['id']],($path+1));
        }
      
    }

    /**
     * 获取分类的最大分级数
     */
    public function getcatenum($data,$pid=0)
    {
        if (empty($data)) return false;
        $this->_data = $data;
        $this->_searchList0($pid);
        $max = 0;
        for($i=0;$i<count($this->_types);$i++){
            $max=max($max,$this->_types[$i]['num']);
        }
        return $max;
    }


    /**
     * 无限级分类树-获取父类
     * @param  array $data 树的数组
     * @param  int   $id   子类ID
     * @return string|array
     */
    public function getParent($data, $id)
    {
        if (!$data || !is_array($data)) {
            return array();
        }

        $temp = array();
        $f_id = $this->_field['id'];
        foreach ($data as $vaule) {
            $temp[$vaule[$f_id]] = $vaule;
        }
        $f_pid = $this->_field['pid'];
        $parentid = $temp[$id][$f_pid];

        return $temp[$parentid];
    }
}
?>