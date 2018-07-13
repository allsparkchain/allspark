<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/3 0003
 * Time: 16:13
 */

namespace App\Utils\Services;
use PhpBoot\DI\Traits\EnableDIAnnotations;
use App\Utils\ThrowResponseParamerTrait;
use App\Utils\HttpResponseTrait;
use PhpBoot\DB\DB;
use App\random;

class Auth
{
    use EnableDIAnnotations,ThrowResponseParamerTrait, HttpResponseTrait;

    /**
     * @inject
     * @var DB
     */
    protected $db;

    protected $sTable = 't_oauth_access_tokens';
    /**
     * 查询平台对应的重定向地址
     * @param int $iPlatform
     * @return string
     */
    public function redirectSite($iPlatform)
    {
        $rs = $this->db->select('redirect_site')
            ->from('t_redirect_site')
            ->where([
                'platform_id' => $iPlatform,
            ])->getFirst();
        return empty($rs) ? '' : $rs['redirect_site'];
    }

    /**
     * 生成token
     * @param string $iuserID
     * @return string token
     */
    public function tokenGenerate($iUserID)
    {
        try {
            $sRand = random(32);
            $iTime = time();
            $sAccessToken = md5($sRand.$iTime. $iUserID);

            $oAccessToken = DB::table(self::$sTable)->where('iUserID', $iUserID)->first();

            $aData = [
                'iUserID' => $iUserID,
                'sAccessToken' => $sAccessToken,
                'iExpireTime' => $iTime + env('ACCESS_TOKEN_EXPIRE', LoginCenterConst::EXPIRE_TIME),
                'iStatus' => 1,
            ];

            if ($oAccessToken) {
                $aData['iUpdateTime'] = $iTime;
                DB::table(self::$sTable)->where('iUserID', $iUserID)->update($aData);
            } else {
                $aData['iCreateTime'] = $iTime;
                DB::table(self::$sTable)->insert($aData);
            }

            $oRedis = Redis::connection();
            $oRedis->setnx(sprintf(self::$sUserTokenCacheKey, $sAccessToken), $iUserID);
            //设置过期时间
            $oRedis->expireat(sprintf(self::$sUserTokenCacheKey, $sAccessToken), env('ACCESS_TOKEN_EXPIRE', LoginCenterConst::EXPIRE_TIME));
            return encrypt($sAccessToken);
        }catch(Exception $e){
            abort('404', $e->getMessage());
        }
    }
}