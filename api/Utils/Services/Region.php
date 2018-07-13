<?php
/**
 * Created by PhpStorm.
 * User: viki
 * Date: 2018/5/8/015
 * Time: 15:00
 */

namespace App\Utils\Services;

use DI\Container;
use PhpBoot\DB\DB;
use App\Utils\Mutex;
use App\Utils\Paramers;
use App\Utils\ErrorConst;
use Psr\Log\LoggerInterface;
use function App\getCurrentTime;
use App\Utils\ThrowResponseParamerTrait;
use PhpBoot\DI\Traits\EnableDIAnnotations;

class Region
{
    use EnableDIAnnotations, ThrowResponseParamerTrait;

    /**
     * @inject
     * @var Paramers
     */
    protected $paramer;
    /**
     * @inject
     * @var DB
     */
    private $db;

    /**
     * @inject
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @inject
     * @var Mutex
     */
    public $mutex;

    /**
     * @inject
     * @var Container
     */
    protected $container;

    /**
     * @inject
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * getProvince
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getProvince()
    {
        try {
            return $this->db->select('b.id,code,name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')->where(['r.level' => 1])
                ->orderBy('r.id', DB::ORDER_BY_ASC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * getBasicProvince
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getBasicProvince($arr = [])
    {
        try {
            $list = $this->db->select('basic_id')->from('t_basic_region_relate')->where(['level' => 1])->get();
            foreach ($list as $k => $v) $arr[] = $v['basic_id'];
            $where = ['level' => 1, 'parent_id' => 0];
            if ($arr) $where['id'] = ['not in' => $arr];
            return $this->db->select('id', 'code', 'name')->from('t_basic_region')->where($where)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * addProvince
     * @param int $province_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function addProvince($province_id)
    {
        try {
            return $this->mutex->getMutex('addProvince' . $province_id)->synchronized(function () use ($province_id) {
                $item = $this->db->select('id')->from('t_basic_region_relate')->where(['basic_id' => $province_id])->getFirst();
                if ($item) return true;
                return $this->db->insertInto('t_basic_region_relate')->values([
                    'level' => 1,
                    'basic_id' => $province_id,
                    'add_time' => getCurrentTime(),
                ])->exec()->rows;
            });

        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * addCity
     * @param int $province_id
     * @param int $city_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function addCity($province_id, $city_id)
    {
        try {
            return $this->mutex->getMutex('addCity' . $city_id)->synchronized(function () use ($province_id, $city_id) {
                $item = $this->db->select('id')->from('t_basic_region_relate')->where(['basic_id' => $city_id])->getFirst();
                if ($item) return true;
                return $this->db->insertInto('t_basic_region_relate')->values([
                    'level' => 2,
                    'basic_id' => $city_id,
                    'one_id' => $province_id,
                    'add_time' => getCurrentTime(),
                ])->exec()->rows;
            });

        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * addCity
     * @param int $province_id
     * @param int $city_id
     * @param int $area_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function addArea($province_id, $city_id, $area_id)
    {
        try {
            return $this->mutex->getMutex('addArea' . $area_id)->synchronized(function () use ($province_id, $city_id, $area_id) {
                $item = $this->db->select('id')->from('t_basic_region_relate')->where(['basic_id' => $area_id])->getFirst();
                if ($item) return true;
                $base = $this->db->select('name')->from('t_basic_region')->where(['id' => $city_id])->getFirst();
                return $this->db->insertInto('t_basic_region_relate')->values([
                    'level' => 3,
                    'basic_id' => $area_id,
                    'one_id' => $province_id,
                    'two_id' => $city_id,
                    'prefix' => $base['name'],
                    'add_time' => getCurrentTime(),
                ])->exec()->rows;
            });
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * getBasicCityByProvinceId
     * @param int $province_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getBasicCityByProvinceId($province_id, $arr = [])
    {
        try {
            $list = $this->db->select('basic_id')->from('t_basic_region_relate')->where(['level' => 2, 'one_id' => $province_id])->get();
            foreach ($list as $k => $v) $arr[] = $v['basic_id'];
            $where = ['level' => 2, 'parent_id' => $province_id];
            if ($arr) $where['id'] = ['not in' => $arr];
            return $this->db->select('id', 'code', 'name')->from('t_basic_region')->where($where)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * noHotCity
     * @param int $province_id
     * @param int $hot_flag
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function noHotCity($province_id, $hot_flag = 0)
    {
        try {
            $where = ['r.level' => 2, 'one_id' => $province_id];
            if ($hot_flag == 0) $where['r.is_hot'] = 0;
            return $this->db->select('b.id,code,name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where($where)->orderBy('r.id', DB::ORDER_BY_DESC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * changeCity
     * @param int $city_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function changeCity($city_id)
    {
        try {
            $where = ['r.level' => 3, 'two_id' => $city_id];
            return $this->db->select('b.id,code,name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where($where)->orderBy('r.id', DB::ORDER_BY_DESC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * delProvince
     * @param int $province_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function delProvince($province_id)
    {
        try {
            $this->db->deleteFrom('t_basic_region_relate')->where(['basic_id' => $province_id])->orWhere(['one_id' => $province_id])->exec();
            $this->db->deleteFrom('t_product_region')->where(['province_id' => $province_id])->exec();
            return true;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * delCity
     * @param int $city_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function delCity($city_id)
    {
        try {
            $this->db->deleteFrom('t_basic_region_relate')->where(['basic_id' => $city_id])->orWhere(['two_id' => $city_id])->exec();
            $this->db->deleteFrom('t_product_region')->where(['city_id' => $city_id])->exec();
            return true;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * delArea
     * @param int $area_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function delArea($area_id)
    {
        try {
            $this->db->deleteFrom('t_basic_region_relate')->where(['basic_id' => $area_id])->exec();
            $this->db->deleteFrom('t_product_region')->where(['region_id' => $area_id])->exec();
            return true;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * configureCity
     * @param int $province_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function configureCity($province_id)
    {
        try {
            $list = $this->db->select('b.id,code,name,is_hot')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where(['r.level' => 2, 'one_id' => $province_id])->orderBy('r.add_time', DB::ORDER_BY_DESC)->get();
            foreach ($list as $k => $v) {
                $list[$k]['area'] = $this->db->select('b.id,code,name,basic_id,is_hot')->from('t_basic_region', 'b')
                    ->leftJoin(DB::raw('t_basic_region_relate r'))->on('r.basic_id=b.id')
                    ->where(['b.level' => 3, 'parent_id' => $v['id']])->orderBy('b.id', DB::ORDER_BY_ASC)->get();
            }
            return $list;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * delArea
     * @param int $area_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function addHotCity($city_id)
    {
        try {
            $item = $this->db->select('*')->from('t_basic_region_relate')->where(['basic_id' => $city_id])->getFirst();
            if (!$item) return true;
            $this->db->update('t_basic_region_relate')->set(['is_hot' => 1])->where(['basic_id' => $city_id])->exec();
            return true;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * hotCity
     * @return array
     * @param int $default
     * @throws \App\Exceptions\RuntimeException
     */
    public function hotCity($default = 0)
    {
        try {
            $list = $this->db->select('b.id,name,hot_sort')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where(['is_hot' => 1])->orderBy('hot_sort', DB::ORDER_BY_DESC)
                ->orderBy('r.add_time', DB::ORDER_BY_ASC)->get();
            if ($list) return $list;
            else if ($default == 1) {
                $arr = ['北京', '上海', '广州', '深圳', '天津', '杭州', '南京', '苏州', '成都', '武汉', '重庆'];
                $list = $this->db->select('b.id,name')->from('t_basic_region_relate', 'r')
                    ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')->where(['name' => ['in' => $arr]])
                    ->orderBy('r.add_time', DB::ORDER_BY_ASC)->get();
                if (! $list) return [];
                $sortArr = [];
                foreach ($arr as $k => $v) {
                    foreach ($list as $k1 => $v1) {
                        if ($v == $v1['name']) {
                            $sortArr[] = ['id' => $v1['id'], 'name' => $v];
                            break;
                        }
                    }
                }
                return $sortArr;
            } else return [];
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * more
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function more()
    {
        try {
            return $this->db->select('b.id,name,initial,prefix')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where(['r.level' => ['>' => 1]])->orderBy('initial', DB::ORDER_BY_ASC)
                ->orderBy('hot_sort', DB::ORDER_BY_DESC)
                ->orderBy('is_hot', DB::ORDER_BY_DESC)->orderBy('r.level', DB::ORDER_BY_ASC)
                ->orderBy('r.add_time', DB::ORDER_BY_ASC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * choose
     * param int $province_id
     * @param int $city_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function choose($province_id = 0, $city_id = 0)
    {
        try {
            $where = ['id' => 0];
            if (! $province_id && !$city_id) $where = ['r.level' => 1];
            else if ($province_id && !$city_id) $where = ['one_id' => $province_id, 'r.level' => 2];
            else if ($province_id && $city_id) $where = ['one_id' => $province_id, 'two_id' => $city_id, 'r.level' => 3];
            return $this->db->select('b.id,name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where($where)->orderBy('r.add_time', DB::ORDER_BY_ASC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * chooseBase
     * param int $province_id
     * @param int $city_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function chooseBase($province_id = 0, $city_id = 0)
    {
        try {
            $where = ['id' => 0];
            if (! $province_id && !$city_id) $where = ['level' => 1];
            else if ($province_id && !$city_id) $where = ['parent_id' => $province_id, 'level' => 2];
            else if ($province_id && $city_id) $where = ['parent_id' => $city_id, 'level' => 3];
            return $this->db->select('id', 'name')->from('t_basic_region')
                ->where($where)->orderBy('id', DB::ORDER_BY_ASC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * search
     * param string $keywords
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function search($keywords)
    {
        try {
            if (! $keywords) return [];
            return $this->db->select('b.id,name,prefix')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where([
                    'name' => ['like' => '%' . $keywords . '%'],
                    'r.level' => ['>' => 1],
                ])->orderBy('hot_sort', DB::ORDER_BY_DESC)->orderBy('is_hot', DB::ORDER_BY_DESC)
                ->orderBy('r.level', DB::ORDER_BY_ASC)
                ->orderBy('r.add_time', DB::ORDER_BY_ASC)->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * selected
     * param int $uid
     * param int $selected
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function selected($uid, $selected)
    {
        try {
            $region = $this->db->select('name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')->where(['r.basic_id' => $selected])->getFirst();
            if (! $region && $selected) return true;
            if (! $uid) return true;
            $this->db->update('t_user_info')->set(['region_id' => $selected])->where(['uid' => $uid])->exec();
            return true;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * current
     * param int $uid
     * param int $region_id
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function current($uid, $region_id = 0)
    {
        try {
            if ($uid) {
                $where = "region_id>0 and uid='$uid'";
                $item = $this->db->select('region_id')->from('t_user_info')->where($where)->getFirst();
                $region_id = $item ? $item['region_id'] : $region_id;
            }
            $region = $this->db->select('name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')->where(['r.basic_id' => $region_id])->getFirst();
            if (! $region) return ['region_id' => '0', 'name' => '全国'];
            else return ['region_id' => $region_id, 'name' => $region['name'] ? $region['name'] : '全国'];
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * changeSort
     * param int $region_id
     * param int $sort
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function changeSort($region_id, $sort)
    {
        try {
            $region = $this->db->select('name')->from('t_basic_region_relate', 'r')
                ->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where(['r.basic_id' => $region_id, 'r.is_hot' => 1])->getFirst();
            if (! $region) true;
            return $this->db->update('t_basic_region_relate')->set(['hot_sort' => $sort])->where(['basic_id' => $region_id])->exec()->rows;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * delHot
     * param int $region_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function delHot($region_id)
    {
        try {
            return $this->db->update('t_basic_region_relate')->set(['is_hot' => 0])->where(['basic_id' => $region_id])->exec()->rows;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * 添加商品关联
     * @param int $product_id {@v min:1}
     * @param int $province_id
     * @param int $city_id
     * @param int $area_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function addGoodsRelate($product_id, $province_id, $city_id, $area_id) {
        try {
            $region_id = $city_id ? $city_id : $province_id;
            $region_id = $area_id ? $area_id : $region_id;
            $key = $product_id . '-' . $region_id;
            return $this->mutex->getMutex('addArea' . $key)->synchronized(function () use ($product_id, $province_id, $city_id, $area_id, $region_id) {
                $count = $this->db->select('region_id')->from('t_product_region')->where(['region_id' => $region_id, 'product_id' => $product_id])->count();
                if ($count) {
                    throw $this->exception([
                        'code' => ErrorConst::REPEAT_ADD,
                        'text' => '重复添加',
                    ]);
                }
                return $this->db->insertInto('t_product_region')->values([
                    'product_id' => $product_id,
                    'province_id' => $province_id,
                    'city_id' => $city_id,
                    'region_id' => $region_id,
                    'add_time' => getCurrentTime(),
                ])->exec()->rows;
            });

        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * 商品关联列表
     * @param int $product_id {@v min:1}
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function goodsRelate($product_id) {
        try {
            return $this->db->select('basic_id', 'name')->from('t_product_region', 'p')->leftJoin(DB::raw('t_basic_region_relate r'))
                ->on('r.basic_id=p.region_id')->leftJoin(DB::raw('t_basic_region b'))->on('b.id=r.basic_id')
                ->where(['product_id' => $product_id, 'region_id' => ['>' => 0]])->get();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * 商品删除关联
     * @param int $product_id
     * @param int $region_id
     * @return boolean
     * @throws \App\Exceptions\RuntimeException
     */
    public function delGoodsRelate($product_id, $region_id) {
        try {
            return $this->db->deleteFrom('t_product_region')->where(['product_id' => $product_id, 'region_id' => $region_id])->exec()->rows;
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }

    /**
     * getBasicInfoByid
     * @return array
     * @throws \App\Exceptions\RuntimeException
     */
    public function getBasicInfoByid($id)
    {
        try {
            return $this->db->select('*')->from('t_basic_region')
                ->where(['id' => $id])->getFirst();
        } catch (\PDOException $e) {
            throw $this->exception([
                'code' => ErrorConst::SYSTEM_ERROR,
                'text' => $e->getTrace()
            ]);
        }
    }
}