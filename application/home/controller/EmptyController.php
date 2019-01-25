<?php

namespace app\home\controller;

use think\Db;

class EmptyController extends Common
{
    protected $dao, $fields;

    public function initialize()
    {
        parent::initialize();
        $this->dao = Db::name(DBNAME);
    }

    public function index()
    {


        if (DBNAME == 'page') {

            $arrchildid = db('category')->where(['id' => input('catId')])->value('arrchildid');
            $map = ' ';
            if ($arrchildid != input('catId')) {
                $map .= "page.id in ($arrchildid)";
            } else {
                $map .= 'page.id = ' . input("catId");
            }



            $info =  Db::name('page')->alias('page')->leftJoin('clt_category cat','cat.id=page.id')
                ->where('page.status = 1 or (page.status = 0 and page.createtime <'.time().')')
                ->where($map)->field('page.*,cat.catname,cat.image,cat.imageMobile,cat.catdir')->find();

            $map = 'catid = 45';
            $map .= ' and (status = 1 or (status = 0 and createtime <'.time().'))';


            $this->assign('info', $info);

            if ($info['template']) {
                $template = $info['template'];
            } else {
                $info['template'] = Db::name('category')->where(['id' => $info['id']])->value('template_show');
                   if ($info['template']) {
                    $template = $info['template'];
                } else {
                    $template = DBNAME . '_show';
                }
            }

            return $this->fetch($template);
        } else {
            if (DBNAME == 'picture') {
                $setup = Db::name('field')->where([['moduleid', 'eq', 3], ['field', 'eq', 'group']])->value('setup');
                $setup = is_array($setup) ? $setup : string2array($setup);
                $options = explode("\n", $setup['options']);
                foreach ($options as $r) {
                    $v = explode("|", $r);
                    $k = trim($v[1]);
                    $optionsarr[$k]['val'] = $v[0];
                    $optionsarr[$k]['key'] = $k;
                }
                $this->assign('options', $optionsarr);
            }
            $arrchildid = db('category')->where(['id' => input('catId')])->value('arrchildid');
            $map = ' ';
            if ($arrchildid != input('catId')) {
                $map .= 'catid in ($arrchildid)';
            } else {
                $map .= 'catid = ' . input("catId");
            }
            $map .= ' and (status = 1 or (status = 0 and createtime <' . time() . '))';
            if (DBNAME == 'cars') {
                $map.= ' and address_id='.session('address_id');

                $list = $this->dao->alias('a')
                    ->join(config('database.prefix') . 'category c', 'a.catid = c.id', 'left')
                    ->where($map)
                    ->field('a.*,c.catdir')
                    ->order('sort asc,createtime desc')
                    ->select();

                foreach ($list as $k => $v) {
                    $images = explode(";",$v['bimgs']);
                    $title = explode("\n",$v['bimgsTitle']);
                    $list[$k]['bgimg'] =  'url('.$v['bgimg'].')';

                    $list[$k]['bimgs']= array();
                    if(!empty($images)){
                        foreach ($images as $key=>$val){
                            $list[$k]['bimgs'][] = array('title'=>$title[$key],'img'=>$val);
                        }
                    }
                }
                $this->assign('cars',$list);


            } else {
                $list = $this->dao->alias('a')
                    ->join(config('database.prefix') . 'category c', 'a.catid = c.id', 'left')
                    ->where($map)
                    ->field('a.*,c.catdir')
                    ->order('sort asc,createtime desc')
                    ->paginate($this->pagesize);
                // 获取分页显示
                $page = $list->render();
                $list = $list->toArray();
                foreach ($list['data'] as $k => $v) {
                    $list['data'][$k]['controller'] = $v['catdir'];
                    if (isset($v['thumb'])) {
                        $list['data'][$k]['title_thumb'] = imgUrl($v['thumb'], '/static/home/images/portfolio-thumb/p' . ($k + 1) . '.jpg');
                    } else {
                        $list['data'][$k]['title_thumb'] = '/static/home/images/portfolio-thumb/p' . ($k + 1) . '.jpg';
                    }
                }
                $this->assign('list', $list['data']);
                $this->assign('page', $page);
            }
            $cattemplate = db('category')->where('id', input('catId'))->value('template_list');
            $template = $cattemplate ? $cattemplate : DBNAME . '_list';

            return $this->fetch($template);
        }
    }

    public function info()
    {
        Db::name(DBNAME)->where('id', input('id'))->setInc('hits');
        $info = Db::name(DBNAME)->where('id', input('id'))->find();
        $info['pic'] = isset($info['pic']) ? $info['pic'] : "/static/home/images/sample-images/blog-post" . rand(1, 3) . ".jpg";
        $title_thumb = $info['thumb'];
        $info['title_thumb'] = $title_thumb ? $title_thumb : '/static/home/images/sample-images/blog-post' . rand(1, 3) . '.jpg';
        $this->assign('info', $info);
        if ($info['template']) {
            $template = $info['template'];
        } else {
            $cattemplate = Db::name('category')->where('id', $info['catid'])->value('template_show');
            if ($cattemplate) {
                $template = $cattemplate;
            } else {
                $template = DBNAME . '_show';
            }
        }
        return $this->fetch($template);
    }


}