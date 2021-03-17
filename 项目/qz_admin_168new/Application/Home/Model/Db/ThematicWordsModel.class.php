<?php

namespace Home\Model\Db;
Use Think\Model;

class ThematicWordsModel extends Model
{
    public function findWordInfoByName($word)
    {
        $map = array(
            "name" => array("EQ",$word),
        );
        return $this->where($map)->find();
    }

    public function findWordInfoByNames($words,$type)
    {
        $map = array(
            "name" => array("IN",$words),
            "type" => array("EQ",$type)
        );
        return $this->where($map)->select();
    }


    public function findWordInfoById($id)
    {
        $map = array(
            "id" => array("EQ",$id)
        );
        return $this->where($map)->find();
    }

    public function getWordInfoByIds($ids){
        $map = array(
            "id" => array("in",$ids)
        );
        return $this->where($map)->select();
    }

    public function addInfo($data)
    {
        return $this->add($data);
    }

    public function editInfo($id,$data)
    {
        $map = array(
           "id" => array("EQ",$id)
        );
        return $this->where($map)->save($data);
    }

    public function getWordsListCount($name,$module,$type,$start,$end)
    {
        $map = [
            'is_delete'=>1
        ];
        if (!empty($name)) {
            $map["name"] = array("LIKE","%".$name."%");
        }

        if (!empty($module)) {
            $map["module"] = array("EQ",$module);
        }

        if (!empty($type)) {
            $map["type"] = array("EQ",$type);
        }

        if (!empty($start) && !empty($end)) {
            $map["createtime"] = array(
                array("EGT",strtotime($start)),
                array("ELT",strtotime($end) + 86400 - 1)
            );
        }

        return $this->where($map)->count();
    }

    public function getWordsList($name,$module,$type,$start,$end,$pageIndex,$pageCount)
    {
        $map = [
            'is_delete'=>1
        ];
        if (!empty($name)) {
            $map["name"] = array("LIKE","%".$name."%");
        }
        if (!empty($module)) {
            $map["module"] = array("EQ",$module);
        }

        if (!empty($type)) {
            $map["type"] = array("EQ",$type);
        }

        if (!empty($start) && !empty($end)) {
            $map["createtime"] = array(
                array("EGT",strtotime($start)),
                array("ELT",strtotime($end) + 86400 - 1)
            );
        }

        return $this->where($map)->field("id,name,module,words,is_delete,createtime,is_show,type,op_uname")->order("id desc")->limit($pageIndex,$pageCount)->select();
    }

    public function addAllInfo($data)
    {
        return $this->addAll($data);
    }

    public function getExportList($module,$type,$name,$start,$end)
    {
        if (!empty($module)) {
            $map["module"] = array("EQ",$module);
        }

        if (!empty($name)) {
            $map["name"] = array("LIKE","%".$name."%");
        }
        if (!empty($type)) {
            $map["type"] = array("EQ",$type);
        }

        if (!empty($start) && !empty($end)) {
            $map["createtime"] = array(
                array("EGT",strtotime($start)),
                array("ELT",strtotime($end) + 86400 - 1)
            );
        }

        return $this->where($map)->field("id,name,module,type,createtime,words,is_show")->select();
    }

    public function addThematicRelated($data)
    {
        return M("thematic_content_related")->addAll($data);
    }

    public function delThematicRelated($content_id,$module)
    {
        $map = array(
            "content_id" => array("EQ",$content_id),
            "module" => array("EQ",$module)
        );
        return M("thematic_content_related")->where($map)->delete();
    }

    public function getArticleList($name,$module)
    {
        $map["a.state"] = array("EQ",2);
        $map["_complex"] = array(
            "a.title" => array("EQ","$name"),
            "a.id" => array("EQ",$name),
            "_logic" => "OR"
        );

        return M("www_article")->where($map)->alias("a")
                        ->join("left join qz_thematic_content_related b on a.id = b.content_id and b.module =".$module)
                        ->join("left join qz_thematic_words c on c.id = b.thematic_id")
                        ->field("a.id,a.title,c.name as label,c.id as label_id")->select();

    }

    public function getBaikeList($name,$module)
    {
        $map["a.visible"] = array("EQ",0);
        $map["_complex"] = array(
            "a.item" => array("EQ","$name"),
            "a.id" => array("EQ",$name),
            "_logic" => "OR"
        );
        return M("baike")->where($map)->alias("a")
            ->join("left join qz_thematic_content_related b on a.id = b.content_id and b.module =".$module)
            ->join(" left join qz_thematic_words c on c.id = b.thematic_id")
            ->field("a.id,a.item as title,c.name as label,c.id as label_id")->select();
    }

    public function getAskList($name,$module)
    {
        $map["a.visible"] = array("EQ",0);
        $map["_complex"] = array(
            "a.title" => array("EQ","$name"),
            "a.id" => array("EQ",$name),
            "_logic" => "OR"
        );
        return M("ask")->where($map)->alias("a")
            ->join("left join qz_thematic_content_related b on a.id = b.content_id and b.module =".$module)
            ->join("left join qz_thematic_words c on c.id = b.thematic_id")
            ->field("a.id,a.title,c.name as label,c.id as label_id")->select();
    }

    public function getThematicList($name,$module)
    {
        $map["a.is_delete"] = array("EQ",1);
        $map["a.name"] = array("EQ","$name");

        return M("thematic_words")->where($map)->alias("a")
            ->join("left join qz_thematic_content_related b on a.id = b.content_id and b.module =".$module)
            ->join("left join qz_thematic_words c on c.id = b.thematic_id")
            ->field("a.id,a.name as title,c.name as label,c.id as label_id")->select();
    }

    public function findlabel($name)
    {
       $map = array(
           "name" => array("LIKE","$name%")
       );
       return $this->where($map)->field("id,name as text")->select();
    }

    public function delWordsInfo($ids = [])
    {
        $where = [
            'id' => ['in', $ids]
        ];
        return $this->where($where)->delete();
    }
}